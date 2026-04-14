<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $transactions = [];
        $users = DB::table('users')->get();
        
        foreach ($users as $user) {
            $accounts = DB::table('accounts')->where('user_id', $user->id)->get();
            $categories = DB::table('categories')->where('user_id', $user->id)->get();
            
            $incomeCategories = $categories->where('type', 'income');
            $expenseCategories = $categories->where('type', 'expense');
            
            $transactionCount = rand(10, 20);
            
            for ($i = 0; $i < $transactionCount; $i++) {
                $account = $accounts->random();
                $isIncome = rand(0, 1);
                
                $category = $isIncome 
                    ? $incomeCategories->random() 
                    : $expenseCategories->random();
                
                $amount = $isIncome 
                    ? rand(1000, 100000) 
                    : -rand(100, 15000);
                
                $descriptions = [
                    'expense' => ['Покупка продуктов', 'Оплата проезда', 'Кино', 'Оплата ЖКХ', 'Аптека', 'Одежда', 'Кафе', 'Мобильная связь'],
                    'income' => ['Зарплата', 'Аванс', 'Подработка', 'Возврат долга', 'Дивиденды'],
                ];
                
                $description = $isIncome 
                    ? $descriptions['income'][array_rand($descriptions['income'])]
                    : $descriptions['expense'][array_rand($descriptions['expense'])];
                
                $transactions[] = [
                    'user_id' => $user->id,
                    'account_id' => $account->id,
                    'category_id' => $category->id,
                    'amount' => $amount,
                    'operation_date' => now()->subDays(rand(0, 30)),
                    'operation_time' => sprintf('%02d:%02d:%02d', rand(8, 22), rand(0, 59), rand(0, 59)),
                    'description' => $description,
                    'status' => 'accepted',
                    'transfer_account_id' => null,
                    'created_at' => now()->subDays(rand(0, 30)),
                    'updated_at' => now(),
                ];
            }
            
            if ($accounts->count() >= 2) {
                for ($i = 0; $i < 3; $i++) {
                    $fromAccount = $accounts->random();
                    $toAccount = $accounts->where('id', '!=', $fromAccount->id)->random();
                    
                    $amount = rand(500, 10000);
                    
                    $transactions[] = [
                        'user_id' => $user->id,
                        'account_id' => $fromAccount->id,
                        'category_id' => null,
                        'amount' => -$amount,
                        'operation_date' => now()->subDays(rand(0, 30)),
                        'operation_time' => sprintf('%02d:%02d:%02d', rand(8, 22), rand(0, 59), rand(0, 59)),
                        'description' => 'Перевод на счет ' . $toAccount->name,
                        'status' => 'accepted',
                        'transfer_account_id' => $toAccount->id,
                        'created_at' => now()->subDays(rand(0, 30)),
                        'updated_at' => now(),
                    ];
                }
            }
        }
        
        DB::table('transactions')->insert($transactions);
    }
}