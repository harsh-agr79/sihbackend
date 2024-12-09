<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HackContest extends Model
{
    use HasFactory;


    protected $fillable = [
        'company_id',
        'title',
        'description',
        'problem_statement',
        'evaluation_criteria',
        'eligibility',
        'start_date_time',
        'end_date_time',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function registrations()
    {
        return $this->hasMany(HackathonRegistration::class);
    }

    // Shortcut to fetch all submissions through registrations
    public function submissions()
    {
        return $this->hasManyThrough(
            HackathonSubmission::class,
            HackathonRegistration::class,
            'hack_contest_id', // Foreign key on hackathon_registrations
            'hackathon_registration_id', // Foreign key on submissions
            'id', // Local key on hack_contests
            'id'  // Local key on hackathon_registrations
        );
    }
}
