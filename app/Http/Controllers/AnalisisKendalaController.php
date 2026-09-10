<?php

namespace App\Http\Controllers;

use App\Models\Indikator;
use App\Models\KendalaRtl;
use App\Models\Pegawai;
use Illuminate\Http\Request;

class AnalisisKendalaController extends Controller
{
    public function index(Request $request)
    {
        $tahun = $request->get('tahun', session('global_tahun', date('Y')));
        $triwulan = $request->get('triwulan', session('global_triwulan', min(ceil(date('n') / 3), 4)));

        // Get indicators visible to current user
        $indikators = Indikator::visibleTo(auth()->user())
            ->orderBy('kode')
            ->get();

        // Get existing KendalaRtl data for this period
        $kendalaRtls = KendalaRtl::where('tahun', $tahun)
            ->where('triwulan', $triwulan)
            ->whereIn('indikator_id', $indikators->pluck('id'))
            ->get()
            ->groupBy('indikator_id');

        $pegawais = Pegawai::orderBy('nama')->get();

        return view('analisis_kendala.index', compact('indikators', 'kendalaRtls', 'tahun', 'triwulan', 'pegawais'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'indikator_id' => 'required|exists:indikators,id',
            'tahun' => 'required|integer',
            'triwulan' => 'required|integer|between:1,4',
            'kendala' => 'required|string',
            'solusi' => 'required|string',
            'rtl' => 'required|string',
            'pic_nip' => 'nullable|string|exists:pegawais,nip',
            'batas_waktu' => 'nullable|date',
        ]);

        $user = auth()->user();
        if ($user->isPimpinan() || $user->isAnggota()) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki akses.'], 403);
        }
        
        if (!$user->isAdminOrPimpinan()) {
            $indikator = Indikator::find($validated['indikator_id']);
            if (!$indikator || $indikator->pic_id != $user->pegawai_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki akses untuk indikator ini.'
                ], 403);
            }
        }

        $kendalaRtl = KendalaRtl::create([
            'indikator_id' => $validated['indikator_id'],
            'tahun' => $validated['tahun'],
            'triwulan' => $validated['triwulan'],
            'kendala' => $validated['kendala'],
            'solusi' => $validated['solusi'],
            'rtl' => $validated['rtl'],
            'pic_nip' => $validated['pic_nip'],
            'batas_waktu' => $validated['batas_waktu'],
            'status' => 'Belum Ditindak Lanjut',
        ]);

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Analisis dan Kendala berhasil dicatat.',
                'data' => $kendalaRtl,
            ]);
        }

        return redirect()->back()->with('success', 'Analisis dan Kendala berhasil dicatat.');
    }

    public function show($id, Request $request)
    {
        $tahun = $request->get('tahun', session('global_tahun', date('Y')));
        $triwulan = $request->get('triwulan', session('global_triwulan', min(ceil(date('n') / 3), 4)));

        $indikator = Indikator::visibleTo(auth()->user())->findOrFail($id);

        $kendalaRtls = KendalaRtl::where('tahun', $tahun)
            ->where('triwulan', $triwulan)
            ->where('indikator_id', $indikator->id)
            ->get();

        $pegawais = Pegawai::orderBy('nama')->get();

        return view('analisis_kendala.show', compact('indikator', 'kendalaRtls', 'tahun', 'triwulan', 'pegawais'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'kendala' => 'required|string',
            'solusi' => 'required|string',
            'rtl' => 'required|string',
            'pic_nip' => 'nullable|string|exists:pegawais,nip',
            'batas_waktu' => 'nullable|date',
        ]);

        $kendalaRtl = KendalaRtl::findOrFail($id);

        $user = auth()->user();
        if ($user->isPimpinan() || $user->isAnggota()) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki akses.'], 403);
        }
        
        if (!$user->isAdminOrPimpinan()) {
            $indikator = $kendalaRtl->indikator;
            if (!$indikator || $indikator->pic_id != $user->pegawai_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki akses untuk mengubah data ini.'
                ], 403);
            }
        }

        $kendalaRtl->update([
            'kendala' => $validated['kendala'],
            'solusi' => $validated['solusi'],
            'rtl' => $validated['rtl'],
            'pic_nip' => $validated['pic_nip'],
            'batas_waktu' => $validated['batas_waktu'],
        ]);

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Riwayat Kendala berhasil diperbarui.',
                'data' => $kendalaRtl,
            ]);
        }

        return redirect()->back()->with('success', 'Riwayat Kendala berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $kendalaRtl = KendalaRtl::findOrFail($id);
        
        $user = auth()->user();
        if ($user->isPimpinan() || $user->isAnggota()) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki akses.'], 403);
        }
        
        if (!$user->isAdminOrPimpinan()) {
            $indikator = $kendalaRtl->indikator;
            if (!$indikator || $indikator->pic_id != $user->pegawai_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki akses untuk menghapus data ini.'
                ], 403);
            }
        }

        $kendalaRtl->delete();

        if (request()->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Riwayat Kendala berhasil dihapus.',
            ]);
        }

        return redirect()->back()->with('success', 'Riwayat Kendala berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file_import' => 'required|mimes:xlsx,xls,csv',
            'tahun' => 'required|integer',
            'triwulan' => 'required|integer|between:1,4',
        ]);

        $user = auth()->user();
        if (!$user->isAdminOrPimpinan()) {
            return redirect()->back()->with('error', 'Hanya admin yang bisa melakukan import bulk data.');
        }

        try {
            \Maatwebsite\Excel\Facades\Excel::import(
                new \App\Imports\AnalisisKendalaBaruImport($request->tahun, $request->triwulan),
                $request->file('file_import')
            );
            return redirect()->back()->with('success', 'Data kendala berhasil diimport.');
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Format file tidak sesuai template! (' . $e->getMessage() . ')');
        }
    }
}

