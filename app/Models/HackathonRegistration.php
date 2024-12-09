<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HackathonRegistration extends Model
{
    use HasFactory;

    protected $fillable = ['hack_contest_id', 'student_id'];

    public function hackContest()
    {
        return $this->belongsTo(HackContest::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function submissions()
    {
        return $this->hasMany(HackathonSubmission::class);
    }
}
