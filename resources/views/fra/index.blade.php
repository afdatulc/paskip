@extends('layouts.dashboard')

@section('title', 'Form Rencana Aksi (FRA)')

@section('styles')
<style>
    .table-rekap-excel {
        font-size: 0.8rem;
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }
    .table-rekap-excel th, .table-rekap-excel td {
        border: 1px solid #c0c0c0;
        padding: 6px 8px;
        vertical-align: middle;
        white-space: nowrap;
    }
    .table-rekap-excel thead tr:nth-child(1) th {
        background-color: #d1e7dd;
        text-align: center;
        position: sticky;
        top: 0;
        z-index: 10;
        font-weight: 700;
        height: 50px;
    }
    .table-rekap-excel thead tr:nth-child(2) th {
        background-color: #d1e7dd;
        text-align: center;
        position: sticky;
        top: 50px;
        z-index: 10;
        font-weight: 700;
    }
    
    .sticky-col-main {
        position: sticky !important;
        left: 0 !important;
        background-color: #ffffff !important;
        z-index: 20 !important;
        min-width: 450px;
        max-width: 550px;
        white-space: normal !important;
        border-right: 2px solid #dee2e6 !important;
    }
    
    .table-rekap-excel thead th.sticky-col-main {
        background-color: #d1e7dd !important;
        z-index: 40 !important;
        top: 0 !important;
        left: 0 !important;
    }

    .row-tujuan { background-color: #e9ecef !important; font-weight: 700; color: #212529; }
    .row-sasaran { background-color: #fdfdfe !important; font-weight: 600; }
    .row-indikator { background-color: #ffffff !important; }
    
    .bg-target { background-color: #f8f9fa; }
    .bg-realisasi { background-color: #fff; }
    
    .scroll-container {
        height: 600px;
        overflow: auto;
        border: 1px solid #dee2e6;
        border-radius: 8px;
    }
    .sub-head { font-size: 0.7rem; background-color: #f1f8f5 !important; }
</style>
@endsection

@section('content')
<!-- Filter Card -->
<div class="card mb-4 shadow-sm border-0 rounded-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-end flex-wrap gap-3">
            <form action="{{ route('fra.index') }}" method="GET" class="d-flex gap-3 align-items-end flex-wrap">
                <div>
                    <label class="form-label text-muted small fw-bold mb-1">Tahun</label>
                    <select name="tahun" class="form-select" style="width: 120px;" onchange="this.form.submit()">
                        @for($i = date('Y') - 1; $i <= date('Y') + 2; $i++)
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
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-dark rounded-3 px-3" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    <i class="fas fa-file-import me-1"></i> Import Excel
                </button>
                <a href="{{ route('fra.export', ['tahun' => $tahun, 'triwulan' => $triwulan]) }}" id="btnExportFra" class="btn btn-success rounded-3 px-3">
                    <i class="fas fa-file-export me-1"></i> Export Excel
                </a>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- SPREADSHEET VIEW -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-0">
        <div class="scroll-container m-0 border-0" style="border-radius: 1rem;">
            <table class="table-rekap-excel">
                <thead>
                    <tr>
                        <th rowspan="2" class="sticky-col-main">Tujuan / Sasaran / Indikator Kinerja</th>
                        <th rowspan="2">Jenis (IKU atau Proksi)</th>
                        <th rowspan="2">Jenis (Triwulanan atau Tahunan)</th>
                        <th rowspan="2">Jenis (% atau Non %)</th>
                        <th rowspan="2">Target</th>
                        <th rowspan="2">Satuan</th>
                        <th colspan="4" class="bg-target">Alokasi Target (Kumulatif)</th>
                        <th colspan="4" class="bg-realisasi">Realisasi (Kumulatif)</th>
                        <th colspan="4" class="bg-target">Capaian Terhadap Target Triwulanan</th>
                        <th colspan="4" class="bg-realisasi">Capaian Terhadap Target Tahunan</th>
                        <th rowspan="2" style="min-width: 250px; width: 250px; max-width: 250px; white-space: normal;">Kendala yang dihadapi pada Triwulan Berjalan</th>
                        <th rowspan="2" style="min-width: 250px; width: 250px; max-width: 250px; white-space: normal;">Solusi yang sudah dilakukan pada Triwulan Berjalan</th>
                        <th rowspan="2" style="min-width: 250px; width: 250px; max-width: 250px; white-space: normal;">Rencana Tindak lanjut</th>
                        <th rowspan="2">PIC Tindak Lanjut</th>
                        <th rowspan="2">Batas Waktu Tindak Lanjut</th>
                        <th rowspan="2">Link Bukti Dukung Kinerja</th>
                        <th rowspan="2">Link Bukti Dukung Tindak Lanjut Triwulan Sebelumnya</th>
                    </tr>
                    <tr>
                        <th class="sub-head bg-target">TW 1</th>
                        <th class="sub-head bg-target">TW 2</th>
                        <th class="sub-head bg-target">TW 3</th>
                        <th class="sub-head bg-target">TW 4</th>
                        <th class="sub-head bg-realisasi">TW 1</th>
                        <th class="sub-head bg-realisasi">TW 2</th>
                        <th class="sub-head bg-realisasi">TW 3</th>
                        <th class="sub-head bg-realisasi">TW 4</th>
                        <th class="sub-head bg-target">TW 1</th>
                        <th class="sub-head bg-target">TW 2</th>
                        <th class="sub-head bg-target">TW 3</th>
                        <th class="sub-head bg-target">TW 4</th>
                        <th class="sub-head bg-realisasi">TW 1</th>
                        <th class="sub-head bg-realisasi">TW 2</th>
                        <th class="sub-head bg-realisasi">TW 3</th>
                        <th class="sub-head bg-realisasi">TW 4</th>
                    </tr>
                </thead>
                <tbody>
                    @php $no = 1; @endphp
                    @forelse($grouped as $tujuan => $sasarans)
                        @php
                            $firstInd = $sasarans->first()->first();
                            $kodeTujuanStr = $firstInd && $firstInd->kode_tujuan ? $firstInd->kode_tujuan . ': ' : '';
                        @endphp
                        <tr class="row-tujuan">
                            <td class="sticky-col-main">{{ $kodeTujuanStr }}{{ $tujuan }}</td>
                            <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                        </tr>
                        @foreach($sasarans as $sasaran => $inds)
                            <tr class="row-sasaran">
                                <td class="sticky-col-main">
                                    @if($inds->first()->kode_sasaran)
                                        <span class="badge bg-secondary me-1">{{ $inds->first()->kode_sasaran }}</span>
                                    @endif
                                    {{ $sasaran }}
                                </td>
                                <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                            </tr>
                            @foreach($inds as $ind)
                                @php
                                    $canEditAll = auth()->user()->isAdmin();
                                    $pegawaiId = auth()->user()->pegawai_id;
                                    $canEditThis = $canEditAll || (auth()->user()->isPic() && $ind->pic_id == $pegawaiId);
                                    $editClass = $canEditThis ? 'live-edit' : '';
                                    $editAttr = $canEditThis ? 'contenteditable="true"' : '';
                                    
                                    $target = $ind->target;
                                    $r1 = $ind->realisasis->where('triwulan', 1)->first()->realisasi_kumulatif ?? '-';
                                    $r2 = $ind->realisasis->where('triwulan', 2)->first()->realisasi_kumulatif ?? '-';
                                    $r3 = $ind->realisasis->where('triwulan', 3)->first()->realisasi_kumulatif ?? '-';
                                    $r4 = $ind->realisasis->where('triwulan', 4)->first()->realisasi_kumulatif ?? '-';
                                    $kendalas = $ind->kendalaRtls->where('triwulan', $triwulan);
                                    $kendala = $kendalas->first();
                                    
                                    $formatBullet = function($str) {
                                        if ($str === null || trim($str) === '') return null;
                                        $str = trim($str);
                                        return str_starts_with($str, '-') ? $str : '- ' . $str;
                                    };
                                    
                                    $compiledKendalaStr = $kendalas->pluck('kendala')->filter()->map($formatBullet)->join("\n");
                                    $compiledSolusiStr = $kendalas->pluck('solusi')->filter()->map($formatBullet)->join("\n");
                                    $compiledRtlStr = $kendalas->pluck('rtl')->filter()->map($formatBullet)->join("\n");
                                    
                                    $capaian = $ind->capaianKinerjas->where('triwulan', $triwulan)->first();
                                    
                                    $c1 = $ind->capaianKinerjas->where('triwulan', 1)->first();
                                    $c2 = $ind->capaianKinerjas->where('triwulan', 2)->first();
                                    $c3 = $ind->capaianKinerjas->where('triwulan', 3)->first();
                                    $c4 = $ind->capaianKinerjas->where('triwulan', 4)->first();
                                    
                                    $picName = $ind->pic ? $ind->pic->nama : ($kendala && $kendala->pic ? $kendala->pic->nama : '-');
                                @endphp
                                <tr class="row-indikator">
                                    <td class="sticky-col-main ps-3">
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-light text-dark border me-2">{{ $ind->kode }}</span>
                                            <span style="white-space: normal;">{{ $ind->indikator_kinerja }}</span>
                                        </div>
                                    </td>
                                    
                                    <td class="text-center">{{ $ind->tipe }}</td>
                                    <td class="text-center">{{ $ind->periode }}</td>
                                    <td class="text-center">{{ $ind->jenis_indikator }}</td>
                                    <td class="text-center">{{ $ind->target_tahunan }}</td>
                                    <td class="text-center">{{ $ind->satuan }}</td>
                                    
                                    <td class="text-center bg-target">{{ $target ? $target->target_tw1 : '-' }}</td>
                                    <td class="text-center bg-target">{{ $target ? $target->target_tw2 : '-' }}</td>
                                    <td class="text-center bg-target">{{ $target ? $target->target_tw3 : '-' }}</td>
                                    <td class="text-center bg-target">{{ $target ? $target->target_tw4 : '-' }}</td>
                                    
                                    <td class="text-center bg-realisasi {{ $editClass }}" {!! $editAttr !!} data-id="{{ $ind->id }}" data-type="realisasi" data-field="realisasi_kumulatif" data-tw="1">{{ $r1 }}</td>
                                    <td class="text-center bg-realisasi {{ $editClass }}" {!! $editAttr !!} data-id="{{ $ind->id }}" data-type="realisasi" data-field="realisasi_kumulatif" data-tw="2">{{ $r2 }}</td>
                                    <td class="text-center bg-realisasi {{ $editClass }}" {!! $editAttr !!} data-id="{{ $ind->id }}" data-type="realisasi" data-field="realisasi_kumulatif" data-tw="3">{{ $r3 }}</td>
                                    <td class="text-center bg-realisasi {{ $editClass }}" {!! $editAttr !!} data-id="{{ $ind->id }}" data-type="realisasi" data-field="realisasi_kumulatif" data-tw="4">{{ $r4 }}</td>
                                    
                                    <td class="text-center bg-target capaian-tw-cell" data-id="{{ $ind->id }}" data-tw="1">{{ $c1 && !is_null($c1->capaian_kinerja) ? $c1->capaian_kinerja : '-' }}</td>
                                    <td class="text-center bg-target capaian-tw-cell" data-id="{{ $ind->id }}" data-tw="2">{{ $c2 && !is_null($c2->capaian_kinerja) ? $c2->capaian_kinerja : '-' }}</td>
                                    <td class="text-center bg-target capaian-tw-cell" data-id="{{ $ind->id }}" data-tw="3">{{ $c3 && !is_null($c3->capaian_kinerja) ? $c3->capaian_kinerja : '-' }}</td>
                                    <td class="text-center bg-target capaian-tw-cell" data-id="{{ $ind->id }}" data-tw="4">{{ $c4 && !is_null($c4->capaian_kinerja) ? $c4->capaian_kinerja : '-' }}</td>
                                    
                                    <td class="text-center bg-realisasi capaian-thn-cell" data-id="{{ $ind->id }}" data-tw="1">{{ $c1 && !is_null($c1->capaian_tahunan) ? $c1->capaian_tahunan : '-' }}</td>
                                    <td class="text-center bg-realisasi capaian-thn-cell" data-id="{{ $ind->id }}" data-tw="2">{{ $c2 && !is_null($c2->capaian_tahunan) ? $c2->capaian_tahunan : '-' }}</td>
                                    <td class="text-center bg-realisasi capaian-thn-cell" data-id="{{ $ind->id }}" data-tw="3">{{ $c3 && !is_null($c3->capaian_tahunan) ? $c3->capaian_tahunan : '-' }}</td>
                                    <td class="text-center bg-realisasi capaian-thn-cell" data-id="{{ $ind->id }}" data-tw="4">{{ $c4 && !is_null($c4->capaian_tahunan) ? $c4->capaian_tahunan : '-' }}</td>
                                    
                                    <td>
                                        <div {!! $editAttr !!} data-id="{{ $ind->id }}" data-type="kendala_rtl" data-field="kendala" data-tw="{{ $triwulan }}" style="width: 100%; white-space: pre-wrap;" class="{{ $editClass }} p-1 {{ $compiledKendalaStr ? '' : 'text-center' }}">{{ $compiledKendalaStr ? $compiledKendalaStr : '-' }}</div>
                                    </td>
                                    <td>
                                        <div {!! $editAttr !!} data-id="{{ $ind->id }}" data-type="kendala_rtl" data-field="solusi" data-tw="{{ $triwulan }}" style="width: 100%; white-space: pre-wrap;" class="{{ $editClass }} p-1 {{ $compiledSolusiStr ? '' : 'text-center' }}">{{ $compiledSolusiStr ? $compiledSolusiStr : '-' }}</div>
                                    </td>
                                    <td>
                                        <div {!! $editAttr !!} data-id="{{ $ind->id }}" data-type="kendala_rtl" data-field="rtl" data-tw="{{ $triwulan }}" style="width: 100%; white-space: pre-wrap;" class="{{ $editClass }} p-1 {{ $compiledRtlStr ? '' : 'text-center' }}">{{ $compiledRtlStr ? $compiledRtlStr : '-' }}</div>
                                    </td>
                                    
                                    <td class="text-center">{{ $picName }}</td>
                                    
                                    <td class="text-center">
                                        @if($kendala && $kendala->batas_waktu)
                                            {{ \Carbon\Carbon::parse($kendala->batas_waktu)->format('d-m-Y') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    
                                    <td class="text-center">
                                        @if($capaian && $capaian->link_bukti_kinerja)
                                            <a href="{{ $capaian->link_bukti_kinerja }}" target="_blank" class="text-primary"><i class="fas fa-link"></i> Link</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($capaian && $capaian->link_bukti_tindak_lanjut)
                                            <a href="{{ $capaian->link_bukti_tindak_lanjut }}" target="_blank" class="text-primary"><i class="fas fa-link"></i> Link</a>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                @if($ind->tipe === '%')
                                    @php
                                        $tx1 = $target ? $target->target_x_tw1 : '-';
                                        $tx2 = $target ? $target->target_x_tw2 : '-';
                                        $tx3 = $target ? $target->target_x_tw3 : '-';
                                        $tx4 = $target ? $target->target_x_tw4 : '-';
                                        
                                        $ty1 = $target ? $target->target_y_tw1 : '-';
                                        $ty2 = $target ? $target->target_y_tw2 : '-';
                                        $ty3 = $target ? $target->target_y_tw3 : '-';
                                        $ty4 = $target ? $target->target_y_tw4 : '-';
                                        
                                        $rx1 = $ind->realisasis->where('triwulan', 1)->first()->realisasi_x ?? '-';
                                        $rx2 = $ind->realisasis->where('triwulan', 2)->first()->realisasi_x ?? '-';
                                        $rx3 = $ind->realisasis->where('triwulan', 3)->first()->realisasi_x ?? '-';
                                        $rx4 = $ind->realisasis->where('triwulan', 4)->first()->realisasi_x ?? '-';
                                        
                                        $ry1 = $ind->realisasis->where('triwulan', 1)->first()->realisasi_y ?? '-';
                                        $ry2 = $ind->realisasis->where('triwulan', 2)->first()->realisasi_y ?? '-';
                                        $ry3 = $ind->realisasis->where('triwulan', 3)->first()->realisasi_y ?? '-';
                                        $ry4 = $ind->realisasis->where('triwulan', 4)->first()->realisasi_y ?? '-';
                                    @endphp
                                    <tr class="row-indikator" style="background-color: #fbfbfb;">
                                        <td class="sticky-col-main ps-3 text-muted small">
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-light text-dark border me-2" style="opacity: 0;">{{ $ind->kode }}</span>
                                                <span style="white-space: normal;">X: {{ $ind->definisi_x ?? 'Pembilang (X)' }}</span>
                                            </div>
                                        </td>
                                        <td></td><td></td><td></td><td></td><td></td>
                                        <td class="text-center bg-target">{{ $tx1 }}</td>
                                        <td class="text-center bg-target">{{ $tx2 }}</td>
                                        <td class="text-center bg-target">{{ $tx3 }}</td>
                                        <td class="text-center bg-target">{{ $tx4 }}</td>
                                        <td class="text-center bg-realisasi {{ $editClass }}" {!! $editAttr !!} data-id="{{ $ind->id }}" data-type="realisasi" data-field="realisasi_x" data-tw="1">{{ $rx1 }}</td>
                                        <td class="text-center bg-realisasi {{ $editClass }}" {!! $editAttr !!} data-id="{{ $ind->id }}" data-type="realisasi" data-field="realisasi_x" data-tw="2">{{ $rx2 }}</td>
                                        <td class="text-center bg-realisasi {{ $editClass }}" {!! $editAttr !!} data-id="{{ $ind->id }}" data-type="realisasi" data-field="realisasi_x" data-tw="3">{{ $rx3 }}</td>
                                        <td class="text-center bg-realisasi {{ $editClass }}" {!! $editAttr !!} data-id="{{ $ind->id }}" data-type="realisasi" data-field="realisasi_x" data-tw="4">{{ $rx4 }}</td>
                                        <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                                    </tr>
                                    <tr class="row-indikator" style="background-color: #fbfbfb;">
                                        <td class="sticky-col-main ps-3 text-muted small">
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-light text-dark border me-2" style="opacity: 0;">{{ $ind->kode }}</span>
                                                <span style="white-space: normal;">Y: {{ $ind->definisi_y ?? 'Penyebut (Y)' }}</span>
                                            </div>
                                        </td>
                                        <td></td><td></td><td></td><td></td><td></td>
                                        <td class="text-center bg-target">{{ $ty1 }}</td>
                                        <td class="text-center bg-target">{{ $ty2 }}</td>
                                        <td class="text-center bg-target">{{ $ty3 }}</td>
                                        <td class="text-center bg-target">{{ $ty4 }}</td>
                                        <td class="text-center bg-realisasi {{ $editClass }}" {!! $editAttr !!} data-id="{{ $ind->id }}" data-type="realisasi" data-field="realisasi_y" data-tw="1">{{ $ry1 }}</td>
                                        <td class="text-center bg-realisasi {{ $editClass }}" {!! $editAttr !!} data-id="{{ $ind->id }}" data-type="realisasi" data-field="realisasi_y" data-tw="2">{{ $ry2 }}</td>
                                        <td class="text-center bg-realisasi {{ $editClass }}" {!! $editAttr !!} data-id="{{ $ind->id }}" data-type="realisasi" data-field="realisasi_y" data-tw="3">{{ $ry3 }}</td>
                                        <td class="text-center bg-realisasi {{ $editClass }}" {!! $editAttr !!} data-id="{{ $ind->id }}" data-type="realisasi" data-field="realisasi_y" data-tw="4">{{ $ry4 }}</td>
                                        <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                                    </tr>
                                @endif
                            @endforeach
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="30" class="text-center py-5 text-muted">
                                <i class="fas fa-folder-open fs-1 mb-3 opacity-50"></i><br>
                                Tidak ada data ditemukan untuk filter tersebut.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- UPLOAD MODAL -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-primary text-white border-0 rounded-top-4">
                <h5 class="modal-title fw-bold"><i class="fas fa-file-excel me-2"></i> Upload Excel FRA</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('fra.preview') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-info bg-info bg-opacity-10 border-0 rounded-3 mb-4 small">
                        <i class="fas fa-info-circle me-1"></i> Silakan export data FRA terlebih dahulu jika Anda belum memiliki template terisi.
                    </div>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tahun</label>
                            <select name="tahun" class="form-select rounded-3" required>
                                @for($i = date('Y') - 1; $i <= date('Y') + 2; $i++)
                                    <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Triwulan (Preview)</label>
                            <select name="triwulan" class="form-select rounded-3" required>
                                <option value="1" {{ $triwulan == 1 ? 'selected' : '' }}>Triwulan 1</option>
                                <option value="2" {{ $triwulan == 2 ? 'selected' : '' }}>Triwulan 2</option>
                                <option value="3" {{ $triwulan == 3 ? 'selected' : '' }}>Triwulan 3</option>
                                <option value="4" {{ $triwulan == 4 ? 'selected' : '' }}>Triwulan 4</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">File Excel (.xlsx)</label>
                        <input type="file" name="file_excel" class="form-control rounded-3" accept=".xlsx, .xls" required>
                        <div class="form-text">Pastikan Anda menggunakan struktur template FRA yang disediakan sistem.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4 shadow-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                        <i class="fas fa-search me-1"></i> Preview Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let csrfToken = $('meta[name="csrf-token"]').attr('content');

    $('.live-edit').on('focus', function() {
        $(this).data('original', $(this).text().trim());
    });

    $('.live-edit').on('blur', function() {
        let $cell = $(this);
        let id = $cell.data('id');
        let type = $cell.data('type');
        let field = $cell.data('field');
        let tw = $cell.data('tw');
        let value = $cell.text().trim();
        let original = $cell.data('original');

        if (value === original) {
            return; // Tidak ada perubahan, jangan kirim AJAX
        }

        // Optional: show some loading state or disable edit temporarily
        $cell.css('background-color', '#e2e3e5');

        $.ajax({
            url: "{{ route('fra.live_update') }}",
            type: "POST",
            data: {
                _token: csrfToken,
                indikator_id: id,
                type: type,
                field: field,
                triwulan: tw,
                value: value
            },
            success: function(response) {
                // Background hijau
                $cell.css('background-color', '#d4edda');
                
                // Jika response mengandung perhitungan ulang
                if (response.realisasi_kumulatif !== undefined) {
                    // Update sel realisasi kumulatif (baris utama)
                    let $rkCell = $('.live-edit[data-id="'+id+'"][data-field="realisasi_kumulatif"][data-tw="'+tw+'"]');
                    if ($rkCell.length) {
                        $rkCell.text(response.realisasi_kumulatif);
                        $rkCell.css('background-color', '#d4edda');
                        setTimeout(() => { $rkCell.css('background-color', ''); }, 1500);
                    }
                }

                if (response.capaian_tw !== undefined) {
                    let $capTwCell = $('.capaian-tw-cell[data-id="'+id+'"][data-tw="'+tw+'"]');
                    if ($capTwCell.length) {
                        $capTwCell.text(response.capaian_tw);
                        $capTwCell.css('background-color', '#d4edda');
                        setTimeout(() => { $capTwCell.css('background-color', ''); }, 1500);
                    }
                }
                
                if (response.capaian_thn !== undefined) {
                    let $capThnCell = $('.capaian-thn-cell[data-id="'+id+'"][data-tw="'+tw+'"]');
                    if ($capThnCell.length) {
                        $capThnCell.text(response.capaian_thn);
                        $capThnCell.css('background-color', '#d4edda');
                        setTimeout(() => { $capThnCell.css('background-color', ''); }, 1500);
                    }
                }

                setTimeout(() => {
                    $cell.css('background-color', originalBg);
                }, 1500);
                
                // Update original value
                $cell.data('original', value);
            },
            error: function(xhr) {
                $cell.css('background-color', '#f8d7da');
                console.error(xhr.responseText);
                alert('Terjadi kesalahan saat menyimpan data.');
            }
        });
    });

    // Also trigger blur on Enter key for single-line inputs like realisasi
    $('.live-edit[data-type="realisasi"]').on('keydown', function(e) {
        if(e.keyCode === 13) {
            e.preventDefault();
            $(this).blur();
        }
    });
});
</script>
@endsection
