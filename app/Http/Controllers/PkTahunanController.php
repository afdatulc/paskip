<?php

namespace App\Http\Controllers;

use App\Models\PkTahunan;
use App\Models\Indikator;
use App\Models\TargetRenstra;
use App\Models\Renstra;
use App\Services\CapaianCalculator;
use Illuminate\Http\Request;

class PkTahunanController extends Controller
{
    /**
     * Daftar PK Tahunan per tahun.
     */
    public function index(Request $request)
    {
        $tahun = $request->get('tahun', session('global_tahun', date('Y')));
        
        $pkTahunans = PkTahunan::where('tahun', $tahun)
            ->with(['indikator', 'evaluasiTahunan'])
            ->get();

        // Hitung capaian untuk setiap PK
        $pkTahunans->each(function ($pk) {
            $realisasiTerakhir = $pk->indikator->realisasis()
                ->orderBy('triwulan', 'desc')->first();
            
            $pk->realisasi_saat_ini = $realisasiTerakhir 
                ? $realisasiTerakhir->realisasi_kumulatif 
                : 0;
            
            $pk->capaian = CapaianCalculator::hitung(
                $pk->target_efektif,
                $pk->realisasi_saat_ini,
                $pk->indikator->polarisasi ?? 'positif'
            );
        });

        // Summary operasional
        $summary = [
            'total' => $pkTahunans->count(),
            'direvisi' => $pkTahunans->where('status_revisi', true)->count(),
            'sudah_realisasi' => $pkTahunans->where('realisasi_saat_ini', '>', 0)->count(),
            'belum_realisasi' => $pkTahunans->where('realisasi_saat_ini', 0)->count(),
        ];

        return view('pk_tahunan.index', compact('pkTahunans', 'tahun', 'summary'));
    }

    /**
     * Generate PK Tahunan dari Target Renstra.
     */
    public function generate(Request $request)
    {
        $request->validate([
            'tahun' => 'required|integer',
        ]);

        $tahun = $request->tahun;
        $renstra = Renstra::aktif()->first();

        if (!$renstra) {
            return back()->with('error', 'Tidak ada Renstra aktif. Silakan aktifkan Renstra terlebih dahulu.');
        }

        $targets = TargetRenstra::where('renstra_id', $renstra->id)
            ->where('tahun', $tahun)
            ->get();

        if ($targets->isEmpty()) {
            return back()->with('error', "Tidak ada target Renstra untuk tahun {$tahun}.");
        }

        $created = 0;
        foreach ($targets as $target) {
            PkTahunan::firstOrCreate(
                [
                    'indikator_id' => $target->indikator_id,
                    'tahun' => $tahun,
                ],
                [
                    'target_awal' => $target->target,
                    'status_revisi' => false,
                ]
            );
            $created++;
        }

        return back()->with('success', "{$created} PK Tahunan berhasil di-generate dari Renstra {$renstra->periode}.");
    }

    /**
     * Update target revisi PK Tahunan.
     */
    public function update(Request $request, PkTahunan $pk_tahunan)
    {
        $request->validate([
            'target_revisi' => 'required|numeric|min:0',
            'alasan_revisi' => 'required|string',
        ]);

        $pk_tahunan->update([
            'target_revisi' => $request->target_revisi,
            'status_revisi' => true,
            'alasan_revisi' => $request->alasan_revisi,
        ]);

        return back()->with('success', 'Target PK berhasil direvisi.');
    }

    /**
     * Batalkan revisi (kembalikan ke target awal).
     */
    public function cancelRevisi(PkTahunan $pk_tahunan)
    {
        $pk_tahunan->update([
            'target_revisi' => null,
            'status_revisi' => false,
            'alasan_revisi' => null,
        ]);

        return back()->with('success', 'Revisi target dibatalkan, kembali ke target awal Renstra.');
    }
}
