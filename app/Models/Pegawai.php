<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    protected $table = 'pegawai';

    protected $fillable = [
        'nama_pegawai',
        'jabatan',
        'no_hp',
        'email',
        'alamat',
        'status',
    ];
}
