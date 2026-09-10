@extends('layouts.dashboard')

@section('title', 'Renstra ' . $renstra->periode)

@section('styles')
<style>
    .target-input {
        width: 100px;
        text-align: center;
        font-weight: 600;
    }
    .trend-badge {
        font-size: 0.7rem;
        padding: 0.25rem 0.6rem;
    }
    .iku-row:hover {
        background: rgba(67, 97, 238, 0.03);
    }
    .chart-container {
        position: relative;
        height: 350px;
    }
    .info-pill {
        background: rgba(67, 97, 238, 0.08);
        color: var(--primary-color);
        padding: 0.4rem 1rem;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }
    .status-dot.success { background: #2ec4b6; }
    .status-dot.warning { background: #ff9f1c; }
    .status-dot.danger { background: #e71d36; }
</style>
@endsection

@section('content')
{{-- Header Info --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('renstra.index') }}" class="btn btn-sm btn-light rounded-pill">
                    <i class="fas fa-arrow-left me-1"></i>Kembali
                </a>
                <span class="info-pill">
                    <i class="fas fa-calendar-alt me-1"></i>Periode {{ $renstra->periode }}
                </span>
                <span class="badge rounded-pill {{ $renstra->is_aktif ? 'bg-success' : 'bg-secondary' }}">
                    {{ $renstra->is_aktif ? 'AKTIF' : 'NONAKTIF' }}
                </span>
            </div>
            @if($renstra->deskripsi)
            <span class="text-muted small">{{ $renstra->deskripsi }}</span>
            @endif
        </div>
    </div>
</div>

{{-- Grafik Tren Capaian Multi-Tahun --}}
@if(!empty($trendData))
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-chart-line me-2 text-primary"></i>Tren Capaian Renstra per Tahun</span>
        <select id="trendIkuSelect" class="form-select form-select-sm w-auto">
            @foreach($trendData as $id => $data)
                <option value="{{ $id }}">{{ $data['kode'] ?: 'IKU' }} — {{ Str::limit($data['nama'], 50) }}</option>
            @endforeach
        </select>
    </div>
    <div class="card-body">
        <div class="chart-container">
            <canvas id="trendChart"></canvas>
        </div>
    </div>
</div>
@endif

{{-- Tabel Target Renstra per IKU --}}
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary bg-opacity-10 text-primary p-2 rounded-circle">
                <i class="fas fa-bullseye"></i>
            </span>
            <div>
                <h6 class="fw-bold mb-0 text-dark">Target Renstra per IKU</h6>
                <div class="text-muted extra-small">Target 5-tahunan periode {{ $renstra->periode }}</div>
            </div>
        </div>
        @if(auth()->user()->isAdmin())
        <div class="d-flex align-items-center gap-2">

            <button class="btn btn-sm btn-light border text-primary rounded-pill px-3 shadow-none fw-semibold" data-bs-toggle="modal" data-bs-target="#modalImportTarget">
                <i class="fas fa-file-import me-1"></i>Import Excel
            </button>
            <button class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTambahTarget">
                <i class="fas fa-plus me-1"></i>Tambah Target
            </button>
        </div>
        @endif
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4" style="min-width: 250px;">Indikator Kinerja</th>
                        @foreach($tahunList as $th)
                        <th class="text-center" style="min-width: 120px;">
                            <div class="fw-bold">{{ $th }}</div>
                            <div class="text-muted" style="font-size: 0.65rem;">Target</div>
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($grouped as $indikatorId => $targets)
                        @php
                            $indikator = $indikators->get($indikatorId);
                            $trend = $trendData[$indikatorId] ?? null;
                        @endphp
                        <tr class="iku-row">
                            <td class="ps-4">
                                <div class="fw-bold small">{{ $indikator->kode ?? '' }}</div>
                                <div class="text-dark small">{{ $indikator->indikator_kinerja }}</div>
                                <div class="text-muted" style="font-size: 0.7rem;">
                                    {{ $indikator->satuan }} · 
                                    <span class="badge bg-{{ $indikator->polarisasi === 'negatif' ? 'warning' : 'info' }} bg-opacity-10 text-{{ $indikator->polarisasi === 'negatif' ? 'warning' : 'info' }} trend-badge">
                                        <i class="fas fa-arrow-{{ $indikator->polarisasi === 'negatif' ? 'down' : 'up' }} me-1"></i>{{ ucfirst($indikator->polarisasi ?? 'positif') }}
                                    </span>
                                </div>
                            </td>
                            @foreach($tahunList as $th)
                                @php
                                    $targetVal = $targets->firstWhere('tahun', $th)?->target ?? '-';
                                    $capaianVal = $trend['capaians'][$th] ?? null;
                                @endphp
                                <td class="text-center">
                                    <div class="fw-bold">{{ $targetVal !== '-' ? number_format($targetVal, 2) : '-' }}</div>
                                    @if($capaianVal !== null && $targetVal !== '-')
                                        <div class="d-flex align-items-center justify-content-center gap-1" style="font-size: 0.7rem;">
                                            <span class="status-dot {{ $capaianVal >= 100 ? 'success' : ($capaianVal >= 80 ? 'warning' : 'danger') }}"></span>
                                            <span class="text-muted">{{ number_format($capaianVal, 1) }}%</span>
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($tahunList) + 1 }}" class="text-center py-5">
                                <i class="fas fa-inbox text-muted d-block mb-2" style="font-size: 2rem;"></i>
                                <span class="text-muted">Belum ada target IKU. Klik "Tambah Target IKU" untuk memulai.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal Tambah Target --}}
@if(auth()->user()->isAdmin())
<div class="modal fade" id="modalTambahTarget" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('renstra.store-target', $renstra) }}" method="POST" class="modal-content border-0 shadow-lg rounded-4">
            @csrf
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-bullseye text-primary me-2"></i>Tambah Target IKU Renstra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-4">
                    <label class="form-label small fw-bold">Pilih Indikator Kinerja</label>
                    <select name="indikator_id" class="form-select select2" required>
                        <option value="">-- Pilih Indikator --</option>
                        @foreach($semuaIndikator as $ind)
                            <option value="{{ $ind->id }}">{{ $ind->kode ?? '' }} — {{ $ind->indikator_kinerja }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-3">
                    @foreach($tahunList as $th)
                    <div class="col-md">
                        <label class="form-label small fw-bold text-center d-block">{{ $th }}</label>
                        <input type="number" name="targets[{{ $th }}]" class="form-control form-control-sm rounded-3 text-center"
                               step="0.01" min="0" placeholder="0.00">
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4 justify-content-center">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                    <i class="fas fa-save me-1"></i>Simpan Target
                </button>
            </div>
        </form>
    </div>
</div>
@endif

<!-- Modal Import Target -->
@if(auth()->user()->isAdmin())
<div class="modal fade" id="modalImportTarget" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('renstra.import-target', $renstra) }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow-lg rounded-4">
            @csrf
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-file-import text-success me-2"></i>Import Target Renstra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info border-0 rounded-3 small mb-3">
                    <i class="fas fa-info-circle me-1" style="justify-content: center;"></i> Download template Excel terlebih dahulu. Seluruh IKU telah terlampir secara otomatis dalam template, isikan target tahunannya saja.
                </div>
                <div class="mb-4 text-center">
                    <a href="{{ route('renstra.download-template', $renstra) }}" class="btn btn-outline-success rounded-pill px-4 fw-bold">
                        <i class="fas fa-download me-1"></i> Download Template Renstra
                    </a>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">Pilih File Excel (.xlsx / .xls / .csv)</label>
                    <input type="file" name="file" class="form-control rounded-3" accept=".xlsx,.xls,.csv" required>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4 justify-content-center">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm">
                    <i class="fas fa-upload me-1"></i>Upload & Import
                </button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@section('scripts')
<script>
@if(!empty($trendData))
    const trendData = @json($trendData);
    const tahunList = @json($tahunList);
    let trendChart;

    function renderTrendChart(indikatorId) {
        const data = trendData[indikatorId];
        if (!data) return;

        const targets = tahunList.map(t => data.targets[t] || 0);


        if (trendChart) trendChart.destroy();

        trendChart = new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: tahunList,
                datasets: [
                    {
                        label: 'Target Renstra',
                        data: targets,
                        backgroundColor: 'rgba(67, 97, 238, 0.2)',
                        borderColor: 'rgba(67, 97, 238, 1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    title: {
                        display: true,
                        text: data.nama,
                        font: { size: 14, weight: 'bold' },
                        padding: { bottom: 20 }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.04)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    document.getElementById('trendIkuSelect').addEventListener('change', function() {
        renderTrendChart(this.value);
    });

    // Render chart pertama
    const firstKey = Object.keys(trendData)[0];
    if (firstKey) renderTrendChart(firstKey);
@endif
</script>
@endsection
