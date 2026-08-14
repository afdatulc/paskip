<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KendalaRtl extends Model
{
    use HasFactory;

    protected $fillable = [
        'indikator_id',
        'triwulan',
        'tahun',
        'kendala',
        'solusi',
        'rtl',
        'batas_waktu',
        'pic_nip',
        'status',
    ];

    protected $casts = [
        'batas_waktu' => 'date',
    ];

    public function indikator()
    {
        return $this->belongsTo(Indikator::class);
    }

    public function pic()
    {
        return $this->belongsTo(Pegawai::class, 'pic_nip', 'nip');
    }

    public function executions()
    {
        return $this->hasMany(KendalaRtlExecution::class);
    }
}
