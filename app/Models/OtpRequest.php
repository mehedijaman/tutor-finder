<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpRequest extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'phone',
        'purpose',
        'otp_hash',
        'expires_at',
        'attempts',
        'sent_count',
        'ip',
        'user_agent',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }
}
