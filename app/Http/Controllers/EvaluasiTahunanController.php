<?php

namespace App\Http\Controllers;

use App\Models\EvaluasiTahunan;
use App\Models\PkTahunan;
use App\Services\CapaianCalculator;
use Illuminate\Http\Request;

class EvaluasiTahunanController extends Controller
{
    /**
     * Daftar evaluasi tahunan.
     */
    public function index(Request $request)
    {
        $tahun = $request->get('tahun', session('global_tahun', date('Y')));

        $pkTahunans = PkTahunan::where('tahun', $tahun)
            ->with(['indikator', 'evaluasiTahunan'])
            ->get();

        // Hitung capaian aktual untuk setiap PK
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

        // Summary evaluasi
        $totalDievaluasi = $pkTahunans->filter(fn($pk) => $pk->evaluasiTahunan)->count();
        $totalRekomenRevisi = $pkTahunans->filter(
            fn($pk) => $pk->evaluasiTahunan && $pk->evaluasiTahunan->rekomendasi_revisi
        )->count();

        $summary = [
            'total_pk' => $pkTahunans->count(),
            'sudah_evaluasi' => $totalDievaluasi,
            'belum_evaluasi' => $pkTahunans->count() - $totalDievaluasi,
            'rekomendasi_revisi' => $totalRekomenRevisi,
        ];

        return view('evaluasi_tahunan.index', compact('pkTahunans', 'tahun', 'summary'));
    }

    /**
     * Simpan/update evaluasi tahunan.
     */
    public function store(Request $request)
    {
        $request->validate([
            'pk_tahunan_id' => 'required|exists:pk_tahunans,id',
            'realisasi_akhir' => 'required|numeric|min:0',
            'analisis_hasil' => 'required|string',
            'rekomendasi_revisi' => 'boolean',
            'catatan_revisi' => 'nullable|required_if:rekomendasi_revisi,1|string',
        ]);

        $pkTahunan = PkTahunan::with('indikator')->findOrFail($request->pk_tahunan_id);

        // Hitung capaian otomatis
        $capaianPersentase = CapaianCalculator::hitung(
            $pkTahunan->target_efektif,
            $request->realisasi_akhir,
            $pkTahunan->indikator->polarisasi ?? 'positif'
        );

        EvaluasiTahunan::updateOrCreate(
            ['pk_tahunan_id' => $pkTahunan->id],
            [
                'realisasi_akhir' => $request->realisasi_akhir,
                'capaian_persentase' => $capaianPersentase,
                'analisis_hasil' => $request->analisis_hasil,
                'rekomendasi_revisi' => $request->boolean('rekomendasi_revisi'),
                'catatan_revisi' => $request->catatan_revisi,
            ]
        );

        return back()->with('success', 'Evaluasi tahunan berhasil disimpan.');
    }
}
