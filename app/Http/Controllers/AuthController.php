<?php

namespace App\Http\Controllers;

use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
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
        } catch (ValidationException $e) {
            Log::error('Validation failed during registration', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Validation failed'], 422);
        } catch (\Exception $e) {
            Log::error('Registration failed', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Registration failed'], 500);
        }
    }

    public function login(Request $request)
    {
        try {
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
                return response()->json([
                    'token' => $token,
                ], 200);
            } else {
                Log::error('User not found');
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        } catch (\Exception $e) {
            Log::error('Login failed', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Login failed'], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            // Check if the user is authenticated
            if (!$request->user()) {
                Log::warning('Logout attempt by unauthenticated user');
                return response()->json(['error' => 'Not authenticated'], 401);
            }
    
            $user = $request->user();
            Log::info('Logout request received for user', ['user_id' => $user->id]);
    
            $token = $user->currentAccessToken();
            if ($token) {
                $token->delete();
                Log::info('Token successfully deleted', ['user_id' => $user->id]);
                return response()->json(['message' => 'Logged out'], 200);
            } else {
                Log::warning('No token found or token already deleted', ['user_id' => $user->id]);
                return response()->json(['message' => 'Already logged out or no token found'], 200);
            }
        } catch (\Exception $e) {
            Log::error('Logout failed', ['user_id' => $request->user()->id ?? 'unknown', 'exception' => $e->getMessage()]);
            return response()->json(['error' => 'Logout failed'], 500);
        }
    }
    
    
    
}
