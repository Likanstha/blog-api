<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use App\Models\User;
use App\Models\Post;

class PostApiTest extends TestCase
{
    use RefreshDatabase;

    /** successful @test for create_post*/
    public function user_can_create_post()
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Auth::login($user);

        // Define post data
        $postData = [
            'title' => 'Test Post Title',
            'body' => 'This is the body of the test post.',
        ];

        // Send a POST request to the create endpoint
        $response = $this->postJson('/api/posts', $postData);

        // Assert the response status and structure
        $response->assertStatus(201)
                 ->assertJson([
                     'title' => $postData['title'],
                     'body' => $postData['body'],
                 ]);

        // Assert the post was created in the database
        $this->assertDatabaseHas('posts', $postData);
    }

    /** missing credential @test for create_post*/
    public function user_cannot_create_post_without_title()
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Auth::login($user);

        // Define post data with missing title
        $postData = [
            'body' => 'This post has no title.',
        ];

        // Send a POST request to the create endpoint
        $response = $this->postJson('/api/posts', $postData);

        // Assert the response status and structure
        $response->assertStatus(422)
                 ->assertJson([
                     'error' => 'Validation error',
                 ]);

        // Assert no post was created in the database
        $this->assertDatabaseMissing('posts', $postData);
    }

    /** missing credential @test for create_post*/
    public function user_cannot_create_post_without_body()
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Auth::login($user);

        // Define post data with missing body
        $postData = [
            'title' => 'Test Post Title',
        ];

        // Send a POST request to the create endpoint
        $response = $this->postJson('/api/posts', $postData);

        // Assert the response status and structure
        $response->assertStatus(422)
                 ->assertJson([
                     'error' => 'Validation error',
                 ]);

        // Assert no post was created in the database
        $this->assertDatabaseMissing('posts', $postData);
    }





}
