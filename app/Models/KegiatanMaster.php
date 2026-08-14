<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KegiatanMaster extends Model
{
    use HasFactory;

    protected $table = 'kegiatan_masters';

    protected $fillable = [
        'indikator_id',
        'nama_kegiatan',
        'tahapan_json',
        'ketua_tim_id',
    ];

    protected $casts = [
        'tahapan_json' => 'array',
    ];

    public function indikator()
    {
        return $this->belongsTo(Indikator::class);
    }

    public function ketuaTim()
    {
        return $this->belongsTo(Pegawai::class, 'ketua_tim_id');
    }

    public function anggotas()
    {
        return $this->belongsToMany(Pegawai::class, 'kegiatan_anggotas', 'kegiatan_master_id', 'pegawai_id');
    }


}
