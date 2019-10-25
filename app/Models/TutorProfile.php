<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TutorProfile extends Model
{
    //
    protected $fillable = ["user_id", "availability", "rating", "level", "identity_id", "w9form_id", "payment_type", "payment_id", "billing_type", "billing_id", "classes_passed", "total_time"];
    protected $hidden = ['created_at', 'updated_at'];
    
    public function user(){
        return $this->belongsTo(\App\User::class);
    }

    public function identity(){
        return $this->belongsTo(Identity::class);
    }

    public function topics(){
        return $this->belongsToMany(Topic::class);
    }

    public function degrees(){
        return $this->hasMany(Degree::class);
    }

    public function prices(){
        return $this->belongsToMany(Price::class);
    }
    
    public function w9form(){
        return $this->belongsTo(W9Form::class);
    }

    public function video_classes() {
        return $this->hasMany(VideoClass::class);
    }
}
