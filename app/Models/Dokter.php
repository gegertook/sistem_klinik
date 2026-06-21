<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dokter extends Model
{
    protected $table = 'dokter';

    protected $fillable = [
        'poli_id',
        'nama_dokter',
        'kode_dokter',
        'spesialisasi',
        'no_hp',
        'status',
    ];

    public function poli(): BelongsTo
    {
        return $this->belongsTo(Poli::class, 'poli_id');
    }

    public function jadwal(): HasMany
    {
        return $this->hasMany(JadwalDokter::class, 'dokter_id');
    }

    public function kunjungan(): HasMany
    {
        return $this->hasMany(Kunjungan::class, 'dokter_id');
    }
}
