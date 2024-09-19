<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;


/**
 * @OA\Schema(
 *     schema="Post",
 *     type="object",
 *     required={"id", "title", "body", "user_id"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="My first post"),
 *     @OA\Property(property="body", type="string", example="This is the body of my first post"),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-09-19T15:47:01+00:00"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-09-19T15:47:01+00:00")
 * )
 */
class Post extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['title', 'body']; 

    public function user() {
        return $this->belongsTo(User::class);
    }
}
