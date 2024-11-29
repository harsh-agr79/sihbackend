<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Student;

class Object3d extends Model
{
    use HasFactory;

    protected $fillable = ['mentor_id', 'name', 'file_path'];

    public function mentor()
    {
        return $this->belongsTo(Mentor::class);
    }
}
