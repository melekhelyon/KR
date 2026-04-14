<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GoalSeeder extends Seeder
{
    public function run(): void
    {
        $goals = [];
        $users = DB::table('users')->get();
        
        $goalTemplates = [
            ['name' => 'Новый автомобиль', 'description' => 'Накопить на покупку автомобиля'],
            ['name' => 'Отпуск', 'description' => 'Поездка на море'],
            ['name' => 'Ремонт', 'description' => 'Косметический ремонт в квартире'],
            ['name' => 'Новый телефон', 'description' => 'Покупка нового смартфона'],
            ['name' => 'Подушка безопасности', 'description' => 'Финансовая подушка на 3 месяца'],
            ['name' => 'Обучение', 'description' => 'Оплата курсов повышения квалификации'],
            ['name' => 'Свадьба', 'description' => 'Накопления на свадебное торжество'],
            ['name' => 'Ноутбук', 'description' => 'Покупка нового ноутбука для работы'],
            ['name' => 'Фитнес', 'description' => 'Годовой абонемент в фитнес-клуб'],
            ['name' => 'Подарки', 'description' => 'Накопления на новогодние подарки'],
        ];
        
        $statuses = ['in_progress', 'achieved', 'failed'];
        
        foreach ($users as $user) {
            $goalCount = rand(1, 3);
            $shuffledGoals = $goalTemplates;
            shuffle($shuffledGoals);
            
            for ($i = 0; $i < $goalCount; $i++) {
                $template = $shuffledGoals[$i];
                $targetAmount = rand(30000, 500000);
                $status = $statuses[array_rand($statuses)];
                
                $currentAmount = match($status) {
                    'achieved' => $targetAmount,
                    'failed' => rand(0, $targetAmount - 1),
                    default => rand(0, $targetAmount - 1),
                };
                
                $startDate = now()->subDays(rand(30, 180));
                $targetDate = $status === 'in_progress' 
                    ? now()->addDays(rand(30, 365)) 
                    : now()->subDays(rand(1, 30));
                
                $goals[] = [
                    'user_id' => $user->id,
                    'name' => $template['name'],
                    'description' => $template['description'],
                    'target_amount' => $targetAmount,
                    'current_amount' => $currentAmount,
                    'start_date' => $startDate,
                    'target_date' => $targetDate,
                    'status' => $status,
                    'created_at' => $startDate,
                    'updated_at' => now(),
                ];
            }
        }
        
        DB::table('goals')->insert($goals);
    }
}