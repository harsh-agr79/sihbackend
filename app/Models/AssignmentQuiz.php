<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignmentQuiz extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'module_id',
        'type',
        'title',
        'description',
        'content',
        'due_date',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'content' => 'array',
    ];

    /**
     * Get the module that this assignment or quiz belongs to.
     */
    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Get the submissions for this assignment or quiz.
     */
    public function submissions()
    {
        return $this->hasMany(Submission::class, 'assignment_id');
    }
}
