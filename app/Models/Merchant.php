<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Merchant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone_number',
        'address',
        'logo',
        'is_active',
        'accepts_qr',
        'accepts_code',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'accepts_qr' => 'boolean',
        'accepts_code' => 'boolean',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function qrCodes()
    {
        return $this->hasMany(QrCode::class);
    }

    public function paymentCodes()
    {
        return $this->hasMany(PaymentCode::class);
    }
}
