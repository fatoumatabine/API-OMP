<?php

namespace App\Models;

use App\Http\Traits\HasWalletAttribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, HasUuids, HasWalletAttribute;

    protected $fillable = [
        'phone_number',
        'first_name',
        'last_name',
        'email',
        'password',
        'pin_code',
        'cni_number',
        'kyc_status',
        'biometrics_active',
        'balance',
        'status'
    ];

    protected $hidden = [
        'password',
        'pin_code',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'biometrics_active' => 'boolean',
        'balance' => 'decimal:2',
        'pin_code' => 'hashed',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function authentication()
    {
        return $this->hasOne(Authentication::class);
    }

    public function securitySetting()
    {
        return $this->hasOne(SecuritySetting::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function history()
    {
        return $this->hasOne(History::class);
    }
}
