<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BasicDegree extends Model
{
    //
    protected $fillable = ["name", "description"];
    
    protected $hidden = ['created_at', 'updated_at'];

    public function degrees() {
        return $this->hasMany(Degree::class);
    }
}