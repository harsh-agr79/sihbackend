<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;

class Student extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name', 'email', 'password','verification_token','email_verified_at', 'teacher_id'
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

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function communities()
    {
        return $this->morphToMany(Community::class, 'member', 'community_users', 'member_id', 'community_id')
                    ->withPivot('role', 'joined_at')
                    ->withTimestamps();
    }

    // Communities created by the student
    public function createdCommunities()
    {
        return $this->morphMany(Community::class, 'creator');
    }

    // Relationship with HackathonRegistration
    public function hackathonRegistrations()
    {
        return $this->hasMany(HackathonRegistration::class);
    }

    // Relationship with HackContests through HackathonRegistration
    public function hackContests()
    {
        return $this->hasManyThrough(
            HackContest::class,
            HackathonRegistration::class,
            'student_id', // Foreign key on hackathon_registrations
            'id',         // Foreign key on hack_contests
            'id',         // Local key on students
            'hack_contest_id' // Local key on hackathon_registrations
        );
    }

    // Function to fetch registered HackContests
    public function getRegisteredHackContests()
    {
        return $this->hackContests()->get();
    }

    public function eventRegistrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function institute()
    {
        return $this->belongsToThrough(Institute::class, Teacher::class);
    }
}
