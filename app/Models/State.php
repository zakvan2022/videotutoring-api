<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    //
    protected $fillable = ["name", "country_id"];
    protected $hidden = ['created_at', 'updated_at'];
    public function country(){
        return $this->belongsTo(Country::class);
    }
}
