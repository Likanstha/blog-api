<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PostController extends Controller
{
    public function create(Request $request) {
        Log::info('Create post request received', ['user_id' => Auth::id()]);

        try {
            // Validate request
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'body' => 'required',
            ]);

            Log::info('Request validated', ['validated_data' => $validated]);

            // Create post
            $post = Auth::user()->posts()->create($validated);

            Log::info('Post created successfully', ['post' => $post]);

            return response()->json($post, 201);
        } catch (ValidationException $e) {
            Log::error('Validation error occurred', ['error' => $e->errors()]);

            return response()->json(['error' => 'Validation error'], 422);
        } catch (\Exception $e) {
            Log::error('An error occurred while creating the post', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'An error occurred'], 500);
        }
    }
}
