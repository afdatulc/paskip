@extends('layouts.dashboard')

@section('title', 'Rekapitulasi Capaian Kinerja ' . $tahun)

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
    }
    .table-rekap-excel thead th {
        background-color: #d1e7dd;
        text-align: center;
        position: sticky;
        top: 0;
        z-index: 10;
        font-weight: 700;
    }
    
    /* Dual Sticky Columns */
    .sticky-col-1 {
        position: sticky;
        left: 0;
        background-color: #f8f9fa !important;
        z-index: 20;
        width: 50px;
        min-width: 50px;
        text-align: center;
        border-right: 2px solid #dee2e6 !important;
    }
    .sticky-col-2 {
        position: sticky;
        left: 50px;
        background-color: #ffffff !important;
        z-index: 20;
        min-width: 450px;
        border-right: 2px solid #dee2e6 !important;
    }
    
    /* Ensure headers stay on top of sticky columns */
    thead th.sticky-col-1 { z-index: 30; top: 0; }
    thead th.sticky-col-2 { z-index: 30; top: 0; }

    .row-tujuan {
        background-color: #e9ecef !important;
        font-weight: 700;
        color: #212529;
    }
    .row-sasaran {
        background-color: #ffffff !important;
        font-weight: 600;
    }
    .row-indikator {
        background-color: #ffffff !important;
    }
    .row-kegiatan {
        background-color: #ffffff !important;
        color: #6c757d;
        font-style: italic;
    }
    
    .bg-target { background-color: #f8f9fa; }
    .bg-realisasi { background-color: #fff; }
    
    .scroll-container {
        height: calc(100vh - 200px);
        overflow: auto;
        border: 1px solid #dee2e6;
        border-radius: 8px;
    }
    
    .sub-head {
        font-size: 0.7rem;
        background-color: #f1f8f5 !important;
    }
</style>
@endsection

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <h5 class="mb-0 fw-bold"><i class="fas fa-file-excel me-2 text-success"></i>Kertas Kerja Pengukuran Kinerja {{ $tahun }}</h5>
            <div class="d-flex gap-2 align-items-center">
                <form action="{{ route('rekap.capaian') }}" method="GET" class="d-flex gap-2 align-items-center">
                    <select name="tahun" class="form-select form-select-sm" style="width: 100px;" onchange="this.form.submit()">
                        @for($y = date('Y'); $y >= 2025; $y--)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </form>
                <a href="{{ route('rekap.capaian.export', ['tahun' => $tahun]) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-file-export me-1"></i> Export Excel
                </a>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="scroll-container">
            <table class="table-rekap-excel">
                <thead>
                    <tr>
                        <th rowspan="2" class="sticky-col-1">No</th>
                        <th rowspan="2" class="sticky-col-2">Tujuan / Sasaran / Indikator Kinerja</th>
                        <th rowspan="2" width="100">Jenis (IKU/Proksi)</th>
                        <th rowspan="2" width="120">Jenis (Periode)</th>
                        <th rowspan="2" width="100">Jenis (%/Non %)</th>
                        <th rowspan="2" width="100">Target</th>
                        <th rowspan="2" width="100">Satuan</th>
                        <th colspan="4" class="bg-target">Alokasi Target (Kumulatif)</th>
                        <th colspan="4" class="bg-realisasi">Realisasi (Kumulatif)</th>
                        <th rowspan="2" style="min-width: 300px;">Kendala / Hambatan</th>
                    </tr>
                    <tr>
                        <th class="sub-head bg-target">TW I</th>
                        <th class="sub-head bg-target">TW II</th>
                        <th class="sub-head bg-target">TW III</th>
                        <th class="sub-head bg-target">TW IV</th>
                        <th class="sub-head bg-realisasi">TW I</th>
                        <th class="sub-head bg-realisasi">TW II</th>
                        <th class="sub-head bg-realisasi">TW III</th>
                        <th class="sub-head bg-realisasi">TW IV</th>
                    </tr>
                </thead>
                <tbody>
                    @php $globalNo = 1; @endphp
                    @foreach($grouped as $tujuan => $sasaranGroups)
                        <tr class="row-tujuan">
                            <td class="sticky-col-1"></td>
                            <td class="sticky-col-2">
                                @php
                                    $firstIndInTujuan = null;
                                    foreach($sasaranGroups as $sasGroup) {
                                        $firstIndInTujuan = $sasGroup->first();
                                        if($firstIndInTujuan) break;
                                    }
                                    $kodeTujuan = $firstIndInTujuan ? $firstIndInTujuan->kode_tujuan : null;
                                @endphp
                                {{ ($kodeTujuan ? $kodeTujuan . ' - ' : '') . ($tujuan ?: 'Tanpa Tujuan') }}
                            </td>
                            <td colspan="14"></td>
                        </tr>
                        @foreach($sasaranGroups as $sasaran => $indicators)
                            <tr class="row-sasaran">
                                <td class="sticky-col-1"></td>
                                <td class="sticky-col-2" style="padding-left: 30px;">
                                    @php
                                        $kodeSasaran = $indicators->first() ? $indicators->first()->kode_sasaran : null;
                                    @endphp
                                    <span class="text-muted small me-2">Sasaran:</span> 
                                    {{ ($kodeSasaran ? $kodeSasaran . ' - ' : '') . ($sasaran ?: 'Tanpa Sasaran') }}
                                </td>
                                <td colspan="14"></td>
                            </tr>
                            @foreach($indicators as $i)
                                @php
                                    $target = $i->target;
                                    $realisasis = $i->realisasis;
                                @endphp
                                <tr class="row-indikator">
                                    <td class="sticky-col-1">{{ $globalNo++ }}</td>
                                    <td class="sticky-col-2" style="padding-left: 50px;">
                                        <div class="fw-bold">{{ $i->kode_indikator_kinerja ?: $i->kode }}</div>
                                        <div>{{ $i->indikator_kinerja }}</div>
                                    </td>
                                    <td class="text-center">{{ $i->jenis_indikator }}</td>
                                    <td class="text-center">{{ $i->periode }}</td>
                                    <td class="text-center">{{ $i->tipe == 'Persen' ? '%' : 'Non %' }}</td>
                                    <td class="text-center fw-bold bg-light">{{ $i->target_tahunan }}</td>
                                    <td class="text-center">{{ $i->satuan }}</td>
                                    
                                    {{-- Targets --}}
                                    <td class="text-center bg-target">{{ $target->target_tw1 ?? 0 }}</td>
                                    <td class="text-center bg-target">{{ $target->target_tw2 ?? 0 }}</td>
                                    <td class="text-center bg-target">{{ $target->target_tw3 ?? 0 }}</td>
                                    <td class="text-center bg-target">{{ $target->target_tw4 ?? 0 }}</td>
                                    
                                    {{-- Realisasis --}}
                                    <td class="text-center bg-realisasi fw-bold">
                                        @php $r1 = $realisasis->where('triwulan', 1)->first(); @endphp
                                        <span class="{{ auth()->user()->isAdmin() && $r1 ? 'cursor-pointer text-decoration-underline' : '' }}" 
                                              @if(auth()->user()->isAdmin() && $r1) onclick="showHistory({{ $r1->id }}, 'TW I')" @endif>
                                            {{ $r1->realisasi_kumulatif ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="text-center bg-realisasi fw-bold">
                                        @php $r2 = $realisasis->where('triwulan', 2)->first(); @endphp
                                        <span class="{{ auth()->user()->isAdmin() && $r2 ? 'cursor-pointer text-decoration-underline' : '' }}" 
                                              @if(auth()->user()->isAdmin() && $r2) onclick="showHistory({{ $r2->id }}, 'TW II')" @endif>
                                            {{ $r2->realisasi_kumulatif ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="text-center bg-realisasi fw-bold">
                                        @php $r3 = $realisasis->where('triwulan', 3)->first(); @endphp
                                        <span class="{{ auth()->user()->isAdmin() && $r3 ? 'cursor-pointer text-decoration-underline' : '' }}" 
                                              @if(auth()->user()->isAdmin() && $r3) onclick="showHistory({{ $r3->id }}, 'TW III')" @endif>
                                            {{ $r3->realisasi_kumulatif ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="text-center bg-realisasi fw-bold">
                                        @php $r4 = $realisasis->where('triwulan', 4)->first(); @endphp
                                        <span class="{{ auth()->user()->isAdmin() && $r4 ? 'cursor-pointer text-decoration-underline' : '' }}" 
                                              @if(auth()->user()->isAdmin() && $r4) onclick="showHistory({{ $r4->id }}, 'TW IV')" @endif>
                                            {{ $r4->realisasi_kumulatif ?? 0 }}
                                        </span>
                                    </td>

                                    <td rowspan="{{ count($i->kegiatanMasters) + 1 }}" class="align-top">
                                        @php $kendalas = $i->kendalaRtls->where('tahun', $tahun)->pluck('kendala')->filter()->unique(); @endphp
                                        @if($kendalas->count() > 0)
                                            <ul class="mb-0 ps-3">
                                                @foreach($kendalas as $k)
                                                    <li class="mb-1 text-danger small">{{ $k }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @foreach($i->kegiatanMasters as $k)
                                    <tr class="row-kegiatan">
                                        <td class="sticky-col-1"></td>
                                        <td class="sticky-col-2" style="padding-left: 70px;">
                                            <i class="fas fa-caret-right me-1 text-muted"></i> {{ $k->nama_kegiatan }}
                                        </td>
                                        <td colspan="13"></td>
                                    </tr>
                                @endforeach
                            @endforeach
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@if(auth()->user()->isAdmin())
<!-- Modal History -->
<div class="modal fade" id="modalHistory" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light border-0">
                <h6 class="modal-title fw-bold">Histori Perubahan <span id="historyTwLabel"></span></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0" style="font-size: 0.8rem;">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3 py-3">Waktu</th>
                                <th class="py-3">User</th>
                                <th class="py-3 text-center">N. Lama</th>
                                <th class="py-3 text-center">N. Baru</th>
                            </tr>
                        </thead>
                        <ul id="historyList" class="list-group list-group-flush">
                            <!-- Data populated via AJAX -->
                        </ul>
                        <div id="historyLoading" class="text-center py-4 d-none">
                            <div class="spinner-border spinner-border-sm text-primary"></div>
                        </div>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
    @if(auth()->user()->isAdmin())
    function showHistory(realisasiId, twLabel) {
        $('#historyTwLabel').text(twLabel);
        $('#historyList').empty();
        $('#historyLoading').removeClass('d-none');
        
        const modal = new bootstrap.Modal(document.getElementById('modalHistory'));
        modal.show();

        fetch(`/realisasi/history/${realisasiId}`)
            .then(response => response.json())
            .then(data => {
                $('#historyLoading').addClass('d-none');
                if (data.length === 0) {
                    $('#historyList').append('<li class="list-group-item text-center py-3 text-muted small">Tidak ada histori perubahan.</li>');
                } else {
                    data.forEach(log => {
                        const date = new Date(log.created_at).toLocaleString('id-ID');
                        const oldVal = log.old_value !== null ? log.old_value : '-';
                        $('#historyList').append(`
                            <li class="list-group-item border-0 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold small text-dark">${log.user.name}</span>
                                    <span class="extra-small text-muted">${date}</span>
                                </div>
                                <div class="d-flex gap-3 extra-small">
                                    <span class="text-muted text-decoration-line-through">Lama: ${oldVal}</span>
                                    <i class="fas fa-arrow-right text-success mt-1"></i>
                                    <span class="fw-bold text-success text-primary">Baru: ${log.new_value}</span>
                                </div>
                            </li>
                        `);
                    });
                }
            })
            .catch(error => {
                $('#historyLoading').addClass('d-none');
                $('#historyList').append('<li class="list-group-item text-danger text-center py-3">Gagal memuat data histori.</li>');
            });
    }
    @endif
</script>
@endsection
