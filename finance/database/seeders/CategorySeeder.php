<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [];
        $users = DB::table('users')->get();
        
        $incomeCategories = ['Зарплата', 'Подработка', 'Инвестиции', 'Подарки', 'Кэшбэк'];
        $expenseCategories = ['Продукты', 'Транспорт', 'Развлечения', 'Коммунальные услуги', 'Здоровье', 'Одежда', 'Рестораны', 'Связь', 'Образование', 'Дом'];
        
        foreach ($users as $user) {
            foreach ($incomeCategories as $index => $name) {
                $categories[] = [
                    'user_id' => $user->id,
                    'name' => $name,
                    'type' => 'income',
                    'parent_id' => null,
                    'created_at' => $user->created_at,
                    'updated_at' => now(),
                ];
            }
            
            foreach ($expenseCategories as $index => $name) {
                $categories[] = [
                    'user_id' => $user->id,
                    'name' => $name,
                    'type' => 'expense',
                    'parent_id' => null,
                    'created_at' => $user->created_at,
                    'updated_at' => now(),
                ];
            }
        }
        
        $allCategories = $categories;
        $parentCategories = DB::table('categories')->whereNotNull('id')->get();
        
        foreach ($parentCategories as $parent) {
            if (rand(0, 1)) {
                $subName = $parent->name . ' (подкатегория)';
                $categories[] = [
                    'user_id' => $parent->user_id,
                    'name' => $subName,
                    'type' => $parent->type,
                    'parent_id' => $parent->id,
                    'created_at' => $parent->created_at,
                    'updated_at' => now(),
                ];
            }
        }
        
        DB::table('categories')->insert($categories);
    }
}