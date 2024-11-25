<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'assignment_id',
        'student_id',
        'submission_content',
        'submitted_at',
        'grade',
        'graded_at',
        'feedback',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'submission_content' => 'array',
    ];

    /**
     * Get the assignment or quiz associated with the submission.
     */
    public function assignment()
    {
        return $this->belongsTo(AssignmentQuiz::class);
    }

    /**
     * Get the student who made the submission.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}

