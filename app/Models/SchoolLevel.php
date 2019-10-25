<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolLevel extends Model
{
    //
    protected $fillable = ["name", "description"];
    protected $hidden = ['created_at', 'updated_at'];

    public function schools() {
        return $this->hasMany(School::class);
    }
}
