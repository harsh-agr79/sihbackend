<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'course_id',
        'group_id',
        'title',
        'video_url',
        'description',
        'transcript',
        'material_links',
        'position',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'material_links' => 'array',
    ];

    /**
     * Get the course that this module belongs to.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the group that this module belongs to, if any.
     */
    public function group()
    {
        return $this->belongsTo(ModuleGroup::class, 'group_id');
    }

    /**
     * Get the assignments or quizzes associated with this module.
     */
    public function assignmentsQuizzes()
    {
        return $this->hasMany(AssignmentQuiz::class);
    }

    /**
     * Get the enrollments that are currently at this module.
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'current_module_id');
    }
}
