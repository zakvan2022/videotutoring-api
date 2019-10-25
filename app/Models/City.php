<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    //
    protected $fillable = ["name", "state_id"];
    protected $hidden = ['created_at', 'updated_at'];
    
    public function state(){
        return $this->belongsTo(State::class);
    }
}
