<?php

namespace App\Http\Controllers;

use App\Models\Indikator;
use App\Models\Pegawai;
use App\Models\Aktivitas;
use App\Models\Analisis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicInputController extends Controller
{
    public function storeKendala(Request $request)
    {
        $user = auth()->user();
        $pegawai = $user->pegawai;

        $validated = $request->validate([
            'indikator_id' => 'required|exists:indikators,id',
            'triwulan' => 'required|integer|between:1,4',
            'severity' => 'required|in:Low,Medium,High',
            'kegiatan_id' => 'required|exists:kegiatan_masters,id',
            'pegawai_nip' => 'required|string',
            'kendala' => 'required|array',
            'kendala.*' => 'required|string',
            'solusi' => 'nullable|array',
            'rencana_tindak_lanjut' => 'nullable|array',
            'pic_tindak_lanjut' => 'nullable|array',
            'batas_waktu' => 'nullable|array',
        ]);

        $kegiatan = \App\Models\KegiatanMaster::find($validated['kegiatan_id']);
        $isKetuaTim = $kegiatan->ketua_tim_id == $pegawai->id;

        // Create or update parent Analisis (since Kendala is now in tindak_lanjuts)
        $analisis = Analisis::firstOrCreate(
            [
                'indikator_id' => $validated['indikator_id'],
                'triwulan' => $validated['triwulan'],
            ],
            [
                'pegawai_nip' => $validated['pegawai_nip'],
                'severity' => $validated['severity'],
                'kegiatan_id' => $validated['kegiatan_id'],
            ]
        );
        
        $tahun = $request->input('tahun', date('Y'));

        // Loop through kendalas
        foreach ($validated['kendala'] as $index => $kendalaText) {
            $issue = \App\Models\Issue::create([
                'indikator_id' => $validated['indikator_id'],
                'triwulan' => $validated['triwulan'],
                'tahun' => $tahun,
                'status_kendala' => 'Open',
                'deskripsi' => $kendalaText,
                'solusi_sementara' => $validated['solusi'][$index] ?? null,
                'pegawai_nip' => $validated['pegawai_nip'],
            ]);

            if ($isKetuaTim && !empty($validated['rencana_tindak_lanjut'][$index])) {
                \App\Models\Rtl::create([
                    'issue_id' => $issue->id,
                    'deskripsi_rtl' => $validated['rencana_tindak_lanjut'][$index],
                    'pic_nip' => $validated['pic_tindak_lanjut'][$index] ?? $pegawai->nip,
                    'due_date' => $validated['batas_waktu'][$index] ?? null,
                    'status_rtl' => 'Belum Selesai',
                ]);
            }
        }

        return redirect()->back()->with('success', 'Laporan kendala berhasil dikirim.');
    }

    public function getKegiatan($indikator_id)
    {
        $user = auth()->user();
        $pegawai_id = $user->pegawai_id;

        $query = \App\Models\KegiatanMaster::where('indikator_id', $indikator_id);

        if (!$user->isAdminOrPimpinan()) {
            $query->where(function($q) use ($pegawai_id) {
                $q->where('ketua_tim_id', $pegawai_id)
                  ->orWhereHas('anggotas', function($q2) use ($pegawai_id) {
                      $q2->where('pegawai_id', $pegawai_id);
                  });
            });
        }

        $kegiatans = $query->get(['id', 'nama_kegiatan', 'tahapan_json', 'ketua_tim_id']);
        
        // Add anggota list for Ketua Tim selection
        $kegiatans->map(function($k) {
            $k->anggotas_list = $k->anggotas()->get(['nama']);
            return $k;
        });

        return response()->json($kegiatans);
    }

    public function storeAktivitas(Request $request)
    {
        $user = auth()->user();
        $pegawai = $user->pegawai;

        $validated = $request->validate([
            'indikator_id'        => 'required|exists:indikators,id',
            'kegiatan_id'         => 'required|exists:kegiatan_masters,id',
            'triwulan'            => 'required|integer|between:1,4',
            'tahapan'             => 'required|string',
            'tanggal_mulai'       => 'required|date',
            'tanggal_selesai'     => 'required|date|after_or_equal:tanggal_mulai',
            'uraian'              => 'required|string',
            'penjelasan_kegiatan' => 'nullable|string',
            'realisasi_kegiatan'  => 'nullable|string',
            'lampiran.*'          => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,csv|max:10240',
            'pegawai_nip'         => 'required|string',
        ]);

        $paths = [];
        if ($request->hasFile('lampiran')) {
            foreach ($request->file('lampiran') as $file) {
                $paths[] = $file->store('lampiran', 'public');
            }
        }

        \App\Models\Aktivitas::create([
            'indikator_id'        => $validated['indikator_id'],
            'kegiatan_id'         => $validated['kegiatan_id'],
            'pegawai_nip'         => $validated['pegawai_nip'],
            'triwulan'            => $validated['triwulan'],
            'tahapan'             => $validated['tahapan'],
            'tanggal_mulai'       => $validated['tanggal_mulai'],
            'tanggal_selesai'     => $validated['tanggal_selesai'],
            'uraian'              => $validated['uraian'],
            'penjelasan_kegiatan' => $validated['penjelasan_kegiatan'] ?? null,
            'realisasi_kegiatan'  => $validated['realisasi_kegiatan'] ?? null,
            'lampiran'            => $paths,
        ]);

        return redirect()->back()->with('success', 'Aktivitas berhasil dicatat.');
    }
}

