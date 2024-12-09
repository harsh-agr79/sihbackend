<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'type',
        'title',
        'link',
        'datetime',
        'speaker',
        'description',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

}
