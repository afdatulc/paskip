<?php

namespace App\Http\Controllers;

use App\Models\Renstra;
use App\Models\TargetRenstra;
use App\Models\Indikator;
use App\Services\CapaianCalculator;
use App\Exports\TargetRenstraTemplateExport;
use App\Imports\TargetRenstraImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class RenstraController extends Controller
{
    /**
     * Daftar semua periode Renstra (aktif + historis).
     */
    public function index()
    {
        $renstras = Renstra::orderBy('tahun_awal', 'desc')->get();
        
        return view('renstra.index', compact('renstras'));
    }

    /**
     * Form tambah Renstra baru.
     */
    public function create()
    {
        return view('renstra.create');
    }

    /**
     * Simpan Renstra baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tahun_awal' => 'required|integer|min:2020|max:2040',
            'tahun_akhir' => 'required|integer|gt:tahun_awal',
            'deskripsi' => 'nullable|string',
        ]);

        Renstra::create($request->only('tahun_awal', 'tahun_akhir', 'deskripsi'));

        return redirect()->route('renstra.index')
            ->with('success', 'Periode Renstra berhasil ditambahkan.');
    }

    /**
     * Detail Renstra: target per-IKU per-tahun + grafik tren.
     */
    public function show(Renstra $renstra)
    {
        $tahunList = $renstra->daftar_tahun;
        
        // Ambil semua indikator yang punya target di Renstra ini
        $targetRenstras = TargetRenstra::where('renstra_id', $renstra->id)
            ->with('indikator')
            ->orderBy('indikator_id')
            ->orderBy('tahun')
            ->get();

        // Group by indikator
        $grouped = $targetRenstras->groupBy('indikator_id');
        
        // Ambil realisasi tahunan untuk perbandingan
        $indikatorIds = $targetRenstras->pluck('indikator_id')->unique();
        $indikators = Indikator::whereIn('id', $indikatorIds)
            ->with(['realisasis', 'target'])
            ->get()
            ->keyBy('id');

        // Hitung capaian per-tahun per-IKU untuk grafik tren
        $trendData = [];
        foreach ($grouped as $indikatorId => $targets) {
            $indikator = $indikators->get($indikatorId);
            if (!$indikator) continue;
            
            $trendData[$indikatorId] = [
                'nama' => $indikator->indikator_kinerja,
                'kode' => $indikator->kode ?? '',
                'targets' => [],
                'realisasis' => [],
                'capaians' => [],
            ];
            
            foreach ($targets as $tr) {
                $trendData[$indikatorId]['targets'][$tr->tahun] = $tr->target;
                
                // Cari realisasi kumulatif terakhir di tahun tersebut
                $realisasiTerakhir = $indikator->realisasis
                    ->sortByDesc('triwulan')
                    ->first();
                
                $realisasiVal = $realisasiTerakhir ? $realisasiTerakhir->realisasi_kumulatif : 0;
                $trendData[$indikatorId]['realisasis'][$tr->tahun] = $realisasiVal;
                
                $trendData[$indikatorId]['capaians'][$tr->tahun] = CapaianCalculator::hitung(
                    $tr->target,
                    $realisasiVal,
                    $indikator->polarisasi ?? 'positif'
                );
            }
        }

        // Semua indikator untuk form input target
        $semuaIndikator = Indikator::orderBy('kode')->get();

        return view('renstra.show', compact(
            'renstra', 'tahunList', 'grouped', 'indikators', 'trendData', 'semuaIndikator'
        ));
    }

    /**
     * Simpan/update target Renstra per-IKU per-tahun.
     */
    public function storeTarget(Request $request, Renstra $renstra)
    {
        $request->validate([
            'indikator_id' => 'required|exists:indikators,id',
            'targets' => 'required|array',
            'targets.*' => 'nullable|numeric|min:0',
        ]);

        foreach ($request->targets as $tahun => $target) {
            if (!is_null($target)) {
                TargetRenstra::updateOrCreate(
                    [
                        'renstra_id' => $renstra->id,
                        'indikator_id' => $request->indikator_id,
                        'tahun' => $tahun,
                    ],
                    ['target' => $target]
                );
            }
        }

        return back()->with('success', 'Target Renstra berhasil disimpan.');
    }

    /**
     * Aktifkan Renstra (nonaktifkan yang lain).
     */
    public function activate(Renstra $renstra)
    {
        // Nonaktifkan semua
        Renstra::query()->update(['status' => 'nonaktif']);
        
        // Aktifkan yang dipilih
        $renstra->update(['status' => 'aktif']);

        return back()->with('success', "Renstra {$renstra->periode} berhasil diaktifkan.");
    }

    /**
     * Hapus Renstra beserta target-nya.
     */
    public function destroy(Renstra $renstra)
    {
        $renstra->delete();
        return redirect()->route('renstra.index')
            ->with('success', 'Periode Renstra berhasil dihapus.');
    }

    /**
     * Download template import Target Renstra berisi IKU.
     */
    public function downloadTemplate(Renstra $renstra)
    {
        return Excel::download(
            new TargetRenstraTemplateExport($renstra),
            "Template_Target_Renstra_{$renstra->periode}.xlsx"
        );
    }

    /**
     * Import target Renstra dari file Excel.
     */
    public function importTarget(Request $request, Renstra $renstra)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        Excel::import(new TargetRenstraImport($renstra), $request->file('file'));

        return back()->with('success', 'Target Renstra berhasil diimport dari file Excel.');
    }
}
