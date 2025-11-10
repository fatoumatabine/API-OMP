<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'merchant_id',
        'payment_method',
        'transaction_code',
        'payment_details',
    ];

    protected $casts = [
        'payment_details' => 'array',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function qrCode()
    {
        return $this->hasOne(QrCode::class, 'id', 'transaction_code'); // Assuming transaction_code stores qr_code id
    }

    public function paymentCode()
    {
        return $this->hasOne(PaymentCode::class, 'id', 'transaction_code'); // Assuming transaction_code stores payment_code id
    }
}
