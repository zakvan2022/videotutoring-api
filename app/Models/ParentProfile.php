<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentProfile extends Model
{
    protected $fillable = ["user_id", "payment_type", "payment_id", "billing_type", "billing_id"];
    protected $hidden = ['created_at', 'updated_at'];
    public function user(){
        return $this->belongsTo(\App\User::class);
    }

    public function children(){
        return $this->hasMany(StudentProfile::class);
    }
}
