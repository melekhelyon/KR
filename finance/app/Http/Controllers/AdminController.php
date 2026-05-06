<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Categories;
use App\Models\User;
use App\Models\Account;
use App\Models\Goal;
use App\Models\Budget;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Operation;

class AdminController extends Controller
{
    public function stats()
    {
        return response()->json([
            'users_count' => User::count(),
            'accounts_count' => Account::where('status', 'active')->count(),
            'categories_count' => Categories::count(),
            'operations_count' => Operation::count(),
            'goals_count' => Goal::count(),
            'budgets_count' => Budget::count(),
            'total_balance' => Account::where('status', 'active')->sum('balance'),
            'users_by_date' => User::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))->groupBy('date')->orderBy('date', 'desc')->limit(30)->get(),
            'operations_by_date' => Operation::select(DB::raw('DATE(operation_date) as date'), DB::raw('SUM(CASE WHEN amount > 0 THEN amount ELSE 0 END) as income'), DB::raw('SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END) as expense'))->groupBy('date')->orderBy('date', 'desc')->limit(30)->get()
        ]);
    }

    public function users(Request $request)
    {
        $query = User::with('role');
            
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('email', 'like', '%' . $request->search . '%')->orWhere('first_name', 'like', '%' . $request->search . '%')->orWhere('last_name', 'like', '%' . $request->search . '%');
            });
        }
            
        $users = $query->latest()->paginate(10);
            
        return response()->json($users);
    }

    public function updateUserRole(Request $request, $id)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id'
        ]);

        $user = User::findOrFail($id);
        
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Нельзя изменить свою роль'], 403);
        }
        
        $user->role_id = $request->role_id;
        $user->save();
        
        return response()->json(['message' => 'Роль обновлена']);
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Нельзя удалить самого себя'], 403);
        }
        
        $user->delete();
        
        return response()->json(['message' => 'Пользователь удален']);
    }

    public function roles()
    {
        return response()->json(Role::all());
    }

    public function exportCsv(Request $request)
    {
        try {
            $fromDate = $request->get('from_date');
            $toDate = $request->get('to_date');
            $rows = [];
            $rows[] = ['========== ПОЛЬЗОВАТЕЛИ =========='];
            $rows[] = ['ID', 'Имя', 'Фамилия', 'Email', 'Телефон', 'Роль', 'Валюта', 'Дата регистрации'];
            $usersQuery = \DB::table('users')
                ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
                ->select('users.*', 'roles.name as role_name');
            if ($fromDate) $usersQuery->whereDate('users.created_at', '>=', $fromDate);
            if ($toDate) $usersQuery->whereDate('users.created_at', '<=', $toDate);
            foreach ($usersQuery->get() as $user) {
                $rows[] = [
                    $user->id,
                    $user->first_name,
                    $user->last_name,
                    $user->email,
                    $user->phone ?? '',
                    $user->role_name ?? 'user',
                    $user->default_currency ?? 'RUB',
                    $user->created_at,
                ];
            }
            $rows[] = [];
            $rows[] = ['========== СЧЕТА =========='];
            $rows[] = ['ID', 'ID пользователя', 'Название', 'Тип', 'Валюта', 'Баланс', 'Статус', 'Создан'];
            $accountsQuery = \DB::table('accounts');
            if ($fromDate) $accountsQuery->whereDate('created_at', '>=', $fromDate);
            if ($toDate) $accountsQuery->whereDate('created_at', '<=', $toDate);
            foreach ($accountsQuery->get() as $account) {
                $rows[] = [
                    $account->id,
                    $account->user_id,
                    $account->name,
                    $account->type,
                    $account->currency,
                    $account->balance,
                    $account->status,
                    $account->created_at,
                ];
            }
            $rows[] = [];
            $rows[] = ['========== ОПЕРАЦИИ =========='];
            $rows[] = ['ID', 'ID пользователя', 'ID счета', 'ID счета перевода', 'ID категории', 'Сумма', 'Описание', 'Дата операции', 'Время', 'Статус'];
            $operationsQuery = \DB::table('transactions');
            if ($fromDate) $operationsQuery->whereDate('operation_date', '>=', $fromDate);
            if ($toDate) $operationsQuery->whereDate('operation_date', '<=', $toDate);
            foreach ($operationsQuery->orderBy('operation_date', 'desc')->get() as $op) {
                $type = 'Обычная';
                if ($op->transfer_account_id) {
                    $type = 'Перевод';
                } elseif ($op->category_id) {
                    $category = \DB::table('categories')->where('id', $op->category_id)->first();
                    if ($category) {
                        $type = $category->type === 'income' ? 'Доход' : 'Расход';
                    }
                }
                $rows[] = [
                    $op->id,
                    $op->user_id,
                    $op->account_id,
                    $op->transfer_account_id ?? '',
                    $op->category_id ?? '',
                    $op->amount,
                    $op->description ?? '',
                    $op->operation_date,
                    $op->operation_time ?? '',
                    $op->status,
                ];
            }
            $rows[] = [];
            $rows[] = ['========== ЦЕЛИ =========='];
            $rows[] = ['ID', 'ID пользователя', 'Название', 'Описание', 'Целевая сумма', 'Текущая сумма', 'Прогресс %', 'Статус', 'Дата начала', 'Дата цели'];   
            $goalsQuery = \DB::table('goals');
            if ($fromDate) $goalsQuery->whereDate('created_at', '>=', $fromDate);
            if ($toDate) $goalsQuery->whereDate('created_at', '<=', $toDate);  
            foreach ($goalsQuery->get() as $goal) {
                $progress = $goal->target_amount > 0 ? round(($goal->current_amount / $goal->target_amount) * 100) : 0;
                $rows[] = [
                    $goal->id,
                    $goal->user_id,
                    $goal->name,
                    $goal->description,
                    $goal->target_amount,
                    $goal->current_amount,
                    $progress . '%',
                    $goal->status,
                    $goal->start_date,
                    $goal->target_date ?? '—',
                ];
            }
            $rows[] = [];
            $rows[] = ['========== БЮДЖЕТЫ =========='];
            $rows[] = ['ID', 'ID пользователя', 'ID категории', 'Планируемая сумма', 'Потрачено', 'Остаток', 'Период начала', 'Период конца'];  
            $budgetsQuery = \DB::table('budgets');
            if ($fromDate) $budgetsQuery->whereDate('period_start', '>=', $fromDate);
            if ($toDate) $budgetsQuery->whereDate('period_end', '<=', $toDate);
            foreach ($budgetsQuery->get() as $budget) {
                $rows[] = [
                    $budget->id,
                    $budget->user_id,
                    $budget->category_id,
                    $budget->planned_amount,
                    $budget->spent_amount ?? 0,
                    ($budget->planned_amount - ($budget->spent_amount ?? 0)),
                    $budget->period_start,
                    $budget->period_end,
                ];
            }
            $rows[] = [];
            $rows[] = ['========== КАТЕГОРИИ =========='];
            $rows[] = ['ID', 'ID пользователя', 'Название', 'Тип', 'ID родителя', 'Создана'];
            $categoriesQuery = \DB::table('categories');
            if ($fromDate) $categoriesQuery->whereDate('created_at', '>=', $fromDate);
            if ($toDate) $categoriesQuery->whereDate('created_at', '<=', $toDate);
            foreach ($categoriesQuery->get() as $category) {
                $rows[] = [
                    $category->id,
                    $category->user_id,
                    $category->name,
                    $category->type === 'income' ? 'Доход' : 'Расход',
                    $category->parent_id ?? '—',
                    $category->created_at,
                ];
            }
            $rows[] = [];
            $rows[] = ['========== СТАТИСТИКА =========='];
            $rows[] = ['Всего пользователей', \DB::table('users')->count()];
            $rows[] = ['Всего счетов', \DB::table('accounts')->where('status', 'active')->count()];
            $rows[] = ['Всего операций', \DB::table('transactions')->count()];
            $rows[] = ['Всего целей', \DB::table('goals')->count()];
            $rows[] = ['Всего бюджетов', \DB::table('budgets')->count()];
            $rows[] = ['Всего категорий', \DB::table('categories')->count()];
            $rows[] = ['Общий баланс', \DB::table('accounts')->where('status', 'active')->sum('balance')];
            $handle = fopen('php://temp', 'w+');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM для UTF-8  
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }  
            rewind($handle);
            $csvContent = stream_get_contents($handle);
            fclose($handle);
            $fileName = 'admin_report_' . date('Y-m-d_His') . '.csv';
            return response($csvContent, 200, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]); 
        } catch (\Exception $e) {
            \Log::error('Export error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'file' => basename($e->getFile()),
                'line' => $e->getLine()
            ], 500);
        }
    }
}