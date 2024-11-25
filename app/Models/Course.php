<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'mentor_id',
        'title',
        'description',
        'domain_id',
        'subdomains',
        'level',
        'verified',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'subdomains' => 'array',
    ];

    /**
     * Get the mentor associated with the course.
     */
    public function mentor()
    {
        return $this->belongsTo(Mentor::class);
    }

    /**
     * Get the domain associated with the course.
     */
    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }

    /**
     * Get the modules associated with the course.
     */
    public function modules()
    {
        return $this->hasMany(Module::class);
    }

    /**
     * Get the module groups associated with the course.
     */
    public function moduleGroups()
    {
        return $this->hasMany(ModuleGroup::class);
    }

    /**
     * Get the enrollments associated with the course.
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function ungroupedModules()
    {
        return $this->modules()->whereNull('module_group_id')->get();
    }
}

