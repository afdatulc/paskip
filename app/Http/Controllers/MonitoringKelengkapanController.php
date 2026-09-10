<?php

namespace App\Http\Controllers;

use App\Models\Indikator;
use App\Models\CapaianKinerja;
use Illuminate\Http\Request;

class MonitoringKelengkapanController extends Controller
{
    public function index(Request $request)
    {
        $tahun = $request->get('tahun', session('global_tahun', date('Y')));
        $triwulan = $request->get('triwulan', session('global_triwulan', min(ceil(date('n') / 3), 4)));

        $indikators = Indikator::visibleTo(auth()->user())
            ->with(['realisasis' => function ($query) use ($triwulan) {
                $query->where('triwulan', $triwulan);
            }, 'kendalaRtls' => function ($query) use ($triwulan) {
                $query->where('triwulan', $triwulan);
            }])
            ->orderBy('kode')
            ->get();

        $capaians = CapaianKinerja::where('tahun', $tahun)
            ->where('triwulan', $triwulan)
            ->whereIn('indikator_id', $indikators->pluck('id'))
            ->get()
            ->keyBy('indikator_id');

        return view('monitoring_kelengkapan.index', compact('indikators', 'capaians', 'tahun', 'triwulan'));
    }
}
