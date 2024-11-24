<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function subdomains()
    {
        return $this->hasMany(Subdomain::class);
    }
}
