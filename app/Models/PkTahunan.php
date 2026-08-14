<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\CapaianCalculator;

class PkTahunan extends Model
{
    use HasFactory;

    protected $fillable = [
        'indikator_id',
        'tahun',
        'target_awal',
        'target_revisi',
        'status_revisi',
        'alasan_revisi',
    ];

    protected function casts(): array
    {
        return [
            'status_revisi' => 'boolean',
        ];
    }

    public function indikator()
    {
        return $this->belongsTo(Indikator::class);
    }

    public function evaluasiTahunan()
    {
        return $this->hasOne(EvaluasiTahunan::class);
    }

    /**
     * Target efektif: gunakan target revisi jika ada, kalau tidak pakai target awal.
     */
    public function getTargetEfektifAttribute(): float
    {
        return $this->target_revisi ?? $this->target_awal;
    }
}
