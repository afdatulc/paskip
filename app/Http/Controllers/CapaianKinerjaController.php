<?php

namespace App\Http\Controllers;

use App\Models\CapaianKinerja;
use App\Models\Indikator;
use Illuminate\Http\Request;

class CapaianKinerjaController extends Controller
{
    public function index(Request $request)
    {
        $tahun = $request->get('tahun', session('global_tahun', date('Y')));
        $triwulan = $request->get('triwulan', session('global_triwulan', min(ceil(date('n') / 3), 4)));

        // Get indicators visible to current user with target and realisasi for current triwulan
        $indikators = Indikator::visibleTo(auth()->user())
            ->with(['target', 'realisasis' => function ($query) use ($triwulan) {
                $query->where('triwulan', $triwulan);
            }])
            ->orderBy('kode')
            ->get();

        // Get existing capaian data for this period
        $capaians = CapaianKinerja::with('histories')
            ->where('tahun', $tahun)
            ->where('triwulan', $triwulan)
            ->whereIn('indikator_id', $indikators->pluck('id'))
            ->get()
            ->keyBy('indikator_id');

        return view('capaian_kinerja.index', compact('indikators', 'capaians', 'tahun', 'triwulan'));
    }

    public function edit(Request $request, Indikator $indikator, $tahun, $triwulan)
    {
        $user = auth()->user();
        
        // Authorization check: User must be able to view this indikator
        if (!\App\Models\Indikator::visibleTo($user)->where('id', $indikator->id)->exists()) {
            abort(403, 'Akses ditolak.');
        }

        // Determine if user should be read-only (Not Admin and Not PIC)
        $isReadOnly = false;
        if (!$user->isAdminOrPimpinan() && $indikator->pic_id != $user->pegawai_id) {
            $isReadOnly = true;
        }

        $realisasi = $indikator->realisasis()->where('triwulan', $triwulan)->first();
        $capaian = $indikator->capaianKinerjas()->where('triwulan', $triwulan)->where('tahun', $tahun)->first();
        $targetField = 'target_tw' . $triwulan;
        $targetYField = 'target_y_tw' . $triwulan;
        $targetVal = $indikator->target ? $indikator->target->$targetField : null;
        $targetYVal = $indikator->target ? $indikator->target->$targetYField : null;

        return view('capaian_kinerja.edit', compact('indikator', 'tahun', 'triwulan', 'realisasi', 'capaian', 'targetVal', 'targetYVal', 'isReadOnly'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'indikator_id' => 'required|exists:indikators,id',
            'tahun' => 'required|integer',
            'triwulan' => 'required|integer|between:1,4',
            'link_bukti_kinerja' => 'nullable|string',
            'link_bukti_tindak_lanjut' => 'nullable|string',
            'penjelasan_lainnya' => 'nullable|string',
            'dasar_hitung' => 'nullable|string',
            'argumen_logis' => 'nullable|string',
            'target_realisasi' => 'nullable|string',
            'realisasi_kumulatif' => 'required|numeric',
            'realisasi_x' => 'nullable|numeric|min:0',
            'realisasi_y' => 'nullable|numeric|min:0',
        ]);

        // Authorization check
        $user = auth()->user();
        if ($user->isPimpinan() || $user->isAnggota()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda tidak memiliki akses untuk mengubah data ini.'
            ], 403);
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

        $indikatorId = $validated['indikator_id'];
        $tw = $validated['triwulan'];

        // Validation TW > Previous TW
        $previous = \App\Models\Realisasi::where('indikator_id', $indikatorId)
            ->where('triwulan', '<', $tw)
            ->orderBy('triwulan', 'desc')
            ->first();

        if ($previous && $validated['realisasi_kumulatif'] < $previous->realisasi_kumulatif) {
            return response()->json([
                'status' => 'error',
                'message' => "Nilai kumulatif tidak boleh lebih kecil dari Triwulan sebelumnya ({$previous->realisasi_kumulatif})"
            ], 422);
        }

        // Get previous realisasi to log in history (BEFORE updating it)
        $oldRealisasi = \App\Models\Realisasi::where('indikator_id', $indikatorId)
            ->where('triwulan', $tw)
            ->first();

        // Save numeric realization
        \App\Models\Realisasi::updateOrCreate(
            ['indikator_id' => $indikatorId, 'triwulan' => $tw],
            [
                'realisasi_kumulatif' => $validated['realisasi_kumulatif'],
                'realisasi_x'         => $validated['realisasi_x'] ?? null,
                'realisasi_y'         => $validated['realisasi_y'] ?? null,
            ]
        );

        // Check existing capaian
        $capaian = CapaianKinerja::where('indikator_id', $indikatorId)
            ->where('tahun', $validated['tahun'])
            ->where('triwulan', $tw)
            ->first();

        if ($capaian && $capaian->status_approval === 'Ditolak') {
            \App\Models\CapaianKinerjaHistory::create([
                'capaian_kinerja_id' => $capaian->id,
                'realisasi_kumulatif' => $oldRealisasi ? $oldRealisasi->realisasi_kumulatif : null,
                'realisasi_x' => $oldRealisasi ? $oldRealisasi->realisasi_x : null,
                'realisasi_y' => $oldRealisasi ? $oldRealisasi->realisasi_y : null,
                'link_bukti_kinerja' => $capaian->link_bukti_kinerja,
                'link_bukti_tindak_lanjut' => $capaian->link_bukti_tindak_lanjut,
                'penjelasan_lainnya' => $capaian->penjelasan_lainnya,
                'dasar_hitung' => $capaian->dasar_hitung,
                'argumen_logis' => $capaian->argumen_logis,
                'target_realisasi' => $capaian->target_realisasi,
                'catatan_pimpinan' => $capaian->catatan_pimpinan,
            ]);
        }

        if ($capaian) {
            $capaian->update([
                'link_bukti_kinerja' => $validated['link_bukti_kinerja'] ?: null,
                'link_bukti_tindak_lanjut' => $validated['link_bukti_tindak_lanjut'] ?: null,
                'penjelasan_lainnya' => $validated['penjelasan_lainnya'] ?: null,
                'dasar_hitung' => $validated['dasar_hitung'] ?: null,
                'argumen_logis' => $validated['argumen_logis'] ?: null,
                'target_realisasi' => $validated['target_realisasi'] ?: null,
                'status_approval' => 'Menunggu',
                'catatan_pimpinan' => null,
            ]);
        } else {
            $capaian = CapaianKinerja::create([
                'indikator_id' => $indikatorId,
                'tahun' => $validated['tahun'],
                'triwulan' => $tw,
                'link_bukti_kinerja' => $validated['link_bukti_kinerja'] ?: null,
                'link_bukti_tindak_lanjut' => $validated['link_bukti_tindak_lanjut'] ?: null,
                'penjelasan_lainnya' => $validated['penjelasan_lainnya'] ?: null,
                'dasar_hitung' => $validated['dasar_hitung'] ?: null,
                'argumen_logis' => $validated['argumen_logis'] ?: null,
                'target_realisasi' => $validated['target_realisasi'] ?: null,
                'status_approval' => 'Menunggu',
                'catatan_pimpinan' => null,
            ]);
        }

        if ($request->input('action') === 'save_and_next') {
            return redirect()->route('analisis-kendala.index', [
                'tahun' => $validated['tahun'],
                'triwulan' => $validated['triwulan'],
            ])->with('success', 'Capaian kinerja berhasil disimpan. Silakan lanjutkan mengisi kendala.');
        }

        return redirect()->route('capaian-kinerja.index', [
            'tahun' => $validated['tahun'],
            'triwulan' => $validated['triwulan'],
        ])->with('success', 'Capaian kinerja berhasil disimpan.');

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Capaian kinerja berhasil disimpan.',
                'data' => $capaian,
            ]);
        }

        return redirect()->route('capaian-kinerja.index', [
            'tahun' => $validated['tahun'],
            'triwulan' => $validated['triwulan'],
        ])->with('success', 'Capaian kinerja berhasil disimpan.');
    }

    public function getDataPrevious(Request $request, $indikatorId)
    {
        $tahun = $request->get('tahun', session('global_tahun', date('Y')));
        $triwulan = $request->get('triwulan');

        $capaian = CapaianKinerja::where('indikator_id', $indikatorId)
            ->where('tahun', $tahun)
            ->where('triwulan', $triwulan)
            ->first();

        if ($capaian && ($capaian->dasar_hitung || $capaian->argumen_logis || $capaian->penjelasan_lainnya || $capaian->target_realisasi)) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'dasar_hitung' => $capaian->dasar_hitung ?? '',
                    'argumen_logis' => $capaian->argumen_logis ?? '',
                    'penjelasan_lainnya' => $capaian->penjelasan_lainnya ?? '',
                    'target_realisasi' => $capaian->target_realisasi ?? '',
                ]
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Data tidak ditemukan'
        ], 404);
    }
    
    public function approve(Request $request, $id)
    {
        $validated = $request->validate([
            'status_approval' => 'required|in:Disetujui,Ditolak',
            'catatan_pimpinan' => 'nullable|string',
        ]);

        $user = auth()->user();
        if (!$user->isAdminOrPimpinan()) {
            abort(403, 'Hanya Admin/Pimpinan yang dapat menyetujui capaian.');
        }

        $capaian = CapaianKinerja::findOrFail($id);
        
        $capaian->update([
            'status_approval' => $validated['status_approval'],
            'catatan_pimpinan' => $validated['status_approval'] === 'Ditolak' ? $validated['catatan_pimpinan'] : null,
        ]);

        return redirect()->back()->with('success', 'Status approval berhasil diperbarui.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'tahun' => 'required|integer',
            'triwulan' => 'required|integer|between:1,4',
            'file' => 'required|mimes:xlsx,xls'
        ]);

        // Semua user dengan akses (termasuk PIC) dapat mengimport capaian

        try {
            \Maatwebsite\Excel\Facades\Excel::import(
                new \App\Imports\CapaianKinerjaImport($request->tahun, $request->triwulan),
                $request->file('file')
            );
            return back()->with('success', 'Data Capaian Kinerja berhasil diimport.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Format file tidak sesuai template! (' . $e->getMessage() . ')');
        }
    }

    public function template(Request $request)
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $headers = [
            'Kode Indikator',
            'Realisasi TW',
            'Realisasi X',
            'Realisasi Y',
            'Link Bukti Dukung Kinerja',
            'Link Bukti Dukung Rencana Tindak Lanjut Triwulan Sebelumnya',
            'Kendala Yg Dihadapi',
            'Solusi Yg Telah Dilakukan',
            'Rencana Tindak Lanjut',
            'PIC Tindak Lanjut',
            'Batas Waktu Tindak Lanjut'
        ];

        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getColumnDimension($col)->setAutoSize(true);
            $col++;
        }

        $sheet->getStyle('A1:K1')->getFont()->setBold(true);

        $selectedIds = $request->input('indikator_ids', []);
        $query = Indikator::orderBy('kode');
        if (!empty($selectedIds)) {
            $query->whereIn('id', $selectedIds);
        }
        $indikators = $query->get();
        
        $row = 2;
        foreach ($indikators as $ind) {
            $sheet->setCellValue('A' . $row, $ind->kode);
            $row++;
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'Template_Import_Capaian_Kinerja.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($fileName).'"');
        $writer->save('php://output');
        exit;
    }
}

