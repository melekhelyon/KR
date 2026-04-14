<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cookie;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'password' => 'required|string|min:8',
            'email' => 'required|string|unique:users',
            'phone' => 'required|string|max:20',
        ]);

        $user = User::create([
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'password' => Hash::make($validatedData['password']),
            'email' => $validatedData['email'],
            'phone' => $validatedData['phone'],
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;
        $cookie = cookie('auth_token', $token, 60 * 24, null, null, false, false);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
        ])->withCookie($cookie);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Неверные учетные данные'], 401);
        }

        $user = Auth::user();

        $expiration = now()->addHours(1);

        $token = $user->createToken('fin', ['*'], $expiration)->plainTextToken;

        $cookie = cookie('auth_token', $token, 60, null, null, false, true); 

        return response()->json([
            'message' => 'Успешный вход',
            'user' => $user,
            'token' => $token,
        ])->withCookie($cookie);
    }

     public function logout(Request $request)
    {

        $cookie = Cookie::forget('auth_token');

        return response()->json([
            'message' => 'Logged out successfully'
        ])->withCookie($cookie);
    }

    public function profile(Request $request)
    {
        $user = $request->user()->load('role');
        return response()->json($user);
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'default_currency' => 'nullable|string|size:3',
        ]);

        $user = $request->user();
        $user->update($validated);

        return response()->json($user);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Текущий пароль неверен'], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Пароль изменен']);
    }
}
