<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    //
    protected $fillable = ["video_class_id", "rating", "description", "user_id"];
    protected $hidden = ['created_at', 'updated_at'];

    public function user(){
        return $this->belongsTo(\App\User::class);
    }

    public function videoclass() {
        return $this->belongsTo(VideoClass::class);
    }
}
