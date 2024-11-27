<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunityUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'community_id', 'member_type', 'member_id', 'joined_at', 'role'
    ];

    // Community associated with this membership
    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    // Member of the community (Polymorphic relationship)
    public function member()
    {
        return $this->morphTo();
    }
}
