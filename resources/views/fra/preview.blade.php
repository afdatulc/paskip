@extends('layouts.dashboard')

@section('title', 'Preview Data FRA')

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
    .table-rekap-excel thead th {
        background-color: #d1e7dd;
        text-align: center;
        position: sticky;
        top: 0;
        z-index: 10;
        font-weight: 700;
    }
    
    .sticky-col-1 {
        position: sticky;
        left: 0;
        background-color: #f8f9fa !important;
        z-index: 20;
        width: 40px;
        min-width: 40px;
        text-align: center;
        border-right: 2px solid #dee2e6 !important;
    }
    .sticky-col-2 {
        position: sticky;
        left: 40px;
        background-color: #ffffff !important;
        z-index: 20;
        min-width: 350px;
        max-width: 400px;
        white-space: normal !important;
        border-right: 2px solid #dee2e6 !important;
    }
    
    thead th.sticky-col-1 { z-index: 30; top: 0; }
    thead th.sticky-col-2 { z-index: 30; top: 0; }

    .row-tujuan { background-color: #e9ecef !important; font-weight: 700; color: #212529; }
    .row-sasaran { background-color: #fdfdfe !important; font-weight: 600; }
    .row-indikator { background-color: #ffffff !important; }
    
    .bg-target { background-color: #f8f9fa; }
    .bg-realisasi { background-color: #fff; }
    
    .scroll-container {
        height: 400px;
        overflow: auto;
        border: 1px solid #dee2e6;
        border-radius: 8px;
    }
    .sub-head { font-size: 0.7rem; background-color: #f1f8f5 !important; }
</style>
@endsection

@section('content')
<div class="mb-3">
    <a href="{{ route('fra.index') }}" class="btn btn-light border shadow-sm btn-sm px-3 rounded-pill text-nowrap">
        <i class="fas fa-arrow-left me-1"></i> Batal / Kembali
    </a>
</div>

<div class="d-flex align-items-center mb-4">
    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
        <i class="fas fa-eye fs-5"></i>
    </div>
    <div>
        <h5 class="fw-bold mb-1">Preview File Excel</h5>
        <p class="text-muted mb-0 small">Tahun: <strong>{{ $tahun }}</strong> | Triwulan: <strong>{{ $triwulan }}</strong></p>
    </div>
</div>

<!-- SPREADSHEET PREVIEW -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white py-3">
        <h6 class="mb-0 fw-bold"><i class="fas fa-file-excel text-success me-2"></i>Tampilan Data Excel (Pratinjau Spreadsheet)</h6>
    </div>
    <div class="card-body p-0">
        <div class="scroll-container m-3">
            <table class="table-rekap-excel">
                <thead>
                    <tr>
                        <th rowspan="2" class="sticky-col-1">No</th>
                        <th rowspan="2" class="sticky-col-2">Tujuan / Sasaran / Indikator Kinerja</th>
                        <th colspan="4" class="bg-target">Target (M-P)</th>
                        <th colspan="4" class="bg-realisasi">Realisasi (Q-T)</th>
                        <th rowspan="2">Kendala (AC)</th>
                        <th rowspan="2">Solusi (AD)</th>
                        <th rowspan="2">RTL (AE)</th>
                        <th rowspan="2">PIC (AF)</th>
                        <th rowspan="2">Batas Waktu (AG)</th>
                        <th rowspan="2">Link Kinerja (AH)</th>
                        <th rowspan="2">Link RTL (AI)</th>
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
                    @php $no = 1; @endphp
                    @for($i = 10; $i <= count($rows); $i++)
                        @php 
                            $r = $rows[$i]; 
                            $isTujuan = !empty(trim($r['A'] ?? ''));
                            $isSasaran = !empty(trim($r['C'] ?? '')) && empty(trim($r['E'] ?? ''));
                            $isIndikator = !empty(trim($r['D'] ?? ''));
                            
                            $rowClass = 'row-indikator';
                            if($isTujuan) $rowClass = 'row-tujuan';
                            elseif($isSasaran) $rowClass = 'row-sasaran';
                            
                            if(!$isTujuan && !$isSasaran && !$isIndikator && empty(trim(implode('', $r)))) continue; // skip totally empty rows
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <td class="sticky-col-1">{{ $isIndikator ? $no++ : '' }}</td>
                            <td class="sticky-col-2">
                                @if($isTujuan)
                                    {{ $r['A'] }}
                                @elseif($isSasaran)
                                    {{ $r['B'] ? $r['B'].' - ' : '' }}{{ $r['C'] }}
                                @elseif($isIndikator)
                                    <div class="ps-3">
                                        <span class="badge bg-light text-dark border me-1">{{ $r['D'] }}</span> {{ $r['E'] }}
                                    </div>
                                @endif
                            </td>
                            <td>{{ $r['M'] ?? '' }}</td>
                            <td>{{ $r['N'] ?? '' }}</td>
                            <td>{{ $r['O'] ?? '' }}</td>
                            <td>{{ $r['P'] ?? '' }}</td>
                            
                            <!-- Realisasi based on user TW -->
                            <td class="{{ $colRealisasi == 'Q' ? 'bg-warning bg-opacity-25 fw-bold' : '' }}">{{ $r['Q'] ?? '' }}</td>
                            <td class="{{ $colRealisasi == 'R' ? 'bg-warning bg-opacity-25 fw-bold' : '' }}">{{ $r['R'] ?? '' }}</td>
                            <td class="{{ $colRealisasi == 'S' ? 'bg-warning bg-opacity-25 fw-bold' : '' }}">{{ $r['S'] ?? '' }}</td>
                            <td class="{{ $colRealisasi == 'T' ? 'bg-warning bg-opacity-25 fw-bold' : '' }}">{{ $r['T'] ?? '' }}</td>
                            
                            <td>
                                @if(!empty($r['AC']))
                                <div style="max-height: 60px; overflow-y: auto; max-width: 250px; white-space: normal;">{{ $r['AC'] }}</div>
                                @endif
                            </td>
                            <td>
                                @if(!empty($r['AD']))
                                <div style="max-height: 60px; overflow-y: auto; max-width: 250px; white-space: normal;">{{ $r['AD'] }}</div>
                                @endif
                            </td>
                            <td>
                                @if(!empty($r['AE']))
                                <div style="max-height: 60px; overflow-y: auto; max-width: 250px; white-space: normal;">{{ $r['AE'] }}</div>
                                @endif
                            </td>
                            <td>{{ $r['AF'] ?? '' }}</td>
                            <td>
                                @if(!empty($r['AG']))
                                    @if(is_numeric($r['AG']))
                                        @php
                                            try {
                                                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($r['AG'])->format('d-m-Y');
                                            } catch(\Exception $e) {
                                                $date = $r['AG'];
                                            }
                                        @endphp
                                        {{ $date }}
                                    @else
                                        @php
                                            try {
                                                $date = \Carbon\Carbon::parse($r['AG'])->format('d-m-Y');
                                            } catch(\Exception $e) {
                                                $date = $r['AG'];
                                            }
                                        @endphp
                                        {{ $date }}
                                    @endif
                                @endif
                            </td>
                            <td>
                                @if(!empty($r['AH']) && Str::startsWith($r['AH'], 'http'))
                                    <a href="{{ $r['AH'] }}" target="_blank"><i class="fas fa-link"></i></a>
                                @else
                                    {{ $r['AH'] ?? '' }}
                                @endif
                            </td>
                            <td>
                                @if(!empty($r['AI']) && Str::startsWith($r['AI'], 'http'))
                                    <a href="{{ $r['AI'] }}" target="_blank"><i class="fas fa-link"></i></a>
                                @else
                                    {{ $r['AI'] ?? '' }}
                                @endif
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- PERBANDINGAN DATA -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold"><i class="fas fa-exchange-alt text-primary me-2"></i>Perbandingan Data & Konfirmasi</h6>
    </div>
    <div class="card-body p-4">
        <div class="alert alert-warning border-0 rounded-3 small">
            <i class="fas fa-exclamation-triangle me-1"></i> Terdeteksi perbedaan data antara sistem dengan excel yang diimpor. Centang IKU yang ingin Anda simpan.
        </div>

        <form action="{{ route('fra.store') }}" method="POST">
            @csrf
            <input type="hidden" name="tahun" value="{{ $tahun }}">
            <input type="hidden" name="triwulan" value="{{ $triwulan }}">
            <input type="hidden" name="preview_data" value="{{ $previewJson }}">

            <div class="table-responsive mb-4">
                <table class="table table-bordered table-hover align-middle mb-0 text-sm">
                    <thead class="table-light text-center">
                        <tr>
                            <th rowspan="2" style="width: 40px;" class="align-middle">
                                <div class="form-check d-flex justify-content-center">
                                    <input class="form-check-input" type="checkbox" id="checkAll" checked>
                                </div>
                            </th>
                            <th rowspan="2" class="align-middle" style="width: 300px;">Indikator</th>
                            <th colspan="2">Capaian Kinerja (Kumulatif)</th>
                            <th colspan="2">Kendala & RTL</th>
                        </tr>
                        <tr>
                            <th>Data Sistem Saat Ini</th>
                            <th>Data dari Excel</th>
                            <th>Data Sistem Saat Ini</th>
                            <th>Data dari Excel</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($previewData as $index => $item)
                        <tr>
                            <td class="text-center align-top pt-3">
                                <div class="form-check d-flex justify-content-center">
                                    <input class="form-check-input row-check" type="checkbox" name="selected_items[]" value="{{ $index }}" checked>
                                </div>
                            </td>
                            <td class="align-top">
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-2 mb-1">{{ $item['kode'] }}</span>
                                <div class="fw-bold small">{{ $item['indikator_kinerja'] }}</div>
                            </td>
                            
                            <!-- Capaian: Sistem -->
                            <td class="align-top" style="width: 15%;">
                                <div class="small">
                                    <div class="mb-1"><span class="text-muted">Realisasi:</span> {{ $item['db']['realisasi'] ?? '-' }}</div>
                                    @if($item['db']['link_kinerja'] && Str::startsWith($item['db']['link_kinerja'], 'http'))
                                    <div><a href="{{ $item['db']['link_kinerja'] }}" target="_blank" class="text-decoration-none"><i class="fas fa-link"></i> Bukti Kinerja</a></div>
                                    @endif
                                    @if($item['db']['link_rtl_sblm'] && Str::startsWith($item['db']['link_rtl_sblm'], 'http'))
                                    <div><a href="{{ $item['db']['link_rtl_sblm'] }}" target="_blank" class="text-decoration-none"><i class="fas fa-link"></i> Bukti RTL Lalu</a></div>
                                    @endif
                                </div>
                            </td>
                            
                            <!-- Capaian: Excel -->
                            <td class="align-top {{ $item['has_diff_capaian'] ? 'bg-danger bg-opacity-10' : '' }}" style="width: 15%;">
                                <div class="small">
                                    <div class="mb-1">
                                        <span class="text-muted">Realisasi:</span> 
                                        @if($item['excel']['realisasi'] !== $item['db']['realisasi'])
                                            <strong class="text-danger">{{ $item['excel']['realisasi'] ?? '-' }}</strong>
                                        @else
                                            {{ $item['excel']['realisasi'] ?? '-' }}
                                        @endif
                                    </div>
                                    @if($item['excel']['link_kinerja'] && Str::startsWith($item['excel']['link_kinerja'], 'http'))
                                    <div>
                                        <a href="{{ $item['excel']['link_kinerja'] }}" target="_blank" class="text-decoration-none {{ $item['excel']['link_kinerja'] !== $item['db']['link_kinerja'] ? 'text-danger fw-bold' : '' }}">
                                            <i class="fas fa-link"></i> Bukti Kinerja
                                        </a>
                                    </div>
                                    @endif
                                    @if($item['excel']['link_rtl_sblm'] && Str::startsWith($item['excel']['link_rtl_sblm'], 'http'))
                                    <div>
                                        <a href="{{ $item['excel']['link_rtl_sblm'] }}" target="_blank" class="text-decoration-none {{ $item['excel']['link_rtl_sblm'] !== $item['db']['link_rtl_sblm'] ? 'text-danger fw-bold' : '' }}">
                                            <i class="fas fa-link"></i> Bukti RTL Lalu
                                        </a>
                                    </div>
                                    @endif
                                </div>
                            </td>

                            <!-- Kendala: Sistem -->
                            <td class="align-top" style="width: 20%;">
                                <div class="small" style="max-height: 250px; overflow-y: auto; white-space: normal;">
                                    @if($item['db']['kendala'])
                                        <div class="mb-1"><strong class="text-muted">Kendala:</strong><br>{!! nl2br(e($item['db']['kendala'])) !!}</div>
                                    @endif
                                    @if($item['db']['solusi'])
                                        <div class="mb-1"><strong class="text-muted">Solusi:</strong><br>{!! nl2br(e($item['db']['solusi'])) !!}</div>
                                    @endif
                                    @if($item['db']['rtl'])
                                        <div class="mb-1"><strong class="text-muted">RTL:</strong><br>{!! nl2br(e($item['db']['rtl'])) !!}</div>
                                    @endif
                                    @if($item['db']['pic_name'])
                                        <div class="mb-1"><strong class="text-muted">PIC:</strong> {{ $item['db']['pic_name'] }}</div>
                                    @endif
                                    @if(empty($item['db']['kendala']) && empty($item['db']['solusi']) && empty($item['db']['rtl']))
                                        <span class="text-muted fst-italic">Kosong</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Kendala: Excel -->
                            <td class="align-top {{ $item['has_diff_kendala'] ? 'bg-danger bg-opacity-10' : '' }}" style="width: 20%;">
                                <div class="small" style="max-height: 250px; overflow-y: auto; white-space: normal;">
                                    @if($item['excel']['kendala'])
                                        <div class="mb-1"><strong class="text-muted">Kendala:</strong><br>
                                            <span class="{{ $item['excel']['kendala'] !== $item['db']['kendala'] ? 'text-danger fw-bold' : '' }}">{!! nl2br(e($item['excel']['kendala'])) !!}</span>
                                        </div>
                                    @endif
                                    @if($item['excel']['solusi'])
                                        <div class="mb-1"><strong class="text-muted">Solusi:</strong><br>
                                            <span class="{{ $item['excel']['solusi'] !== $item['db']['solusi'] ? 'text-danger fw-bold' : '' }}">{!! nl2br(e($item['excel']['solusi'])) !!}</span>
                                        </div>
                                    @endif
                                    @if($item['excel']['rtl'])
                                        <div class="mb-1"><strong class="text-muted">RTL:</strong><br>
                                            <span class="{{ $item['excel']['rtl'] !== $item['db']['rtl'] ? 'text-danger fw-bold' : '' }}">{!! nl2br(e($item['excel']['rtl'])) !!}</span>
                                        </div>
                                    @endif
                                    @if($item['excel']['pic_name'])
                                        <div class="mb-1"><strong class="text-muted">PIC:</strong> 
                                            <span class="{{ $item['excel']['pic_name'] !== $item['db']['pic_name'] ? 'text-danger fw-bold' : '' }}">
                                                {{ $item['excel']['pic_name'] }} 
                                                @if(!$item['excel']['pic_id']) <i class="fas fa-exclamation-circle text-warning ms-1" title="PIC tidak ditemukan di database"></i> @endif
                                            </span>
                                        </div>
                                    @endif
                                    @if(empty($item['excel']['kendala']) && empty($item['excel']['solusi']) && empty($item['excel']['rtl']))
                                        <span class="text-muted fst-italic">Kosong</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">
                    <i class="fas fa-save me-1"></i> Simpan Data Terpilih
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkAll = document.getElementById('checkAll');
        const rowChecks = document.querySelectorAll('.row-check');

        checkAll.addEventListener('change', function() {
            rowChecks.forEach(check => {
                check.checked = checkAll.checked;
            });
        });

        rowChecks.forEach(check => {
            check.addEventListener('change', function() {
                const allChecked = Array.from(rowChecks).every(c => c.checked);
                const someChecked = Array.from(rowChecks).some(c => c.checked);
                
                checkAll.checked = allChecked;
                checkAll.indeterminate = someChecked && !allChecked;
            });
        });
    });
</script>
@endsection
