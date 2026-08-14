<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluasiTahunan extends Model
{
    use HasFactory;

    protected $fillable = [
        'pk_tahunan_id',
        'realisasi_akhir',
        'capaian_persentase',
        'analisis_hasil',
        'rekomendasi_revisi',
        'catatan_revisi',
    ];

    protected function casts(): array
    {
        return [
            'rekomendasi_revisi' => 'boolean',
        ];
    }

    public function pkTahunan()
    {
        return $this->belongsTo(PkTahunan::class);
    }

    /**
     * Accessor: Status warna berdasarkan capaian persentase.
     */
    public function getStatusWarnaAttribute(): string
    {
        if ($this->capaian_persentase >= 100) return 'success';
        if ($this->capaian_persentase >= 80) return 'warning';
        return 'danger';
    }

    /**
     * Accessor: Label status capaian.
     */
    public function getLabelCapaianAttribute(): string
    {
        if ($this->capaian_persentase >= 100) return 'Tercapai';
        if ($this->capaian_persentase >= 80) return 'Perlu Perhatian';
        return 'Tidak Tercapai';
    }
}
