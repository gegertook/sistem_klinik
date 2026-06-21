<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';

    protected $fillable = [
        'tagihan_id',
        'order_id',
        'payment_type',
        'transaction_status',
        'transaction_time',
        'gross_amount',
        'fraud_status',
        'response_midtrans',
    ];

    protected function casts(): array
    {
        return [
            'transaction_time' => 'datetime',
            'gross_amount' => 'integer',
            'response_midtrans' => 'array',
        ];
    }

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class, 'tagihan_id');
    }
}
