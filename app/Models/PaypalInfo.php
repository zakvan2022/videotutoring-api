<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaypalInfo extends Model
{
    //
    protected $fillable = ["user_id", "payer_id", "email", "preapproval_id", "active"];
    protected $hidden = ['created_at', 'updated_at'];
}
