<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecuritySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'biometrics_active',
        'failed_attempts',
        'unlock_date',
    ];

    protected $casts = [
        'biometrics_active' => 'boolean',
        'unlock_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
