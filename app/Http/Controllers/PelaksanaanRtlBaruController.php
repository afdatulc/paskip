<?php

namespace App\Http\Controllers;

use App\Models\KendalaRtl;
use App\Models\KendalaRtlExecution;
use Illuminate\Http\Request;

class PelaksanaanRtlBaruController extends Controller
{
    public function index(Request $request)
    {
        $tahun = $request->get('tahun', \App\Models\Setting::get('default_tahun', date('Y')));
        $triwulan = $request->get('triwulan', \App\Models\Setting::get('default_triwulan', min(ceil(date('n') / 3), 4)));
        $status = $request->get('status', 'Semua'); // Semua, Sudah Ditindak Lanjut, Belum Ditindak Lanjut

        $user = auth()->user();
        $query = KendalaRtl::with(['indikator', 'pic', 'executions' => function($q) {
            $q->latest();
        }])
        ->where('tahun', $tahun)
        ->where('triwulan', $triwulan);

        if (!$user->isAdmin()) {
            // Include RTLs where user is PIC or part of the kegiatan team for that indikator
            $query->whereHas('indikator', function ($q) use ($user) {
                $q->where('pic_id', $user->pegawai_id)
                  ->orWhereHas('kegiatanMasters', function ($q2) use ($user) {
                      $q2->whereHas('anggotas', function ($q3) use ($user) {
                          $q3->where('pegawai_id', $user->pegawai_id);
                      });
                  });
            });
        }

        if ($status !== 'Semua') {
            $query->where('status', $status);
        }

        $rtls = $query->get();

        return view('pelaksanaan_rtl_baru.index', compact('rtls', 'tahun', 'triwulan', 'status'));
    }

    public function store(Request $request, KendalaRtl $rtl)
    {
        $validated = $request->validate([
            'narasi_tindak_lanjut' => 'required|string',
            'tanggal_pelaksanaan' => 'required|date',
            'is_dokumentasi_ada' => 'required|boolean',
            'is_timestamp_ada' => 'required|boolean',
            'foto_bukti' => 'nullable|image|max:5120', // Max 5MB
        ]);

        $tanggal = \Carbon\Carbon::parse($validated['tanggal_pelaksanaan']);
        $expectedQuarter = ceil($tanggal->month / 3);
        $expectedYear = $tanggal->year;

        if ($expectedQuarter != $rtl->triwulan || $expectedYear != $rtl->tahun) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tanggal pelaksanaan harus berada dalam rentang Triwulan ' . $rtl->triwulan . ' Tahun ' . $rtl->tahun,
            ], 422);
        }

        if (!$validated['is_dokumentasi_ada']) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dokumentasi narasi wajib dikonfirmasi ada.',
            ], 422);
        }
        
        if (!$validated['is_timestamp_ada']) {
            return response()->json([
                'status' => 'error',
                'message' => 'Timestamp waktu wajib dikonfirmasi ada pada dokumentasi.',
            ], 422);
        }

        $fotoPath = null;
        if ($request->hasFile('foto_bukti')) {
            $fotoPath = $request->file('foto_bukti')->store('rtl_executions', 'public');
        }

        $execution = $rtl->executions()->create([
            'narasi_tindak_lanjut' => $validated['narasi_tindak_lanjut'],
            'tanggal_pelaksanaan' => $validated['tanggal_pelaksanaan'],
            'is_dokumentasi_ada' => $validated['is_dokumentasi_ada'],
            'is_timestamp_ada' => $validated['is_timestamp_ada'],
            'foto_bukti' => $fotoPath,
            'pegawai_nip' => auth()->user()->pegawai->nip ?? auth()->user()->username,
        ]);

        $rtl->update(['status' => 'Sudah Ditindak Lanjut']);

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Bukti Tindak Lanjut berhasil disimpan.',
            ]);
        }

        return back()->with('success', 'Bukti Tindak Lanjut berhasil disimpan.');
    }
}
