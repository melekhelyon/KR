<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Operation;
use App\Models\Account;
use App\Models\Budget;
use Illuminate\Support\Facades\DB;

class OperationsController extends Controller
{
    public function get(Request $request) {
        $operations = $request->user()->operations()
            ->with(['account', 'category'])
            ->latest('operation_date')
            ->get();
        return response()->json($operations);
    }

    public function store(Request $request) {
        $data = $request->validate([
            'account_id'  => 'required|exists:accounts,id',
            'category_id' => 'required|exists:categories,id',
            'amount'      => 'required|numeric',
            'description' => 'nullable|string|max:255',
            'operation_date' => 'required|date',
        ]);

        return DB::transaction(function () use ($request, $data) {
            $account = $request->user()->accounts()->findOrFail($data['account_id']);
            $category = $request->user()->categories()->findOrFail($data['category_id']);
            
            $amount = $data['amount'];
            if ($category->type === 'expense' && $amount > 0) {
                $amount = -abs($amount);
            } elseif ($category->type === 'income' && $amount < 0) {
                $amount = abs($amount);
            }
            
            $operation = $request->user()->operations()->create([
                'account_id' => $account->id,
                'category_id' => $category->id,
                'amount' => $amount,
                'description' => $data['description'] ?? null,
                'operation_date' => $data['operation_date'],
                'operation_time' => now()->format('H:i:s'),
                'status' => 'accepted'
            ]);

            $account->balance += $amount;
            $account->save();

            if ($category->type === 'expense' && $amount < 0) {
                $this->updateBudgetSpending($request->user()->id, $category->id, abs($amount), $data['operation_date']);
            }

            return response()->json($operation->load(['account', 'category']), 201);
        });
    }
    
    public function transfer(Request $request) {
        $data = $request->validate([
            'account_id'          => 'required|exists:accounts,id',
            'transfer_account_id' => 'required|exists:accounts,id|different:account_id',
            'amount'              => 'required|numeric|min:0.01',
            'operation_date'      => 'required|date',
            'description'         => 'nullable|string|max:255',
        ]);

        return DB::transaction(function () use ($request, $data) {
            $fromAccount = $request->user()->accounts()->findOrFail($data['account_id']);
            $toAccount = $request->user()->accounts()->findOrFail($data['transfer_account_id']);
            
            $amount = abs($data['amount']);
            
            // Проверка достаточности средств
            if ($fromAccount->balance < $amount) {
                return response()->json(['message' => 'Недостаточно средств на счете'], 400);
            }
            
            // Создаем операцию списания
            $fromOperation = $request->user()->operations()->create([
                'account_id'          => $fromAccount->id,
                'transfer_account_id' => $toAccount->id,
                'amount'              => -$amount,
                'description'         => $data['description'] ?? 'Перевод на счет ' . $toAccount->name,
                'operation_date'      => $data['operation_date'],
                'operation_time'      => now()->format('H:i:s'),
                'status'              => 'accepted'
            ]);
            
            // Создаем операцию зачисления
            $toOperation = $request->user()->operations()->create([
                'account_id'          => $toAccount->id,
                'transfer_account_id' => $fromAccount->id,
                'amount'              => $amount,
                'description'         => $data['description'] ?? 'Перевод со счета ' . $fromAccount->name,
                'operation_date'      => $data['operation_date'],
                'operation_time'      => now()->format('H:i:s'),
                'status'              => 'accepted'
            ]);
            
            // Обновляем балансы
            $fromAccount->decrement('balance', $amount);
            $toAccount->increment('balance', $amount);
            
            // Загружаем связанные данные для ответа
            $fromOperation->load('account');
            $toOperation->load('account');
            
            return response()->json([
                'success' => true,
                'message' => 'Перевод выполнен успешно',
                'from_operation' => $fromOperation,
                'to_operation' => $toOperation
            ], 201);
        });
    }
    
    private function updateBudgetSpending($userId, $categoryId, $amount, $operationDate)
    {
        $budget = Budget::where('user_id', $userId)
            ->where('category_id', $categoryId)
            ->where('period_start', '<=', $operationDate)
            ->where('period_end', '>=', $operationDate)
            ->first();
            
        if ($budget) {
            $budget->spent_amount = ($budget->spent_amount ?? 0) + $amount;
            $budget->save();
        }
    }
}