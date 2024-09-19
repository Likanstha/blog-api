<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;


/**
 * @OA\Tag(
 *     name="Posts",
 *     description="API Endpoints for managing posts"
 * )
 */
class PostController extends Controller
{
    public function create(Request $request) {
       

        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'body' => 'required',
            ]);
            $post = Auth::user()->posts()->create($validated);
            return response()->json($post, 201);
        } catch (ValidationException $e) {
            Log::error('Validation error occurred', ['error' => $e->errors()]);
            return response()->json(['error' => 'Validation error'], 422);
        } catch (\Exception $e) {
            Log::error('An error occurred while creating the post', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }

    public function update(Request $request, $id) {
        try {
            // Find the post or fail
            $post = Post::findOrFail($id);

            // Validate request
            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'body' => 'sometimes|required',
            ]);
           
            // Update post
            $post->update($validated);

            return response()->json($post);
        } catch (ValidationException $e) {
            Log::error('Validation error occurred', ['error' => $e->errors()]);

            return response()->json(['error' => 'Validation error'], 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Post not found', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'Post not found'], 404);
        } catch (\Exception $e) {
            Log::error('An error occurred while updating the post', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'An error occurred'], 500);
        }
    }

    public function showPost($id) {
        try {
            $post = Post::findOrFail($id);

            return response()->json($post);
        } catch (ModelNotFoundException $e) {
            Log::error('Post not found', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'Post not found'], 404);
        } catch (\Exception $e) {
            Log::error('An error occurred while fetching the post', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'An error occurred'], 500);
        }
    }

    public function showAllPosts() {
        try {
            $posts = Post::paginate(10);

            return response()->json($posts);
        } catch (\Exception $e) {
            Log::error('An error occurred while fetching posts', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'An error occurred'], 500);
        }
    }

    public function delete($id) {
        try {
            $post = Post::findOrFail($id);
            $post->delete();
            return response()->json(['message' => 'Post deleted successfully'],200);
        } catch (ModelNotFoundException $e) {
            Log::error('Post not found', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'Post not found'], 404);
        } catch (\Exception $e) {
            Log::error('An error occurred while deleting the post', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'An error occurred'], 500);
        }
    }

}
