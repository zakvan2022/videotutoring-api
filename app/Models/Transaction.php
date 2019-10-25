<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    //
    protected $fillable = ["user_id", "transaction_type_id", "amount", "description"];
    protected $hidden = ['created_at', 'updated_at'];

    public function transaction_type() {
        return $this->belongsTo(TransactionType::class);
    }
    public function user() {
        return $this->belongsTo(\App\User::class);
    }
}
