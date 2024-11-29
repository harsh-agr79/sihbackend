<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Environment extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'mentor_id', 'object_ids'];

    protected $casts = [
        'object_ids' => 'array', // Automatically cast `object_ids` to array
    ];

    public function objects()
    {
        return $this->hasMany(ThreeDObject::class, 'id', 'object_ids');
    }

    public function mentor()
    {
        return $this->belongsTo(Mentor::class);
    }
}

