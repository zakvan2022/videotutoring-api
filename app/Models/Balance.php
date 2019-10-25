<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
    //
    protected $fillable = ["user_id", "amount"];
    protected $hidden = ['created_at', 'updated_at'];

    public function user(){
        return $this->belongsTo(\App\User::class);
    }
}
