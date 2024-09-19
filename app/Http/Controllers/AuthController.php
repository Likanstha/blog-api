<?php

namespace App\Http\Controllers;

use App\Models\User; 
use Illuminate\Http\Request;
use App\Jobs\SendWelcomeEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;


/**
 * /**
 * @OA\Tag(
 *     name="Authentication",
 *     description="API Endpoints for authentication and users"
 * )
 * @OA\OpenApi(
 *   security={{"bearerAuth": {}}}
 * )
 *
 * @OA\SecurityScheme(
 *   securityScheme="bearerAuth",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="JWT",
 *   description="Enter the token obtained from the login API."
 * )
 */

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password1234")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="user", type="object", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(response=409, description="Email already exists"),
     *     @OA\Response(response=422, description="Validation failed"),
     *     @OA\Response(response=500, description="Registration failed")
     * )
     */
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

             // Dispatch the welcome email job
            SendWelcomeEmail::dispatch($user);

            return response()->json(['user' => $user], 201);
        } catch (ValidationException $e) {
                $errors = $e->errors();
                Log::error('Validation failed during registration', ['errors' => $errors]);
                
                if (isset($errors['email']) && in_array('The email has already been taken.', $errors['email'])) {
                    return response()->json(['error' => 'Email already exists'], 409);
                }

                return response()->json(['error' => 'Validation failed', 'messages' => $errors], 422);
   
        } catch (\Exception $e) {
            Log::error('Registration failed', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Registration failed'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Login a user",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password1234")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="token123")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=500, description="Login failed")
     * )
     */
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
            $errors = $e->errors();
            Log::error('Login failed', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Login failed'], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Logout the authenticated user",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logged out")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Not authenticated"),
     *     @OA\Response(response=500, description="Logout failed")
     * )
     */
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

    /**
    * @OA\Get(
    *     path="/api/users/{id}",
    *     summary="Get a user by ID",
    *     tags={"User"},
    *     security={{"bearerAuth":{}}},
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         required=true,
    *         @OA\Schema(type="integer"),
    *         description="The ID of the user to retrieve"
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="User retrieved successfully",
    *         @OA\JsonContent(ref="#/components/schemas/User")
    *     ),
    *     @OA\Response(response=404, description="User not found"),
    *     @OA\Response(response=401, description="Unauthorized"),
    *     @OA\Response(response=500, description="An error occurred")
    * )
    */
    public function showUser($id) {
        try {
            $user = User::findOrFail($id);

            return response()->json($user);
        } catch (ModelNotFoundException $e) {
            Log::error('User not found', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'User not found'], 404);
        } catch (\Exception $e) {
            Log::error('An error occurred while fetching the User', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'An error occurred'], 500);
        }
    }
    
    
    
}
