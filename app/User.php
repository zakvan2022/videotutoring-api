<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'password', 'phone', 'avatar', 'user_setting_id', 'address_id', 'default_billing_type', 'default_payment_type'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'remember_token', 'created_at', 'updated_at'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function address() {
        return $this->belongsTo(\App\Models\Address::class);
    }

    public function user_setting(){
        return $this->belongsTo(\App\Models\UserSetting::class);
    }

    public function parent_profile(){
        return $this->hasMany(\App\Models\ParentProfile::class);
    }

    public function student_profile(){
        return $this->hasMany(\App\Models\StudentProfile::class);
    }

    public function tutor_profile(){
        return $this->hasMany(\App\Models\TutorProfile::class);
    }

    public function verification_code() {
        return $this->hasOne(\App\Models\VerificationCode::class);
    }

    public function credit_card_info() {
        return $this->hasOne(\App\Models\CreditCardInfo::class);
    }

    public function paypal_info() {
        return $this->hasOne(\App\Models\PaypalInfo::class);
    }

    public function paypal_withdraw_info() {
        return $this->hasOne(\App\Models\PaypalWithdrawInfo::class);
    }

    public function balance() {
        return $this->hasOne(\App\Models\Balance::class);
    }
}