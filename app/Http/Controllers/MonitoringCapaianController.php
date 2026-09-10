<?php

namespace App\Http\Controllers;

use App\Models\Indikator;
use App\Models\CapaianKinerja;
use Illuminate\Http\Request;
use App\Services\CapaianCalculator;

class MonitoringCapaianController extends Controller
{
    public function index(Request $request)
    {
        $tahun = $request->get('tahun', session('global_tahun', date('Y')));
        $triwulan = $request->get('triwulan', session('global_triwulan', min(ceil(date('n') / 3), 4)));

        // Get Indicators with Realisasi (All users can see all IKU in Monitoring)
        $indikators = Indikator::with(['realisasis' => function ($query) use ($triwulan) {
                $query->where('triwulan', $triwulan);
            }, 'pkTahunans' => function ($query) use ($tahun) {
                $query->where('tahun', $tahun);
            }, 'target'])
            ->orderBy('kode')
            ->get();

        $accessibleIndikatorIds = Indikator::visibleTo(auth()->user())->pluck('id')->toArray();

        return view('monitoring_capaian.index', compact('indikators', 'tahun', 'triwulan', 'accessibleIndikatorIds'));
    }
}
