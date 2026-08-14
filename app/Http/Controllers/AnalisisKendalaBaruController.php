<?php

namespace App\Http\Controllers;

use App\Models\Indikator;
use App\Models\KendalaRtl;
use App\Models\Pegawai;
use Illuminate\Http\Request;

class AnalisisKendalaBaruController extends Controller
{
    public function index(Request $request)
    {
        $tahun = $request->get('tahun', \App\Models\Setting::get('default_tahun', date('Y')));
        $triwulan = $request->get('triwulan', \App\Models\Setting::get('default_triwulan', min(ceil(date('n') / 3), 4)));

        // Get indicators visible to current user
        $indikators = Indikator::visibleTo(auth()->user())
            ->orderBy('kode')
            ->get();

        // Get existing KendalaRtl data for this period
        $kendalaRtls = KendalaRtl::where('tahun', $tahun)
            ->where('triwulan', $triwulan)
            ->whereIn('indikator_id', $indikators->pluck('id'))
            ->get()
            ->keyBy('indikator_id');

        $pegawais = Pegawai::orderBy('nama')->get();

        return view('analisis_kendala_baru.index', compact('indikators', 'kendalaRtls', 'tahun', 'triwulan', 'pegawais'));
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
        if (!$user->isAdmin()) {
            $indikator = Indikator::find($validated['indikator_id']);
            if (!$indikator || $indikator->pic_id != $user->pegawai_id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Anda tidak memiliki akses untuk indikator ini.'
                ], 403);
            }
        }

        $kendalaRtl = KendalaRtl::updateOrCreate(
            [
                'indikator_id' => $validated['indikator_id'],
                'tahun' => $validated['tahun'],
                'triwulan' => $validated['triwulan'],
            ],
            [
                'kendala' => $validated['kendala'],
                'solusi' => $validated['solusi'],
                'rtl' => $validated['rtl'],
                'pic_nip' => $validated['pic_nip'],
                'batas_waktu' => $validated['batas_waktu'],
            ]
        );

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Analisis dan Kendala berhasil disimpan.',
                'data' => $kendalaRtl,
            ]);
        }

        return redirect()->route('analisis-kendala-baru.index', [
            'tahun' => $validated['tahun'],
            'triwulan' => $validated['triwulan'],
        ])->with('success', 'Analisis dan Kendala berhasil disimpan.');
    }
}
