<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            AccountSeeder::class,
            CategorySeeder::class,
            TransactionSeeder::class,
            GoalSeeder::class,
            BudgetSeeder::class,
        ]);
    }
}