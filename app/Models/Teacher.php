<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;

class Teacher extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name', 'email', 'password','verification_token','email_verified_at','institute_id'
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

    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
