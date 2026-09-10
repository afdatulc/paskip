<?php

namespace App\Http\Controllers;

use App\Models\Indikator;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $tahun = $request->get('tahun', session('global_tahun', date('Y')));
        $triwulan = $request->get('triwulan', session('global_triwulan', ceil(date('n') / 3)));
        $user = auth()->user();
        
        $indikators = Indikator::where('tahun', $tahun)->with(['target', 'realisasis'])->get();

        $summary = [
            'total' => $indikators->count(),
            'hijau' => $indikators->filter(fn($i) => $i->status_warna == 'success')->count(),
            'kuning' => $indikators->filter(fn($i) => $i->status_warna == 'warning')->count(),
            'merah' => $indikators->filter(fn($i) => $i->status_warna == 'danger')->count(),
        ];
        
        return view('dashboard', [
            'indikators' => $indikators,
            'summary' => $summary,
            'tahun' => $tahun,
            'triwulan' => $triwulan
        ]);
    }
}
