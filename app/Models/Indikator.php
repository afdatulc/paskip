<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\CapaianCalculator;

class Indikator extends Model
{
    use HasFactory;
    
    public function getRouteKeyName()
    {
        return 'kode';
    }

    protected $fillable = [
        'kode',
        'kode_tujuan',
        'kode_sasaran',
        'kode_indikator_kinerja',
        'tujuan',
        'sasaran',
        'indikator_kinerja',
        'jenis_indikator',
        'periode',
        'tipe',
        'polarisasi',
        'satuan',
        'target_tahunan',
        'tahun',
        'pic_id',
        'dasar_hitung',
        'basis_data',
        'triwulan',
        'narasi_analisis',
        'kendala',
        'solusi',
        'rencana_tindak_lanjut',
        'pic_tindak_lanjut',
        'batas_waktu',
        'severity',
        'file_bukti_kinerja',
        'file_bukti_tindak_lanjut',
        'definisi_x',
        'definisi_y',
        'target_tahunan_x',
        'target_tahunan_y',
    ];

    public function pic()
    {
        return $this->belongsTo(Pegawai::class, 'pic_id');
    }

    public function target()
    {
        return $this->hasOne(Target::class);
    }

    public function realisasis()
    {
        return $this->hasMany(Realisasi::class);
    }

    public function tabelRos()
    {
        return $this->hasMany(TabelRo::class);
    }

    /**
     * Scope: tampilkan hanya indikator yang boleh dilihat oleh user tertentu.
     * Admin melihat semua, pegawai hanya melihat indikator di mana ia PIC,
     * ketua tim, atau anggota kegiatan.
     */
    public function scopeVisibleTo($query, $user)
    {
        if ($user->isAdmin() || $user->isPimpinan()) {
            return $query;
        }

        $pegawaiId = $user->pegawai_id;
        if (!$pegawaiId) {
            return $query->whereRaw('1 = 0');
        }

        $userPegawai = $user->pegawai;

        return $query->where(function ($q) use ($pegawaiId, $userPegawai) {
            $q->where('pic_id', $pegawaiId)
              ->orWhereHas('anggotas', function ($q2) use ($pegawaiId) {
                  $q2->where('pegawai_id', $pegawaiId);
              })
              ->orWhereHas('kegiatanMasters', function ($q3) use ($pegawaiId) {
                  $q3->where('ketua_tim_id', $pegawaiId)
                     ->orWhereHas('anggotas', function ($q4) use ($pegawaiId) {
                         $q4->where('pegawai_id', $pegawaiId);
                     });
              });

            if ($userPegawai && $userPegawai->seksi) {
                $q->orWhereHas('pic', function ($q5) use ($userPegawai) {
                    $q5->where('seksi', $userPegawai->seksi);
                });
            }
        });
    }

    public function kegiatanMasters()
    {
        return $this->hasMany(KegiatanMaster::class);
    }

    public function anggotas()
    {
        return $this->belongsToMany(Pegawai::class, 'indikator_anggota', 'indikator_id', 'pegawai_id')->withTimestamps();
    }

    public function kendalaRtls()
    {
        return $this->hasMany(KendalaRtl::class);
    }

    public function outputMasters()
    {
        return $this->hasMany(OutputMaster::class);
    }

    public function capaianKinerjas()
    {
        return $this->hasMany(CapaianKinerja::class);
    }

    public function targetRenstras()
    {
        return $this->hasMany(TargetRenstra::class);
    }

    public function pkTahunans()
    {
        return $this->hasMany(PkTahunan::class);
    }

    public function getTargetTahunanEfektifAttribute(): float
    {
        $tahun = $this->tahun ?? date('Y');
        $pk = $this->pkTahunans->firstWhere('tahun', $tahun);
        if (!$pk) {
            $pk = $this->pkTahunans()->where('tahun', $tahun)->first();
        }
        if ($pk) {
            return (float) $pk->target_efektif;
        }
        return (float) $this->target_tahunan;
    }

    public function getDiscrepancyQ4Attribute(): ?array
    {
        $tahun = $this->tahun ?? date('Y');
        $pk = $this->pkTahunans->firstWhere('tahun', $tahun);
        if (!$pk) {
            $pk = $this->pkTahunans()->where('tahun', $tahun)->first();
        }

        $targetTw4 = $this->target ? $this->target->target_tw4 : null;

        if ($pk && !is_null($targetTw4) && (float)$targetTw4 != (float)$pk->target_efektif) {
            return [
                'target_tw4' => (float)$targetTw4,
                'target_pk' => (float)$pk->target_efektif,
                'status_revisi' => $pk->status_revisi,
                'selisih' => abs((float)$targetTw4 - (float)$pk->target_efektif),
            ];
        }

        return null;
    }

    public function getCapaianTahunanAttribute()
    {
        $realisasiTerakhir = $this->realisasis()->orderBy('triwulan', 'desc')->first();
        if (!$realisasiTerakhir) return 0;

        return CapaianCalculator::hitung(
            $this->target_tahunan_efektif,
            $realisasiTerakhir->realisasi_kumulatif,
            $this->polarisasi ?? 'positif'
        );
    }

    public function getStatusWarnaAttribute()
    {
        $capaian = $this->capaian_tahunan;
        if ($capaian >= 100) return 'success';
        if ($capaian >= 80) return 'warning';
        return 'danger';
    }


    public function anggarans()
    {
        return $this->hasMany(IndikatorAnggaran::class, 'indikator_id');
    }
}
