<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone_number',
        'name',
        'operator',
        'is_valid',
    ];

    protected $casts = [
        'is_valid' => 'boolean',
    ];

    public function transfers()
    {
        return $this->hasMany(Transfer::class, 'recipient_phone_number', 'phone_number');
    }
}
