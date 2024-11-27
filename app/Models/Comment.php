<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id', 'author_type', 'author_id', 'content', 'parent_comment_id'
    ];

    // Post the comment belongs to
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    // Author of the comment (Polymorphic relationship)
    public function author()
    {
        return $this->morphTo();
    }

    // Replies to the comment
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_comment_id');
    }

    // Parent comment
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_comment_id');
    }
}
