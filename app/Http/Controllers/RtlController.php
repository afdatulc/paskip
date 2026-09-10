<?php

namespace App\Http\Controllers;

use App\Models\KendalaRtl;
use App\Models\KendalaRtlExecution;
use Illuminate\Http\Request;

class RtlController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $tab = $request->get('tab', 'semua');
        $tahun = $request->get('tahun', session('global_tahun', date('Y')));
        $triwulan = $request->get('triwulan', session('global_triwulan', min(ceil(date('n') / 3), 4)));
        
        // Menampilkan RTL dari triwulan sebelumnya yang akan dilaksanakan di triwulan terpilih
        $target_triwulan = $triwulan == 1 ? 4 : $triwulan - 1;
        $target_tahun = $triwulan == 1 ? $tahun - 1 : $tahun;

        $query = KendalaRtl::with(['indikator', 'pic', 'executions'])
            ->where('tahun', $target_tahun)
            ->where('triwulan', $target_triwulan);

        if (!$user->isAdminOrPimpinan()) {
            $pegawaiNip = $user->pegawai?->nip ?? $user->pegawai?->email_bps ?? $user->email;
            $query->where('pic_nip', $pegawaiNip);
        }

        // Apply tab filters
        if ($tab == 'belum_tindak_lanjut') {
            $query->where('status', '!=', 'Sudah Ditindak Lanjut');
        } elseif ($tab == 'sudah_tindak_lanjut') {
            $query->where('status', 'Sudah Ditindak Lanjut');
        }

        $rtls = $query->orderBy('batas_waktu', 'asc')->get();

        // Counts for tabs
        $baseQuery = KendalaRtl::query()
            ->where('tahun', $target_tahun)
            ->where('triwulan', $target_triwulan);
            
        if (!$user->isAdminOrPimpinan()) {
            $baseQuery->where('pic_nip', $user->pegawai?->nip ?? $user->pegawai?->email_bps ?? $user->email);
        }
        $counts = [
            'semua' => (clone $baseQuery)->count(),
            'belum_tindak_lanjut' => (clone $baseQuery)->where('status', '!=', 'Sudah Ditindak Lanjut')->count(),
            'sudah_tindak_lanjut' => (clone $baseQuery)->where('status', 'Sudah Ditindak Lanjut')->count(),
        ];

        return view('monitoring_rtl.index', compact('rtls', 'tab', 'counts', 'tahun', 'triwulan', 'target_tahun', 'target_triwulan'));
    }

    public function storeExecution(Request $request, $id)
    {
        $validated = $request->validate([
            'catatan_progres' => 'required|string',
            'file_bukti_dukung' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ]);

        $rtl = KendalaRtl::findOrFail($id);

        $execution = new KendalaRtlExecution();
        $execution->kendala_rtl_id = $rtl->id;
        $execution->narasi_tindak_lanjut = $validated['catatan_progres'];
        $execution->tanggal_pelaksanaan = now();
        $execution->pegawai_nip = auth()->user()->pegawai?->nip ?? auth()->user()->email;
        $execution->is_timestamp_ada = true;
        
        if ($request->hasFile('file_bukti_dukung')) {
            $file = $request->file('file_bukti_dukung');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('bukti_rtl', $filename, 'public');
            $execution->foto_bukti = $path;
            $execution->is_dokumentasi_ada = true;
        } else {
            $execution->is_dokumentasi_ada = false;
        }

        $execution->save();

        // Automatically set status to "Sudah Ditindak Lanjut" if it was not
        if ($rtl->status !== 'Sudah Ditindak Lanjut') {
            $rtl->status = 'Sudah Ditindak Lanjut';
            $rtl->save();
        }

        return back()->with('success', 'Eksekusi RTL berhasil disimpan.');
    }
}

