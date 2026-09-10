@extends('layouts.dashboard')

@section('title', 'Monitoring Capaian Kinerja')

@section('content')
    <!-- Filter Card -->
    <div class="card mb-4 shadow-sm border-0 rounded-4">
        <div class="card-body">
            <form action="{{ route('monitoring-capaian.index') }}" method="GET" class="d-flex gap-3 align-items-end flex-wrap">
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
                <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 0.85rem;">
                    <thead class="table-light text-center align-middle">
                        <tr>
                            <th rowspan="2" width="40">No</th>
                            <th rowspan="2" style="min-width: 250px;">Indikator Kinerja</th>
                            <th colspan="3">Capaian Terhadap Target Triwulanan</th>
                            <th colspan="3">Capaian Terhadap Target PK (Tahunan)</th>
                        </tr>
                        <tr>
                            <th class="fw-normal">Target TW</th>
                            <th class="fw-normal">Realisasi TW</th>
                            <th class="fw-normal">% Capaian TW</th>
                            <th class="fw-normal">Target PK</th>
                            <th class="fw-normal">Realisasi (Kumulatif)</th>
                            <th class="fw-normal">% Capaian PK</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $sumCapaianTriwulan = 0;
                            $sumCapaianTahunan = 0;
                            $countIndikator = 0;
                            $countIndikatorTriwulan = 0;
                        @endphp
                        @forelse($indikators as $ind)
                            @php 
                                $realisasi = $ind->realisasis->first();
                                $pk = $ind->pkTahunans->first();
                                $targetTwField = 'target_tw' . $triwulan;
                                $targetTw = $ind->target ? $ind->target->$targetTwField : 0;
                                $targetPk = $pk ? $pk->target_efektif : ($ind->target_tahunan ?? 0);
                                
                                $realisasiTw = $realisasi ? $realisasi->realisasi_kumulatif : 0;
                                
                                $persenTw = 0;
                                if ($targetTw > 0) {
                                    $persenTw = ($realisasiTw / $targetTw) * 100;
                                }
                                
                                $persenPk = 0;
                                if ($targetPk > 0) {
                                    $persenPk = ($realisasiTw / $targetPk) * 100;
                                }

                                $persenTwLimit = $persenTw > 120 ? 120 : $persenTw;
                                $persenPkLimit = $persenPk > 120 ? 120 : $persenPk;

                                $sumCapaianTriwulan += $persenTwLimit;
                                $sumCapaianTahunan += $persenPkLimit;
                                $countIndikator++;
                                if ($persenTwLimit != 0) $countIndikatorTriwulan++;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-2 mb-1">{{ $ind->kode }}</span>
                                    <div class="fw-bold text-dark">
                                        @if(in_array($ind->id, $accessibleIndikatorIds))
                                            <a href="{{ route('fra.index', ['tahun' => $tahun, 'triwulan' => $triwulan]) }}" class="text-decoration-none text-primary" title="Buka Pengisian FRA">
                                                {{ $ind->indikator_kinerja }}
                                            </a>
                                        @else
                                            {{ $ind->indikator_kinerja }}
                                        @endif
                                    </div>
                                </td>
                                
                                <td class="text-center">{{ number_format((float)$targetTw, 2) }}</td>
                                <td class="text-center">{{ number_format((float)$realisasiTw, 2) }}</td>
                                <td class="text-center fw-bold {{ $persenTw >= 100 ? 'text-success' : ($persenTw > 0 ? 'text-warning' : 'text-danger') }}">
                                    {{ number_format((float)$persenTw, 2) }}%
                                </td>
                                
                                <td class="text-center">{{ number_format((float)$targetPk, 2) }}</td>
                                <td class="text-center">{{ number_format((float)$realisasiTw, 2) }}</td>
                                <td class="text-center fw-bold {{ $persenPk >= 100 ? 'text-success' : ($persenPk > 0 ? 'text-warning' : 'text-danger') }}">
                                    {{ number_format((float)$persenPk, 2) }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                    Tidak ada indikator kinerja.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($countIndikatorTriwulan > 0 || $countIndikator > 0)
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="4" class="text-end">Rata-rata Capaian Triwulanan:</td>
                            <td class="text-center text-primary">{{ $countIndikatorTriwulan > 0 ? number_format($sumCapaianTriwulan / $countIndikatorTriwulan, 2) : 0 }}%</td>
                            <td colspan="2" class="text-end">Rata-rata Capaian Tahunan:</td>
                            <td class="text-center text-success">{{ $countIndikator > 0 ? number_format($sumCapaianTahunan / $countIndikator, 2) : 0 }}%</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
@endsection
