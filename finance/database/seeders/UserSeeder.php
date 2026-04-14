<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'role_id' => 1,
                'email' => 'admin@gmail.com',
                'password' => Hash::make('admin123'),
                'first_name' => 'Админ',
                'last_name' => 'Админов',
                'phone' => '+7 (999) 111-11-11',
                'default_currency' => 'RUB',
                'created_at' => now()->subDays(30),
                'updated_at' => now(),
            ],
        ];

        $firstNames = ['Иван', 'Петр', 'Сергей', 'Алексей', 'Дмитрий', 'Андрей', 'Михаил', 'Николай', 'Елена'];
        $lastNames = ['Иванов', 'Петров', 'Сидоров', 'Смирнов', 'Кузнецов', 'Попов', 'Васильев', 'Михайлов', 'Федорова'];
        
        for ($i = 0; $i < 9; $i++) {
            $users[] = [
                'role_id' => 2,
                'email' => 'user' . ($i + 1) . '@gmail.com',
                'password' => Hash::make('password123'),
                'first_name' => $firstNames[$i],
                'last_name' => $lastNames[$i],
                'phone' => '+7 (999) 222-22-' . str_pad($i + 10, 2, '0', STR_PAD_LEFT),
                'default_currency' => 'RUB',
                'created_at' => now()->subDays(rand(1, 25)),
                'updated_at' => now(),
            ];
        }

        DB::table('users')->insert($users);
    }
}