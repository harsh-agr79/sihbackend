<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_listing_id',
        'student_id',
        'cover_letter',
        'additional_files',
        'status',
        'shortlisted',
        'final_selected',
    ];

    protected $casts = [
        'additional_files' => 'array',
        'shortlisted' => 'boolean',
        'final_selected' => 'boolean',
    ];

    /**
     * Define the relationship between Application and JobListing.
     */
    public function jobListing()
    {
        return $this->belongsTo(JobListing::class);
    }

    /**
     * Define the relationship between Application and Student.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
