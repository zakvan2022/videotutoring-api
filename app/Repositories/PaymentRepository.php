<?php

namespace App\Repositories;

use Illuminate\Http\Request;

use Exception;
use App\Models\Balance;
use App\Models\TransactionType;
use App\Models\Transaction;

class PaymentRepository
{

    /**
     * add Balance
     * @param {integer} userId 
     * @param {float} amount
     */
    public static function updateBalance($userId, $amount) {
        $balance = Balance::firstOrCreate(['user_id'=> $userId]);
        $amount = $balance->amount + $amount;
        $balance->fill(['amount'=> $amount]);
        $balance->save();
    }

    /**
     * add Transaction
     * @param {integer} userId
     * @param {string} transactionType
     * @param {float} amount
     * @param {string} description
     */
    public static function addTransactionHistory($userId, $transactionType, $amount, $description) {
        $transactionType = TransactionType::firstOrCreate(['name'=>$transactionType]);
        $transaction = new Transaction();
        $transaction->fill([
            'user_id'=>$userId,
            'transaction_type_id'=>$transactionType->id,
            'amount'=>$amount,
            'description'=>$description
        ]);
        $transaction->save();
    }
}