<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobListing extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'title',
        'description',
        'type',
        'location',
        'start_date',
        'end_date',
        'application_deadline',
        'special_requirements',
        'skills_required',
        'domain_id',
        'subdomains',
    ];

    protected $casts = [
        'skills_required' => 'array',
        'subdomains' => 'array', // Cast subdomains to an array
        'start_date' => 'date',
        'end_date' => 'date',
        'application_deadline' => 'date',
    ];

    /**
     * Define the relationship between JobListing and Company.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Define the relationship between JobListing and Application.
     */
    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }
}
