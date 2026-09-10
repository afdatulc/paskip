<?php

namespace App\Http\Controllers;

use App\Models\Indikator;
use App\Models\Pegawai;
use Illuminate\Http\Request;

class EvaluasiKinerjaController extends Controller
{
    public function index(Request $request)
    {
        $defaultTahun = date('Y');
        $defaultTriwulan = ceil(date('n') / 3);

        $tahun = $request->get('tahun', session('global_tahun', $defaultTahun));
        $triwulan = $request->get('triwulan', session('global_triwulan', $defaultTriwulan));

        $user = auth()->user();
        
        $indikators = Indikator::visibleTo($user)
            ->where('tahun', $tahun)
            ->with(['realisasis' => function($q) use ($triwulan) {
                $q->where('triwulan', $triwulan);
            }])
            ->get();
            
        $pegawais = Pegawai::orderBy('pangkat_golongan', 'desc')->orderBy('nip', 'asc')->get();

        return view('evaluasi_kinerja.index', compact('indikators', 'tahun', 'triwulan', 'pegawais'));
    }
}
