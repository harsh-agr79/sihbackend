<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'community_id', 'author_type', 'author_id', 'caption', 'content', 'original_post_id'
    ];

    // Community where the post is created
    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    // Author of the post (Polymorphic relationship)
    public function author()
    {
        return $this->morphTo();
    }

    // Comments on the post
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    // Likes on the post
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    // Original post for reposts
    public function originalPost()
    {
        return $this->belongsTo(Post::class, 'original_post_id');
    }

    // Reposts of this post
    public function reposts()
    {
        return $this->hasMany(Post::class, 'original_post_id');
    }
}
