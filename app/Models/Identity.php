<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Identity extends Model
{
    //
    protected $fillable = ["upload_url", "verified_at"];
    protected $hidden = ['created_at', 'updated_at'];
    protected $casts = [
        'verified_at' => 'datetime'
    ];
}
