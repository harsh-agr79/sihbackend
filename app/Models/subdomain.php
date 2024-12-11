<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subdomain extends Model
{
    use HasFactory;

    protected $fillable = ['domain_id', 'name', 'description'];

    public function domain()
    {
        return $this->belongsTo(Domain::class);
    }

    public function communities()
    {
        return $this->belongsToMany(Community::class, 'subdomains', 'subdomain_id', 'community_id');
    }
}
