<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Layanan extends Model
{
    protected $table = 'layanan';

    protected $fillable = ['poli_id', 'nama_layanan', 'kategori', 'harga', 'status'];

    protected function casts(): array
    {
        return [
            'harga' => 'integer',
        ];
    }

    public function poli(): BelongsTo
    {
        return $this->belongsTo(Poli::class, 'poli_id');
    }

    public function detailPemeriksaan(): HasMany
    {
        return $this->hasMany(DetailPemeriksaanLayanan::class, 'layanan_id');
    }
}
