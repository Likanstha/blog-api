<?php

namespace App\Http\Controllers;

use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return response()->json(['user' => $user], 201);
    }

    public function login(Request $request) {
        $credentials = $request->only('email', 'password');
    
        Log::info('Attempting login with credentials:', $credentials); 
        if (!Auth::attempt($credentials)) {
            Log::error('Login failed for credentials:', $credentials); 
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = $request->user();

        if ($user) {
            Log::info('User found:', ['user' => $user]);
            $token = $user->createToken('api-token')->plainTextToken;
        } else {
            Log::error('User not found');
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        //$token = $user->createToken('api-token')->plainTextToken;

        // Return credentials in the response (for debugging purposes)
        return response()->json([
            'token' => $token,
        ], 200);
    }

}
