<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pemeriksaan extends Model
{
    protected $table = 'pemeriksaan';

    protected $fillable = [
        'kunjungan_id',
        'dokter_id',
        'diagnosa',
        'catatan_pemeriksaan',
        'resep',
    ];

    public function kunjungan(): BelongsTo
    {
        return $this->belongsTo(Kunjungan::class, 'kunjungan_id');
    }

    public function dokter(): BelongsTo
    {
        return $this->belongsTo(Dokter::class, 'dokter_id');
    }

    public function detailLayanan(): HasMany
    {
        return $this->hasMany(DetailPemeriksaanLayanan::class, 'pemeriksaan_id');
    }
}
