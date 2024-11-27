<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id', 'liker_type', 'liker_id'
    ];

    // Post the like belongs to
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    // Liker (Polymorphic relationship)
    public function liker()
    {
        return $this->morphTo();
    }
}
