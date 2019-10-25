<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Minor extends Model
{
    //
    protected $fillable = ["name"];
    protected $hidden = ['created_at', 'updated_at'];
    public function topics(){
        return $this->belongsToMany(Topic::class);
    }
}
