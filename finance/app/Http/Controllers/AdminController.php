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
}