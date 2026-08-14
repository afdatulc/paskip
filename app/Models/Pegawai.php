<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    use HasFactory;

    protected $fillable = [
        'nip',
        'nama',
        'email_bps',
        'jabatan',
        'pangkat_golongan',
        'unit_kerja',
        'status',
        'seksi',
        'no_hp',
    ];

    public function user()
    {
        return $this->hasOne(User::class);
    }



    public function analisis()
    {
        return $this->hasMany(Analisis::class, 'pegawai_nip', 'nip');
    }
}
