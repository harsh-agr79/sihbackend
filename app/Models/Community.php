<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Community extends Model {
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'profile_photo', 'cover_photo', 'creator_type', 'creator_id'
    ];

    // Creator of the community ( Polymorphic relationship )

    public function creator() {
        return $this->morphTo();
    }

    // Students in the community

    public function students()
    {
        return $this->morphedByMany(Student::class, 'memberable', 'community_users', 'community_id', 'member_id')
                    ->withPivot('role', 'joined_at')
                    ->withTimestamps();
    }
    
    public function mentors()
    {
        return $this->morphedByMany(Mentor::class, 'memberable', 'community_users', 'community_id', 'member_id')
                    ->withPivot('role', 'joined_at')
                    ->withTimestamps();
    }
    
    public function members()
    {
        return $this->morphToMany(User::class, 'memberable', 'community_users', 'community_id', 'member_id')
                    ->withPivot('member_type', 'role', 'joined_at')
                    ->withTimestamps();
    }
    

    // Get members by role

    public function getMembersByRole( $role ) {
        return $this->members()->wherePivot( 'role', $role );
    }

    // Posts in the community

    public function posts() {
        return $this->hasMany( Post::class );
    }
}
