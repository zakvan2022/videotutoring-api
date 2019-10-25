<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    //
    protected $fillable = ["video_class_id", "rating", "description"];
    protected $hidden = ['created_at', 'updated_at'];
}
