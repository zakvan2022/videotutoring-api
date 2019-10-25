<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    //
    protected $fillable = ["name", "address_id", "schoollevel_id"];
    protected $hidden = ['created_at', 'updated_at'];
    
    public function address(){
        return $this->belongsTo(Address::class);
    }

    public function schoollevel() {
        return $this->belongsTo(SchoolLevel::class);
    }
}