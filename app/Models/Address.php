<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    //
    protected $fillable = ["address1", "address2", "city_id", "zipcode"];
    protected $hidden = ['created_at', 'updated_at'];
    
    public function city(){
        return $this->belongsTo(City::class);
    }
}
