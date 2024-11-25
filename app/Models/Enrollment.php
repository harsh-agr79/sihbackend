<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'student_id',
        'course_id',
        'current_module_id',
        'enrollment_date',
        'completed_at',
    ];

    /**
     * Get the student associated with the enrollment.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the course associated with the enrollment.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the current module associated with the enrollment.
     */
    public function currentModule()
    {
        return $this->belongsTo(Module::class, 'current_module_id');
    }
}

