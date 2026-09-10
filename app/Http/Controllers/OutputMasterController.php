<?php

namespace App\Http\Controllers;

use App\Models\OutputMaster;
use App\Models\Indikator;
use App\Imports\OutputMasterImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class OutputMasterController extends Controller
{
    public function index(Request $request)
    {
        $query = OutputMaster::with('indikator');
        
        $pegawaiId = auth()->user()->pegawai_id;
        $isAdmin = auth()->user()->isAdminOrPimpinan();

        if (!$isAdmin) {
            if ($pegawaiId) {
                $query->whereHas('indikator', function($q) use ($pegawaiId) {
                    if (auth()->user()->isPic()) {
                        $q->where('pic_id', $pegawaiId);
                    } elseif (auth()->user()->isAnggota()) {
                        $q->whereHas('anggotas', function($sq) use ($pegawaiId) {
                            $sq->where('pegawai_id', $pegawaiId);
                        });
                    }
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $outputs = $query->get();
        
        if (!$isAdmin && $pegawaiId) {
            $indikators = Indikator::where('pic_id', $pegawaiId)
                ->orWhereHas('anggotas', function($q) use ($pegawaiId) {
                    $q->where('pegawai_id', $pegawaiId);
                })->orderBy('kode', 'asc')->get();
        } else {
            $indikators = Indikator::orderBy('kode', 'asc')->get();
        }

        return view('output_master.index', compact('outputs', 'indikators'));
    }

    public function import(Request $request)
    {
        if (auth()->user()->isAnggota()) abort(403, 'Akses ditolak.');
        
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        try {
            Excel::import(new OutputMasterImport, $request->file('file'));
            return redirect()->route('output-master.index')->with('success', 'Data Output berhasil diimpor!');
        } catch (\Throwable $e) {
            return redirect()->route('output-master.index')->with('error', 'Format file tidak sesuai template! (' . $e->getMessage() . ')');
        }
    }

    public function store(Request $request)
    {
        if (auth()->user()->isAnggota()) abort(403, 'Akses ditolak.');
        
        $pegawaiId = auth()->user()->pegawai_id;
        $isAdmin = auth()->user()->isAdminOrPimpinan();

        $request->validate([
            'indikator_id' => [
                'required',
                'exists:indikators,id',
                function ($attribute, $value, $fail) use ($isAdmin, $pegawaiId) {
                    if (!$isAdmin) {
                        $indikator = Indikator::find($value);
                        if (!$indikator || $indikator->pic_id != $pegawaiId) {
                            $fail('Anda hanya diperbolehkan menambah output pada indikator di mana Anda adalah PIC.');
                        }
                    }
                },
            ],
            'nama_output' => 'required|string|max:255',
            'jenis_output' => 'required|in:Laporan,Publikasi',
            'periode' => 'required|in:Tahunan,Triwulanan,Bulanan',
        ]);

        try {
            OutputMaster::create([
                'indikator_id' => $request->indikator_id,
                'nama_output' => $request->nama_output,
                'jenis_output' => $request->jenis_output,
                'periode' => $request->periode,
            ]);
            return redirect()->route('output-master.index')->with('success', 'Data Output berhasil ditambahkan!');
        } catch (\Throwable $e) {
            return redirect()->route('output-master.index')->with('error', 'Gagal menambahkan data: ' . $e->getMessage());
        }
    }

    public function update(Request $request, OutputMaster $outputMaster)
    {
        if (auth()->user()->isAnggota()) abort(403, 'Akses ditolak.');
        
        $pegawaiId = auth()->user()->pegawai_id;
        $isAdmin = auth()->user()->isAdminOrPimpinan();

        $request->validate([
            'indikator_id' => [
                'required',
                'exists:indikators,id',
                function ($attribute, $value, $fail) use ($isAdmin, $pegawaiId) {
                    if (!$isAdmin) {
                        $indikator = Indikator::find($value);
                        if (!$indikator || $indikator->pic_id != $pegawaiId) {
                            $fail('Anda hanya diperbolehkan mengubah output pada indikator di mana Anda adalah PIC.');
                        }
                    }
                },
            ],
            'nama_output' => 'required|string|max:255',
            'jenis_output' => 'required|in:Laporan,Publikasi',
            'periode' => 'required|in:Tahunan,Triwulanan,Bulanan',
        ]);

        try {
            $outputMaster->update([
                'indikator_id' => $request->indikator_id,
                'nama_output' => $request->nama_output,
                'jenis_output' => $request->jenis_output,
                'periode' => $request->periode,
            ]);
            return redirect()->route('output-master.index')->with('success', 'Data Output berhasil diperbarui!');
        } catch (\Throwable $e) {
            return redirect()->route('output-master.index')->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    public function destroy(OutputMaster $outputMaster)
    {
        if (auth()->user()->isAnggota()) abort(403, 'Akses ditolak.');
        
        try {
            $outputMaster->delete();
            return redirect()->route('output-master.index')->with('success', 'Data Output berhasil dihapus!');
        } catch (\Throwable $e) {
            return redirect()->route('output-master.index')->with('error', 'Gagal menghapus data.');
        }
    }
}

