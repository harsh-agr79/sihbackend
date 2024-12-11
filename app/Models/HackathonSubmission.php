<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HackathonSubmission extends Model
{
    use HasFactory;

    protected $fillable = ['hackathon_registration_id', 'description', 'link', 'marks'];

    public function hackathonRegistration()
    {
        return $this->belongsTo(HackathonRegistration::class);
    }
}
