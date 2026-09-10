<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CapaianKinerjaHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'capaian_kinerja_id',
        'realisasi_kumulatif',
        'realisasi_x',
        'realisasi_y',
        'link_bukti_kinerja',
        'link_bukti_tindak_lanjut',
        'penjelasan_lainnya',
        'dasar_hitung',
        'argumen_logis',
        'target_realisasi',
        'catatan_pimpinan',
    ];

    public function capaianKinerja()
    {
        return $this->belongsTo(CapaianKinerja::class);
    }
}
