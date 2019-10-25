<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    protected $fillable = ["user_id", "school_id", "parent_profile_id", "level", "rating", "availability", "classes_passed", "total_time"];
    protected $hidden = ['created_at', 'updated_at'];
    public function user(){
        return $this->belongsTo(\App\User::class);
    }

    public function parent_profile(){
        return $this->belongsTo(ParentProfile::class);
    }

    public function school(){
        return $this->belongsTo(School::class);
    }

    public function topics(){
        return $this->belongsToMany(Topic::class);
    }

    public function video_classes() {
        return $this->hasMany(VideoClass::class);
    }
}
