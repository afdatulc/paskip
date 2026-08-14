@extends('layouts.dashboard')

@section('title', 'Dashboard Monitoring ' . $tahun)

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-0">
            <div class="card-body py-3">
                <div class="row align-items-center g-3">
                    <div class="col-auto">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-filter me-2 text-primary"></i> Filter Periode Dashboard</h6>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select form-select-sm bg-light border-0 filter-period" id="filterTahun">
                            <option value="2025" {{ $tahun == 2025 ? 'selected' : '' }}>2025</option>
                            <option value="2026" {{ $tahun == 2026 ? 'selected' : '' }}>2026</option>
                            <option value="2027" {{ $tahun == 2027 ? 'selected' : '' }}>2027</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-select form-select-sm bg-light border-0 filter-period" id="filterTriwulan">
                            <option value="1" {{ $triwulan == 1 ? 'selected' : '' }}>Triwulan I</option>
                            <option value="2" {{ $triwulan == 2 ? 'selected' : '' }}>Triwulan II</option>
                            <option value="3" {{ $triwulan == 3 ? 'selected' : '' }}>Triwulan III</option>
                            <option value="4" {{ $triwulan == 4 ? 'selected' : '' }}>Triwulan IV</option>
                        </select>
                    </div>
                    <div class="col-auto ms-auto d-none d-md-block">
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2">
                            <i class="fas fa-calendar-check me-1"></i> Data Real-time
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="stat-card bg-blue">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="opacity-75 small">Total Indikator</div>
                    <div class="fs-2 fw-bold">{{ $summary['total'] }}</div>
                </div>
                <i class="fas fa-list-ol fa-2x opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-green">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="opacity-75 small">Capaian &ge; 100%</div>
                    <div class="fs-2 fw-bold">{{ $summary['hijau'] }}</div>
                </div>
                <i class="fas fa-check-double fa-2x opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-yellow">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="opacity-75 small">Capaian 80-99%</div>
                    <div class="fs-2 fw-bold">{{ $summary['kuning'] }}</div>
                </div>
                <i class="fas fa-triangle-exclamation fa-2x opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card bg-red">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="opacity-75 small">Capaian &lt; 80%</div>
                    <div class="fs-2 fw-bold">{{ $summary['merah'] }}</div>
                </div>
                <i class="fas fa-circle-exclamation fa-2x opacity-50"></i>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Grafik Capaian Tahunan per Indikator</span>
            </div>
            <div class="card-body">
                <canvas id="capaianChart" height="300"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">Distribusi Status</div>
            <div class="card-body">
                <canvas id="statusChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>



<!-- Modals -->
<div class="modal fade" id="modalAktivitas" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="{{ route('public.aktivitas.store') }}" method="POST" enctype="multipart/form-data" class="modal-content border-0 shadow-lg rounded-4">
            @csrf
            <input type="hidden" name="indikator_id" class="hidden-iku">
            <input type="hidden" name="kegiatan_id" class="hidden-kegiatan">
            <input type="hidden" name="triwulan" value="{{ $triwulan }}">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Tambah / Edit Aktivitas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-primary border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center">
                    <i class="fas fa-user-check me-3 fs-5"></i>
                    <div>Melaporkan sebagai: <strong>{{ auth()->user()->name }}</strong></div>
                </div>
                <div class="mb-3" id="tahapan_wrapper">
                    <label class="form-label small fw-bold mb-1">Tahap yang Sedang Dikerjakan</label>
                    <select name="tahapan" id="tahapan_select" class="form-select select2-modal" required>
                        <option value="" disabled selected>-- Pilih Tahapan --</option>
                    </select>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" class="form-control rounded-3" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" class="form-control rounded-3" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Uraian Aktivitas</label>
                    <textarea name="uraian" class="form-control rounded-3" rows="4" placeholder="Deskripsikan pekerjaan yang dilakukan..." required></textarea>
                </div>
                <div class="mb-0">
                    <label class="form-label fw-bold small">Lampiran Bukti</label>
                    <input type="file" name="lampiran[]" class="form-control rounded-3" multiple>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan Aktivitas</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalKendala" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('public.kendala.store') }}" method="POST" class="modal-content border-0 shadow-lg rounded-4">
            @csrf
            <input type="hidden" name="indikator_id" class="hidden-iku">
            <input type="hidden" name="kegiatan_id" class="hidden-kegiatan">
            <input type="hidden" name="triwulan" value="{{ $triwulan }}">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Laporkan Kendala</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center bg-danger bg-opacity-10 text-danger">
                    <i class="fas fa-user-ninja me-3 fs-5"></i>
                    <div>Melaporkan sebagai: <strong>{{ auth()->user()->name }}</strong></div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold mb-1">Kendala yang Dihadapi</label>
                    <textarea name="kendala" class="form-control rounded-3" rows="3" placeholder="Jelaskan hambatan..." required></textarea>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold mb-1">Solusi</label>
                        <textarea name="solusi" class="form-control rounded-3" rows="2" placeholder="Apa yang sudah dilakukan?"></textarea>
                    </div>
                    <div class="col-md-6 rtl-field">
                        <label class="form-label small fw-bold mb-1">Rencana Tindak Lanjut</label>
                        <textarea name="rencana_tindak_lanjut" class="form-control rounded-3" rows="2" placeholder="Apa rencana ke depan?"></textarea>
                    </div>
                </div>
                <div class="mb-0">
                    <label class="form-label small fw-bold mb-1">PIC & Batas Waktu RTL</label>
                    <div class="row g-2">
                        <div class="col-6">
                            <select name="pic_tindak_lanjut" id="pic_select_kendala" class="form-select select2-modal">
                                <option value="" disabled selected>-- Pilih PIC --</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <input type="date" name="batas_waktu" class="form-control rounded-3">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger rounded-pill px-4">Kirim Laporan</button>
            </div>
        </form>
    </div>
</div>

<div class="card mt-4 shadow-sm border-0">
    <div class="card-header">Daftar Indikator & Capaian</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="allIndikatorTable">
                <thead class="table-light">
                    <tr>
                        <th width="50" class="text-center">No</th>
                        <th>Indikator Kinerja</th>
                        <th width="300">Progress Capaian Tahunan</th>
                        <th width="100" class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($indikators as $i)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $i->indikator_kinerja }}</div>
                            <div class="extra-small text-muted"><i class="fas fa-crosshairs me-1"></i>{{ $i->sasaran }}</div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="progress flex-grow-1 rounded-pill" style="height: 8px; background-color: rgba(0,0,0,0.05);">
                                    <div class="progress-bar bg-{{ $i->status_warna }} rounded-pill" role="progressbar" 
                                         style="width: {{ min(100, $i->capaian_tahunan) }}%"></div>
                                </div>
                                <span class="ms-3 small fw-bold text-{{ $i->status_warna }}">{{ number_format($i->capaian_tahunan, 1) }}%</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $i->status_warna }} bg-opacity-10 text-{{ $i->status_warna }} border border-{{ $i->status_warna }}-subtle rounded-pill px-3">
                                <i class="fas fa-circle me-1 small"></i> {{ $i->capaian_tahunan >= 100 ? 'Sesuai' : ($i->capaian_tahunan >= 80 ? 'Waspada' : 'Kritis') }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('capaianChart').getContext('2d');
        const statusCtx = document.getElementById('statusChart').getContext('2d');

        const codes = {!! json_encode($indikators->pluck('kode')) !!};
        const fullLabels = {!! json_encode($indikators->pluck('indikator_kinerja')) !!};
        const dataCapaian = {!! json_encode($indikators->pluck('capaian_tahunan')) !!};

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: codes,
                datasets: [{
                    label: 'Capaian (%)',
                    data: dataCapaian,
                    backgroundColor: dataCapaian.map(v => v >= 100 ? '#0F766E' : (v >= 80 ? '#B45309' : '#BE123C')),
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            title: function(context) {
                                return fullLabels[context[0].dataIndex];
                            },
                            label: function(context) {
                                return 'Capaian: ' + context.parsed.y + '%';
                            }
                        }
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        max: 120,
                        title: { display: true, text: 'Capaian (%)' }
                    },
                    x: {
                        title: { display: true, text: 'Kode Indikator' },
                        ticks: {
                            autoSkip: false,
                            maxRotation: 45,
                            minRotation: 45
                        }
                    }
                }
            }
        });

        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Hijau', 'Kuning', 'Merah'],
                datasets: [{
                    data: [{{ $summary['hijau'] }}, {{ $summary['kuning'] }}, {{ $summary['merah'] }}],
                    backgroundColor: ['#0F766E', '#B45309', '#BE123C'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // Filter Period Change Logic
        $('.filter-period').on('change', function () {
            const tahun = $('#filterTahun').val();
            const triwulan = $('#filterTriwulan').val();
            window.location.href = `{{ route('dashboard') }}?tahun=${tahun}&triwulan=${triwulan}`;
        });

        $('#allIndikatorTable').DataTable({
            language: window.DATATABLES_ID,
            pageLength: 10
        });
    });
</script>
@endsection
