<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditCardInfo extends Model
{
    //
    protected $fillable = ["user_id", "stripe_customer_id"];
    protected $hidden = ['created_at', 'updated_at'];
}
