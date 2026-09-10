<?php

namespace App\Http\Controllers;

use App\Models\KendalaRtl;
use App\Models\KendalaRtlExecution;
use Illuminate\Http\Request;

class PelaksanaanRtlController extends Controller
{
    public function index(Request $request)
    {
        $tahun = $request->get('tahun', session('global_tahun', date('Y')));
        $triwulan = $request->get('triwulan', session('global_triwulan', min(ceil(date('n') / 3), 4)));
        $status = $request->get('status', 'Semua'); // Semua, Sudah Ditindak Lanjut, Belum Ditindak Lanjut

        // Menampilkan RTL dari triwulan sebelumnya yang akan dilaksanakan di triwulan terpilih
        $target_triwulan = $triwulan == 1 ? 4 : $triwulan - 1;
        $target_tahun = $triwulan == 1 ? $tahun - 1 : $tahun;

        $user = auth()->user();
        $query = KendalaRtl::with(['indikator', 'pic', 'executions' => function($q) {
            $q->latest();
        }])
        ->where('tahun', $target_tahun)
        ->where('triwulan', $target_triwulan);

        if (!$user->isAdminOrPimpinan() && !$user->isPimpinan()) {
            $query->whereIn('indikator_id', \App\Models\Indikator::visibleTo($user)->pluck('id'));
        }

        if ($status !== 'Semua') {
            $query->where('status', $status);
        }

        $rtls = $query->get();

        $groupedIKUs = $rtls->groupBy('indikator_id');

        return view('pelaksanaan_rtl.index', compact('groupedIKUs', 'rtls', 'tahun', 'triwulan', 'status', 'target_tahun', 'target_triwulan'));
    }

    public function show($id, Request $request)
    {
        $tahun = $request->get('tahun', session('global_tahun', date('Y')));
        $triwulan = $request->get('triwulan', session('global_triwulan', min(ceil(date('n') / 3), 4)));

        $target_triwulan = $triwulan == 1 ? 4 : $triwulan - 1;
        $target_tahun = $triwulan == 1 ? $tahun - 1 : $tahun;

        $indikator = \App\Models\Indikator::visibleTo(auth()->user())->findOrFail($id);

        $rtls = KendalaRtl::with(['pic', 'executions' => function($q) {
            $q->latest();
        }])
        ->where('indikator_id', $indikator->id)
        ->where('tahun', $target_tahun)
        ->where('triwulan', $target_triwulan)
        ->get();

        return view('pelaksanaan_rtl.show', compact('indikator', 'rtls', 'tahun', 'triwulan', 'target_tahun', 'target_triwulan'));
    }

    public function store(Request $request, KendalaRtl $rtl)
    {
        $user = auth()->user();
        if ($user->isPimpinan() || $user->isAnggota()) {
            return response()->json(['status' => 'error', 'message' => 'Anda tidak memiliki akses.'], 403);
        }
        
        $validated = $request->validate([
            'foto_bukti' => 'required|mimes:doc,docx|max:10240', // Max 10MB
            'is_dokumentasi_ada' => 'nullable|boolean',
            'is_timestamp_ada' => 'nullable|boolean',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto_bukti')) {
            $fotoPath = $request->file('foto_bukti')->store('rtl_executions', 'public');
        }

        $execution = $rtl->executions()->create([
            'narasi_tindak_lanjut' => 'Dilampirkan pada dokumen Word',
            'tanggal_pelaksanaan' => \Carbon\Carbon::now()->format('Y-m-d'),
            'is_dokumentasi_ada' => $request->has('is_dokumentasi_ada'),
            'is_timestamp_ada' => $request->has('is_timestamp_ada'),
            'foto_bukti' => $fotoPath,
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
    public function destroyBukti(KendalaRtlExecution $execution)
    {
        $user = auth()->user();
        if ($user->isPimpinan() || $user->isAnggota()) abort(403, 'Akses ditolak.');
        
        $rtl = $execution->kendalaRtl;

        // Delete file if exists
        if ($execution->foto_bukti && \Illuminate\Support\Facades\Storage::disk('public')->exists($execution->foto_bukti)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($execution->foto_bukti);
        }

        $execution->delete();

        // Update RTL status if no executions left
        if ($rtl->executions()->count() === 0) {
            $rtl->update(['status' => 'Belum Ditindak Lanjut']);
        }

        return back()->with('success', 'Bukti berhasil dihapus.');
    }
}

