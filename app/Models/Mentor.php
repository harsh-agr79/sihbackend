<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;

class Mentor extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name', 'email', 'password','verification_token','email_verified_at',
    ];

    protected $hidden = [
        'password', 'remember_token','verification_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function sendEmailVerificationNotifications($token)
    {
        $this->notify(new VerifyEmailNotification($token));
    }

    public function communities()
    {
        return $this->morphToMany(Community::class, 'member', 'community_users', 'member_id', 'community_id')
                    ->withPivot('role', 'joined_at')
                    ->withTimestamps();
    }

    // Communities created by the mentor
    public function createdCommunities()
    {
        return $this->morphMany(Community::class, 'creator');
    }

    public function objects3ds()
    {
        return $this->hasMany(Object3d::class);
    }

    // Relationship with Environment
    public function environments()
    {
        return $this->hasMany(Environment::class);
    }
    
}
