<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BudgetSeeder extends Seeder
{
    public function run(): void
    {
        $budgets = [];
        $users = DB::table('users')->get();
        
        foreach ($users as $user) {
            $categories = DB::table('categories')
                ->where('user_id', $user->id)
                ->where('type', 'expense')
                ->get();
            
            $budgetCount = min(rand(3, 6), $categories->count());
            $selectedCategories = $categories->random($budgetCount);
            
            foreach ($selectedCategories as $category) {
                $plannedAmount = rand(3000, 30000);
                $spentAmount = rand(0, $plannedAmount * 1.3);
                
                $periodStart = now()->startOfMonth();
                $periodEnd = now()->endOfMonth();
                
                $budgets[] = [
                    'user_id' => $user->id,
                    'category_id' => $category->id,
                    'planned_amount' => $plannedAmount,
                    'spent_amount' => $spentAmount,
                    'period_start' => $periodStart,
                    'period_end' => $periodEnd,
                    'created_at' => $periodStart,
                    'updated_at' => now(),
                ];
                
                if (rand(0, 1)) {
                    $pastStart = now()->subMonth()->startOfMonth();
                    $pastEnd = now()->subMonth()->endOfMonth();
                    $pastPlanned = rand(3000, 30000);
                    
                    $budgets[] = [
                        'user_id' => $user->id,
                        'category_id' => $category->id,
                        'planned_amount' => $pastPlanned,
                        'spent_amount' => rand($pastPlanned * 0.7, $pastPlanned * 1.2),
                        'period_start' => $pastStart,
                        'period_end' => $pastEnd,
                        'created_at' => $pastStart,
                        'updated_at' => $pastEnd,
                    ];
                }
            }
        }
        
        DB::table('budgets')->insert($budgets);
    }
}