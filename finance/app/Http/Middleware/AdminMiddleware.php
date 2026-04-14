<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Не авторизован'], 401);
        }
        
        if ($user->role_id != 1) {
            return response()->json(['message' => 'Доступ запрещен. Требуются права администратора.'], 403);
        }

        return $next($request);
    }
}