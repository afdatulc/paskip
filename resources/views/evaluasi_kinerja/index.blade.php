@extends('layouts.dashboard')

@section('title', '')

@section('content')
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-1">Evaluasi Kinerja - Triwulan {{ $triwulan }} - {{ $tahun }}</h4>
            <div class="text-muted small">Daftar Indikator Kinerja Utama dan Pelaporan Kendala.</div>
        </div>
        
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom p-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <h6 class="fw-bold text-primary mb-0"><i class="fas fa-list-check me-2"></i>Daftar Indikator Kinerja</h6>
            <form action="{{ route('evaluasi-kinerja.index') }}" method="GET" class="d-flex gap-2">
                <select name="tahun" class="form-select form-select-sm rounded-pill px-3 shadow-sm border-light-subtle" onchange="this.form.submit()" style="min-width: 100px;">
                    @for($i = date('Y') - 2; $i <= date('Y') + 1; $i++)
                        <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
                <select name="triwulan" class="form-select form-select-sm rounded-pill px-3 shadow-sm border-light-subtle" onchange="this.form.submit()" style="min-width: 130px;">
                    <option value="1" {{ $triwulan == 1 ? 'selected' : '' }}>Triwulan 1</option>
                    <option value="2" {{ $triwulan == 2 ? 'selected' : '' }}>Triwulan 2</option>
                    <option value="3" {{ $triwulan == 3 ? 'selected' : '' }}>Triwulan 3</option>
                    <option value="4" {{ $triwulan == 4 ? 'selected' : '' }}>Triwulan 4</option>
                </select>
            </form>
        </div>
        <div class="card-body p-2">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 p-2" id="evaluasiTable" style="font-size: 0.9rem;">
                    <thead class="table-light">
                        <tr>
                            <th width="50" class="text-center">No</th>
                            <th>Indikator</th>
                            <th class="text-end">Target</th>
                            <th class="text-end">Realisasi</th>
                            <th class="text-center">Capaian (%)</th>
                            <th width="150" class="text-center">Aksi</th>
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
                                $targetField = 'target_tw' . $triwulan;
                                $targetVal = $ind->target ? $ind->target->$targetField : 0;
                                $realisasiVal = $realisasi ? $realisasi->realisasi_kumulatif : 0;
                                
                                $capaianPersen = 0;
                                if ($targetVal > 0) {
                                    $capaianPersen = ($realisasiVal / $targetVal) * 100;
                                }
                                if ($capaianPersen > 120) {
                                    $capaianPersen = 120;
                                }
                                
                                $targetTahunanVal = (float)($ind->target_tahunan ?? 0);
                                $capaianTahunanPersen = 0;
                                if ($targetTahunanVal > 0) {
                                    $capaianTahunanPersen = ($realisasiVal / $targetTahunanVal) * 100;
                                }
                                if ($capaianTahunanPersen > 120) {
                                    $capaianTahunanPersen = 120;
                                }
                                
                                $sumCapaianTriwulan += $capaianPersen;
                                $sumCapaianTahunan += $capaianTahunanPersen;
                                $countIndikator++;
                                if ($capaianPersen != 0) $countIndikatorTriwulan++;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill mb-1">{{ $ind->kode }}</span>
                                    <div class="fw-bold text-dark">{{ $ind->indikator_kinerja }}</div>
                                </td>
                                <td class="text-end fw-bold">{{ $targetVal }}</td>
                                <td class="text-end fw-bold text-primary">{{ $realisasiVal ?? '-' }}</td>
                                <td class="text-center">
                                    @if($capaianPersen >= 100)
                                        <span class="badge bg-success rounded-pill px-3 py-2">{{ number_format($capaianPersen, 1) }}%</span>
                                    @elseif($capaianPersen > 0)
                                        <span class="badge bg-warning text-dark rounded-pill px-3 py-2">{{ number_format($capaianPersen, 1) }}%</span>
                                    @else
                                        <span class="badge bg-secondary rounded-pill px-3 py-2">0%</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('target.show', $ind->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill w-100 mb-1">
                                        <i class="fas fa-search me-1"></i> Lihat Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    Tidak ada data IKU pada periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($countIndikatorTriwulan > 0 || $countIndikator > 0)
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="4" class="text-end">Rata-rata Capaian Triwulanan:</td>
                            <td class="text-center text-primary">{{ $countIndikatorTriwulan > 0 ? number_format($sumCapaianTriwulan / $countIndikatorTriwulan, 2) : 0 }}%</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td colspan="4" class="text-end">Rata-rata Capaian Tahunan:</td>
                            <td class="text-center text-success">{{ $countIndikator > 0 ? number_format($sumCapaianTahunan / $countIndikator, 2) : 0 }}%</td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>


@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        
        @if($indikators->count() > 0)
        $('#evaluasiTable').DataTable({
            language: window.DATATABLES_ID,
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            ordering: false
        });
        @endif
    });
</script>
@endsection
