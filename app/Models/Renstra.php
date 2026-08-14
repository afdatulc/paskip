<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Renstra extends Model
{
    use HasFactory;

    protected $fillable = [
        'tahun_awal',
        'tahun_akhir',
        'status',
        'deskripsi',
    ];

    /**
     * Target Renstra per-tahun per-IKU.
     */
    public function targetRenstras()
    {
        return $this->hasMany(TargetRenstra::class);
    }

    /**
     * Accessor: Apakah Renstra ini aktif?
     */
    public function getIsAktifAttribute(): bool
    {
        return $this->status === 'aktif';
    }

    /**
     * Accessor: Format periode "2025-2029".
     */
    public function getPeriodeAttribute(): string
    {
        return $this->tahun_awal . '-' . $this->tahun_akhir;
    }

    /**
     * Accessor: Daftar tahun dalam periode Renstra.
     */
    public function getDaftarTahunAttribute(): array
    {
        return range($this->tahun_awal, $this->tahun_akhir);
    }

    /**
     * Scope: Hanya Renstra aktif.
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }
}
