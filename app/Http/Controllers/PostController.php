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
    /**
     * @OA\Post(
     *     path="/api/posts",
     *     summary="Create a new post",
     *     tags={"Posts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "body"},
     *             @OA\Property(property="title", type="string", maxLength=255, example="New Post Title"),
     *             @OA\Property(property="body", type="string", example="Post content goes here")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Post created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Post")
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="An error occurred")
     * )
     */
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

    /**
     * @OA\Patch(
     *     path="/api/posts/{id}",
     *     summary="Partially update an existing post",
     *     tags={"Posts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="The post ID"
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", maxLength=255, example="Updated Post Title"),
     *             @OA\Property(property="body", type="string", example="Updated post content")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Post")
     *     ),
     *     @OA\Response(response=404, description="Post not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     @OA\Response(response=500, description="An error occurred")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/posts/{id}",
     *     summary="Show details of a specific post",
     *     tags={"Posts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="The post ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post details",
     *         @OA\JsonContent(ref="#/components/schemas/Post")
     *     ),
     *     @OA\Response(response=404, description="Post not found"),
     *     @OA\Response(response=500, description="An error occurred")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/posts",
     *     summary="Get all posts with pagination",
     *     tags={"Posts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer"),
     *         description="Page number for pagination"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of posts",
     *         @OA\JsonContent(ref="#/components/schemas/Post")
     *     ),
     *     @OA\Response(response=500, description="An error occurred")
     * )
     */
    public function showAllPosts() {
        try {
            $posts = Post::paginate(10);

            return response()->json($posts);
        } catch (\Exception $e) {
            Log::error('An error occurred while fetching posts', ['error' => $e->getMessage()]);

            return response()->json(['error' => 'An error occurred'], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/posts/{id}",
     *     summary="Delete a post by ID",
     *     tags={"Posts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="The post ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Post deleted successfully")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Post not found"),
     *     @OA\Response(response=500, description="An error occurred")
     * )
     */
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
