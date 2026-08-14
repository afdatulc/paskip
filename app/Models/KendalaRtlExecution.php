<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KendalaRtlExecution extends Model
{
    use HasFactory;

    protected $fillable = [
        'kendala_rtl_id',
        'narasi_tindak_lanjut',
        'tanggal_pelaksanaan',
        'is_dokumentasi_ada',
        'is_timestamp_ada',
        'foto_bukti',
        'pegawai_nip',
    ];

    protected $casts = [
        'tanggal_pelaksanaan' => 'date',
        'is_dokumentasi_ada' => 'boolean',
        'is_timestamp_ada' => 'boolean',
    ];

    public function kendalaRtl()
    {
        return $this->belongsTo(KendalaRtl::class);
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_nip', 'nip');
    }
}
