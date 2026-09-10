<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TabelRo;
use App\Models\Indikator;
use App\Imports\TabelRoImport;
use Maatwebsite\Excel\Facades\Excel;

class TabelRoController extends Controller
{
    public function index(Request $request)
    {
        $query = TabelRo::with('indikator');
        
        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }
        if ($request->filled('triwulan')) {
            $query->where('triwulan', $request->triwulan);
        }
        
        $ros = $query->latest()->paginate(15);
        return view('tabel_ro.index', compact('ros'));
    }

    public function create()
    {
        $indikators = Indikator::orderBy('kode', 'asc')->get();
        return view('tabel_ro.create', compact('indikators'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'indikator_id' => 'required|exists:indikators,id',
            'tahun' => 'required|integer',
            'triwulan' => 'required|integer|between:1,4',
            'ro' => 'required|string|max:255',
            'realisasi_volume_ro' => 'nullable|numeric',
            'progres_ro' => 'nullable|numeric',
            'pagu_awal' => 'nullable|numeric',
            'pagu_revisi' => 'nullable|numeric',
            'pagu_sisa' => 'nullable|numeric',
            'pagu_realisasi' => 'nullable|numeric',
        ]);

        // Default 0 for nulls
        $fields = ['realisasi_volume_ro', 'progres_ro', 'pagu_awal', 'pagu_revisi', 'pagu_sisa', 'pagu_realisasi'];
        foreach ($fields as $field) {
            $validated[$field] = $validated[$field] ?? 0;
        }

        TabelRo::create($validated);
        return redirect()->route('tabel-ro.index')->with('success', 'Rincian Output (RO) berhasil ditambahkan.');
    }

    public function edit(TabelRo $tabel_ro)
    {
        $indikators = Indikator::orderBy('kode', 'asc')->get();
        return view('tabel_ro.edit', compact('tabel_ro', 'indikators'));
    }

    public function update(Request $request, TabelRo $tabel_ro)
    {
        $validated = $request->validate([
            'indikator_id' => 'required|exists:indikators,id',
            'tahun' => 'required|integer',
            'triwulan' => 'required|integer|between:1,4',
            'ro' => 'required|string|max:255',
            'realisasi_volume_ro' => 'nullable|numeric',
            'progres_ro' => 'nullable|numeric',
            'pagu_awal' => 'nullable|numeric',
            'pagu_revisi' => 'nullable|numeric',
            'pagu_sisa' => 'nullable|numeric',
            'pagu_realisasi' => 'nullable|numeric',
        ]);

        $fields = ['realisasi_volume_ro', 'progres_ro', 'pagu_awal', 'pagu_revisi', 'pagu_sisa', 'pagu_realisasi'];
        foreach ($fields as $field) {
            $validated[$field] = $validated[$field] ?? 0;
        }

        $tabel_ro->update($validated);
        return redirect()->route('tabel-ro.index')->with('success', 'Rincian Output (RO) berhasil diperbarui.');
    }

    public function destroy(TabelRo $tabel_ro)
    {
        $tabel_ro->delete();
        return redirect()->route('tabel-ro.index')->with('success', 'Rincian Output (RO) berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls']);
        try {
            Excel::import(new TabelRoImport, $request->file('file'));
            return redirect()->route('tabel-ro.index')->with('success', 'Data Master RO berhasil diimport.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Format file tidak sesuai template! (' . $e->getMessage() . ')');
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'kode_indikator', 'tahun', 'triwulan', 'ro', 
            'realisasi_volume_ro', 'progres_ro', 
            'pagu_awal', 'pagu_revisi', 'pagu_sisa', 'pagu_realisasi'
        ];
        
        $example = [
            '1.1.1.1', '2026', '1', 'Laporan Kinerja Triwulan I', 
            '1', '100', '150000000', '150000000', '0', '150000000'
        ];

        return Excel::download(new class($headers, $example) implements \Maatwebsite\Excel\Concerns\FromArray {
            protected $headers;
            protected $example;
            public function __construct($headers, $example) { 
                $this->headers = $headers;
                $this->example = $example;
            }
            public function array(): array { 
                return [$this->headers, $this->example]; 
            }
        }, 'template_import_ro.xlsx');
    }
}
