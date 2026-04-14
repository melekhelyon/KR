<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [];
        $users = DB::table('users')->get();
        
        $accountTypes = ['debit', 'credit', 'savings', 'salary'];
        $accountNames = [
            'debit' => ['Основной счет', 'Дебетовая карта', 'Наличные'],
            'credit' => ['Кредитная карта', 'Кредитный счет'],
            'savings' => ['Накопительный счет', 'Копилка'],
            'salary' => ['Зарплатный счет', 'Зарплатная карта'],
        ];
        
        $currencies = ['RUB', 'USD', 'EUR'];
        
        foreach ($users as $user) {
            $accountCount = rand(2, 4);
            $usedNames = [];
            
            for ($i = 0; $i < $accountCount; $i++) {
                $type = $accountTypes[array_rand($accountTypes)];
                $nameOptions = $accountNames[$type];
                
                do {
                    $name = $nameOptions[array_rand($nameOptions)] . ' ' . ($i + 1);
                } while (in_array($name, $usedNames));
                
                $usedNames[] = $name;
                $currency = $currencies[array_rand($currencies)];
                
                $balance = match($type) {
                    'credit' => rand(-50000, 0),
                    'savings' => rand(10000, 200000),
                    default => rand(-10000, 150000),
                };
                
                $accounts[] = [
                    'user_id' => $user->id,
                    'name' => $name,
                    'type' => $type,
                    'currency' => $currency,
                    'balance' => $balance,
                    'status' => 'active',
                    'created_at' => $user->created_at,
                    'updated_at' => now(),
                ];
            }
        }
        
        while (count($accounts) < 10) {
            $user = $users[rand(0, count($users) - 1)];
            $type = $accountTypes[array_rand($accountTypes)];
            $nameOptions = $accountNames[$type];
            $currency = $currencies[array_rand($currencies)];
            
            $accounts[] = [
                'user_id' => $user->id,
                'name' => $nameOptions[array_rand($nameOptions)] . ' ' . (count($accounts) + 1),
                'type' => $type,
                'currency' => $currency,
                'balance' => rand(1000, 50000),
                'status' => 'active',
                'created_at' => now()->subDays(rand(1, 20)),
                'updated_at' => now(),
            ];
        }
        
        DB::table('accounts')->insert($accounts);
    }
}