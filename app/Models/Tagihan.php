<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tagihan extends Model
{
    protected $table = 'tagihan';

    protected $fillable = [
        'no_tagihan',
        'kunjungan_id',
        'total_tagihan',
        'status_pembayaran',
        'metode_pembayaran',
        'snap_token',
        'midtrans_order_id',
        'tanggal_tagihan',
        'tanggal_bayar',
    ];

    protected function casts(): array
    {
        return [
            'total_tagihan' => 'integer',
            'tanggal_tagihan' => 'date',
            'tanggal_bayar' => 'datetime',
        ];
    }

    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(Kunjungan::class, 'kunjungan_id');
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'tagihan_id');
    }
}
