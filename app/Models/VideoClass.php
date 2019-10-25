<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoClass extends Model
{
    protected $fillable = ["tutor_profile_id", "student_profile_id", "call_id", "room_id", "name", "description", "started_at", "duration", "ended_at", "price_id", "paid"];
    protected $hidden = ['created_at', 'updated_at'];
    
    protected $casts = [
        'started_at' => 'datetime'
    ];

    public function tutor_profile(){
        return $this->belongsTo(TutorProfile::class);
    }

    public function student_profile(){
        return $this->belongsTo(StudentProfile::class);
    }

    public function feedback(){
        return $this->hasOne(Feedback::class);
    }

    public function price() {
        return $this->belongsTo(Price::class);
    }
}
