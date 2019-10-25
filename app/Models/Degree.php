<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Degree extends Model
{
    //
    protected $fillable = ["tutor_profile_id", "school_id", "upload_url", "verified_at", "basic_degree_id", "minor_id", "year"];
    protected $hidden = ['created_at', 'updated_at'];
    protected $casts = [
        'verified_at' => 'datetime'
    ];
    
    public function tutor_profile(){
        return $this->belongsTo(TutorProfile::class);
    }

    public function school(){
        return $this->belongsTo(School::class);
    }
    
    public function minor(){
        return $this->belongsTo(Minor::class);
    }

    public function basic_degree() {
        return $this->belongsTo(BasicDegree::class);
    }
}
