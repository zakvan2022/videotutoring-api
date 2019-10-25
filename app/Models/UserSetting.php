<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    //
    protected $fillable = ['notification'];
    protected $hidden = ['created_at', 'updated_at'];
}
