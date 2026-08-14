<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\CapaianCalculator;

class Realisasi extends Model
{
    use HasFactory;

    protected $fillable = [
        'indikator_id', 'triwulan', 'realisasi_kumulatif',
        'realisasi_x', 'realisasi_y',
    ];

    /**
     * Audit log perubahan nilai realisasi.
     */
    public function logs()
    {
        return $this->hasMany(RealisasiLog::class);
    }

    public function indikator()
    {
        return $this->belongsTo(Indikator::class);
    }

    public function getCapaianTriwulanAttribute()
    {
        $target = $this->indikator->target;
        $targetField = 'target_tw' . $this->triwulan;
        $targetVal = $target ? $target->$targetField : 0;
        $polarisasi = $this->indikator->polarisasi ?? 'positif';

        return CapaianCalculator::hitung($targetVal, $this->realisasi_kumulatif, $polarisasi);
    }

    /**
     * Capaian berbasis X/Y jika tersedia.
     */
    public function getCapaianXyAttribute(): ?float
    {
        if ($this->realisasi_y && $this->realisasi_y > 0) {
            return round(($this->realisasi_x / $this->realisasi_y) * 100, 2);
        }
        return null;
    }
}

