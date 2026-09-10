@extends('layouts.dashboard')

@section('title', 'Monitoring Kelengkapan Data')

@section('content')
    <!-- Filter Card -->
    <div class="card mb-4 shadow-sm border-0 rounded-4">
        <div class="card-body">
            <form action="{{ route('monitoring-kelengkapan.index') }}" method="GET" class="d-flex gap-3 align-items-end flex-wrap">
                <div>
                    <label class="form-label text-muted small fw-bold mb-1">Tahun</label>
                <select name="tahun" class="form-select" style="width: 120px;" onchange="this.form.submit()">
                    @php $currentYear = date('Y'); @endphp
                    @for($i = $currentYear - 2; $i <= $currentYear + 2; $i++)
                        <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="form-label text-muted small fw-bold mb-1">Triwulan</label>
                <select name="triwulan" class="form-select" onchange="this.form.submit()">
                    <option value="1" {{ $triwulan == 1 ? 'selected' : '' }}>Triwulan 1</option>
                    <option value="2" {{ $triwulan == 2 ? 'selected' : '' }}>Triwulan 2</option>
                    <option value="3" {{ $triwulan == 3 ? 'selected' : '' }}>Triwulan 3</option>
                    <option value="4" {{ $triwulan == 4 ? 'selected' : '' }}>Triwulan 4</option>
                </select>
            </div>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm rounded-4 text-dark mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0" id="monitoringTable" style="font-size: 0.85rem;">
                    <thead class="table-light text-center align-middle">
                        <tr>
                            <th rowspan="2" width="40">No</th>
                            <th rowspan="2" style="min-width: 250px;">Indikator</th>
                            <th rowspan="2">Realisasi TW</th>
                            <th colspan="2">Rumus Target</th>
                            <th colspan="3">Narasi & Argumen</th>
                            <th colspan="4">Analisis & Tindak Lanjut</th>
                            <th colspan="2">Bukti Dukung</th>
                        </tr>
                        <tr>
                            <th class="fw-normal">Nilai X</th>
                            <th class="fw-normal">Nilai Y</th>
                            <th class="fw-normal">Dasar Hitung</th>
                            <th class="fw-normal">Argumen Logis</th>
                            <th class="fw-normal">Penjelasan</th>
                            
                            <th class="fw-normal">Kendala</th>
                            <th class="fw-normal">Solusi</th>
                            <th class="fw-normal">RTL</th>
                            <th class="fw-normal">PIC & Batas</th>
                            
                            <th class="fw-normal">Kinerja</th>
                            <th class="fw-normal">RTL (TW Sblm)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($indikators as $ind)
                            @php 
                                $capaian = $capaians->get($ind->id);
                                $realisasi = $ind->realisasis->first();
                                $firstKendalaRtl = $ind->kendalaRtls->first();
                                
                                $isComplete = function($val, $url) {
                                    $icon = ($val !== null && $val !== '' && $val !== false) 
                                        ? '<i class="fas fa-check-circle text-success fs-5"></i>' 
                                        : '<i class="fas fa-times-circle text-danger opacity-50"></i>';
                                    return '<a href="'.$url.'" class="text-decoration-none" title="Ke halaman pengisian" data-bs-toggle="tooltip">'.$icon.'</a>';
                                };
                                
                                $hasRumus = $ind->definisi_x || $ind->definisi_y;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-2 mb-1">{{ $ind->kode }}</span>
                                    <div class="fw-bold text-dark">{{ $ind->indikator_kinerja }}</div>
                                </td>
                                
                                <td class="text-center">{!! $isComplete($realisasi->realisasi_kumulatif ?? null, route('capaian-kinerja.index')) !!}</td>
                                
                                @if($hasRumus)
                                    <td class="text-center">{!! $isComplete($realisasi->realisasi_x ?? null, route('capaian-kinerja.index')) !!}</td>
                                    <td class="text-center">{!! $isComplete($realisasi->realisasi_y ?? null, route('capaian-kinerja.index')) !!}</td>
                                @else
                                    <td class="text-center text-muted bg-light">-</td>
                                    <td class="text-center text-muted bg-light">-</td>
                                @endif
                                
                                <td class="text-center">{!! $isComplete($capaian->dasar_hitung ?? null, route('capaian-kinerja.index')) !!}</td>
                                <td class="text-center">{!! $isComplete($capaian->argumen_logis ?? null, route('capaian-kinerja.index')) !!}</td>
                                <td class="text-center">{!! $isComplete($capaian->penjelasan_lainnya ?? null, route('capaian-kinerja.index')) !!}</td>
                                
                                <td class="text-center">{!! $isComplete($firstKendalaRtl->kendala ?? null, route('analisis-kendala.index')) !!}</td>
                                <td class="text-center">{!! $isComplete($firstKendalaRtl->solusi ?? null, route('analisis-kendala.index')) !!}</td>
                                <td class="text-center">{!! $isComplete($firstKendalaRtl->rtl ?? null, route('analisis-kendala.index')) !!}</td>
                                <td class="text-center">
                                    @php
                                        $picBatas = ($firstKendalaRtl->pic_nip ?? null) && ($firstKendalaRtl->batas_waktu ?? null);
                                    @endphp
                                    {!! $isComplete($picBatas, route('analisis-kendala.index')) !!}
                                </td>
                                
                                <td class="text-center">{!! $isComplete($capaian->link_bukti_kinerja ?? null, route('capaian-kinerja.index')) !!}</td>
                                <td class="text-center">{!! $isComplete($capaian->link_bukti_tindak_lanjut ?? null, route('capaian-kinerja.index')) !!}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                    Tidak ada indikator kinerja.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
