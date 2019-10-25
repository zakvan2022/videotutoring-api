<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    protected $hidden = ['created_at', 'updated_at'];
    //
    public function tutor_profiles(){
        return $this->belongsToMany(TutorProfile::class);
    }
}