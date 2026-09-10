<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Indikator;
use App\Models\IndikatorAnggaran;
use App\Models\Setting;

class IndikatorAnggaranController extends Controller
{
    public function index(Request $request)
    {
        $tahun = $request->get('tahun', session('global_tahun', date('Y')));
        
        $indikators = Indikator::where('tahun', $tahun)
            ->with(['anggarans' => function($q) use ($tahun) {
                $q->where('tahun', $tahun);
            }])
            ->orderBy('kode')
            ->get();

        // Group by Sasaran (first 3 parts of kode)
        $groupedIndikators = [];
        $sasaranAnggarans = \App\Models\SasaranAnggaran::where('tahun', $tahun)->get()->keyBy('kode');

        foreach ($indikators as $ind) {
            $parts = explode('.', $ind->kode);
            if (count($parts) >= 3) {
                $sasaranKode = implode('.', array_slice($parts, 0, 3));
            } else {
                $sasaranKode = $ind->kode;
            }

            if (!isset($groupedIndikators[$sasaranKode])) {
                $groupedIndikators[$sasaranKode] = [
                    'kode' => $sasaranKode,
                    'sasaran' => $ind->sasaran,
                    'anggaran' => $sasaranAnggarans->get($sasaranKode),
                    'indikators' => []
                ];
            }
            $groupedIndikators[$sasaranKode]['indikators'][] = $ind;
        }

        return view('anggaran.index', compact('groupedIndikators', 'tahun'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'indikator_id' => 'required|exists:indikators,id',
            'tahun' => 'required|integer',
            'pagu_awal' => 'nullable|numeric',
            'pagu_revisi' => 'nullable|numeric',
            'realisasi_tw1' => 'nullable|numeric',
            'realisasi_tw2' => 'nullable|numeric',
            'realisasi_tw3' => 'nullable|numeric',
            'realisasi_tw4' => 'nullable|numeric',
            'kode_kegiatan' => 'nullable|string',
            'nama_kegiatan' => 'nullable|string',
            'kode_ro' => 'nullable|string',
            'nama_ro' => 'nullable|string',
        ]);

        $anggaran = IndikatorAnggaran::updateOrCreate(
            [
                'indikator_id' => $request->indikator_id,
                'tahun' => $request->tahun,
            ],
            [
                'pagu_awal' => $request->pagu_awal ?? 0,
                'pagu_revisi' => $request->pagu_revisi ?? 0,
                'realisasi_tw1' => $request->realisasi_tw1 ?? 0,
                'realisasi_tw2' => $request->realisasi_tw2 ?? 0,
                'realisasi_tw3' => $request->realisasi_tw3 ?? 0,
                'realisasi_tw4' => $request->realisasi_tw4 ?? 0,
                'kode_kegiatan' => $request->kode_kegiatan,
                'nama_kegiatan' => $request->nama_kegiatan,
                'kode_ro' => $request->kode_ro,
                'nama_ro' => $request->nama_ro,
            ]
        );

        if ($request->ajax()) {
            return response()->json(['status' => 'success', 'message' => 'Anggaran berhasil disimpan.']);
        }

        return back()->with('success', 'Anggaran indikator berhasil disimpan.');
    }

    public function storeSasaran(Request $request)
    {
        $request->validate([
            'kode' => 'required|string',
            'tahun' => 'required|integer',
            'pagu_awal' => 'nullable|numeric',
            'pagu_revisi' => 'nullable|numeric',
            'realisasi_tw1' => 'nullable|numeric',
            'realisasi_tw2' => 'nullable|numeric',
            'realisasi_tw3' => 'nullable|numeric',
            'realisasi_tw4' => 'nullable|numeric',
            'kode_kegiatan' => 'nullable|string',
            'nama_kegiatan' => 'nullable|string',
            'kode_ro' => 'nullable|string',
            'nama_ro' => 'nullable|string',
        ]);

        $anggaran = \App\Models\SasaranAnggaran::updateOrCreate(
            [
                'kode' => $request->kode,
                'tahun' => $request->tahun,
            ],
            [
                'pagu_awal' => $request->pagu_awal ?? 0,
                'pagu_revisi' => $request->pagu_revisi ?? 0,
                'realisasi_tw1' => $request->realisasi_tw1 ?? 0,
                'realisasi_tw2' => $request->realisasi_tw2 ?? 0,
                'realisasi_tw3' => $request->realisasi_tw3 ?? 0,
                'realisasi_tw4' => $request->realisasi_tw4 ?? 0,
                'kode_kegiatan' => $request->kode_kegiatan,
                'nama_kegiatan' => $request->nama_kegiatan,
                'kode_ro' => $request->kode_ro,
                'nama_ro' => $request->nama_ro,
            ]
        );

        if ($request->ajax()) {
            return response()->json(['status' => 'success', 'message' => 'Anggaran sasaran berhasil disimpan.']);
        }

        return back()->with('success', 'Anggaran sasaran berhasil disimpan.');
    }

    public function downloadTemplate(Request $request)
    {
        $tahun = $request->get('tahun', session('global_tahun', date('Y')));
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\AnggaranTemplateExport($tahun), 'template_anggaran_'.$tahun.'.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\AnggaranImport, $request->file('file'));
            return redirect()->back()->with('success', 'Data Anggaran & Realisasi berhasil diimport.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Format file tidak sesuai template! (' . $e->getMessage() . ')');
        }
    }
}
