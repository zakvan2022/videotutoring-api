<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaypalWithdrawInfo extends Model
{
    //
    protected $fillable = ["user_id", "email"];
    protected $hidden = ['created_at', 'updated_at'];
}
