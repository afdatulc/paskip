@extends('layouts.dashboard')

@section('title', 'Perjanjian Kinerja Tahunan ' . $tahun)

@section('styles')
    <style>
        .pk-summary-card {
            padding: 1.2rem;
            border-radius: 14px;
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .pk-summary-card::after {
            content: '';
            position: absolute;
            right: -15px;
            bottom: -15px;
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .progress-bar-custom {
            height: 6px;
            border-radius: 3px;
            background: rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .progress-bar-custom .bar {
            height: 100%;
            border-radius: 3px;
            transition: width 0.8s ease;
        }

        .revisi-badge {
            font-size: 0.65rem;
            background: rgba(255, 159, 28, 0.12);
            color: #f17105;
            padding: 0.2rem 0.5rem;
            border-radius: 20px;
        }

        .capaian-value {
            font-weight: 700;
            font-size: 1rem;
        }

        .capaian-value.success {
            color: #2ec4b6;
        }

        .capaian-value.warning {
            color: #ff9f1c;
        }

        .capaian-value.danger {
            color: #e71d36;
        }
    </style>
@endsection

@section('content')
    {{-- Filter Tahun --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row align-items-center g-3">
                <div class="col-auto">
                    <h6 class="fw-bold mb-0"><i class="fas fa-filter me-2 text-primary"></i>Pilih Tahun PK</h6>
                </div>
                <div class="col-md-2">
                    <select class="form-select form-select-sm bg-light border-0" id="filterTahunPK"
                        onchange="window.location.href='{{ route('pk-tahunan.index') }}?tahun='+this.value">
                        @for($y = date('Y') - 2; $y <= date('Y') + 2; $y++)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-auto">
                    @if(auth()->user()->isAdmin())
                        <form action="{{ route('pk-tahunan.generate') }}" method="POST" class="d-inline"
                            onsubmit="return confirm('Generate PK Tahunan dari target Renstra aktif untuk tahun {{ $tahun }}?')">
                            @csrf
                            <input type="hidden" name="tahun" value="{{ $tahun }}">
                            <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill">
                                <i class="fas fa-wand-magic-sparkles me-1"></i>Generate dari Renstra
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="pk-summary-card bg-blue">
                <div class="small opacity-75">Total PK</div>
                <div class="fs-3 fw-bold">{{ $summary['total'] }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="pk-summary-card bg-green">
                <div class="small opacity-75">Tercapai</div>
                <div class="fs-3 fw-bold">{{ $summary['tercapai'] }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="pk-summary-card bg-yellow">
                <div class="small opacity-75">Perlu Perhatian</div>
                <div class="fs-3 fw-bold">{{ $summary['perlu_perhatian'] }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="pk-summary-card bg-red">
                <div class="small opacity-75">Kritis</div>
                <div class="fs-3 fw-bold">{{ $summary['kritis'] }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="pk-summary-card" style="background: linear-gradient(135deg, #7209b7, #560bad);">
                <div class="small opacity-75">Direvisi</div>
                <div class="fs-3 fw-bold">{{ $summary['direvisi'] }}</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="pk-summary-card" style="background: linear-gradient(135deg, #2b2d42, #1a1c23);">
                <div class="small opacity-75">Rata² Capaian</div>
                <div class="fs-3 fw-bold">{{ number_format($summary['rata_rata'], 1) }}%</div>
            </div>
        </div>
    </div>

    {{-- Tabel PK Tahunan --}}
    <div class="card">
        <div class="card-header">
            <i class="fas fa-handshake me-2 text-primary"></i>Daftar Perjanjian Kinerja {{ $tahun }}
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover mb-2 align-middle" id="tablePK">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4" style="width: 40px;">No</th>
                            <th style="min-width: 280px;">Indikator Kinerja</th>
                            <th class="text-center">Target Awal</th>
                            <th class="text-center">Target Revisi</th>
                            <th class="text-center">Target Efektif</th>
                            <th class="text-center">Realisasi</th>
                            <th class="text-center" style="width: 140px;">Capaian</th>
                            @if(auth()->user()->isAdmin())
                                <th class="text-center" style="width: 100px;">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pkTahunans as $i => $pk)
                            <tr>
                                <td class="ps-4 text-muted">{{ $i + 1 }}</td>
                                <td>
                                    <div class="fw-bold small">{{ $pk->indikator->kode ?? '' }}</div>
                                    <div class="small text-dark">{{ $pk->indikator->indikator_kinerja }}</div>
                                    <div class="text-muted" style="font-size: 0.7rem;">
                                        {{ $pk->indikator->satuan }}
                                        @if($pk->status_revisi)
                                            <span class="revisi-badge ms-1">
                                                <i class="fas fa-pen-to-square me-1"></i>Direvisi
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center fw-bold">{{ number_format($pk->target_awal, 2) }}</td>
                                <td class="text-center">
                                    @if($pk->target_revisi)
                                        <span class="fw-bold text-warning">{{ number_format($pk->target_revisi, 2) }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center fw-bold text-primary">
                                    {{ number_format($pk->target_efektif, 2) }}
                                    @php $disc = $pk->indikator->discrepancy_q4; @endphp
                                    @if($disc)
                                        <div class="mt-1">
                                            <a href="{{ route('target.index') }}"
                                                class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill text-decoration-none"
                                                style="font-size: 0.65rem;"
                                                title="Target Q4 ({{ number_format($disc['target_tw4'], 2) }}) tidak sama dengan PK Tahunan ({{ number_format($disc['target_pk'], 2) }}). Klik untuk perbaiki di Target Triwulanan.">
                                                <i class="fas fa-exclamation-triangle me-1"></i>Beda Q4
                                                ({{ number_format($disc['target_tw4'], 2) }})
                                            </a>
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">{{ number_format($pk->realisasi_saat_ini, 2) }}</td>
                                <td class="text-center">
                                    @php
                                        $warna = $pk->capaian >= 100 ? 'success' : ($pk->capaian >= 80 ? 'warning' : 'danger');
                                    @endphp
                                    <div class="capaian-value {{ $warna }}">{{ number_format($pk->capaian, 1) }}%</div>
                                    <div class="progress-bar-custom mt-1">
                                        <div class="bar bg-{{ $warna }}" style="width: {{ min($pk->capaian, 120) / 1.2 }}%;">
                                        </div>
                                    </div>
                                </td>
                                @if(auth()->user()->isAdmin())
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-warning rounded-pill px-2" data-bs-toggle="modal"
                                            data-bs-target="#modalRevisi{{ $pk->id }}" title="Revisi Target">
                                            <i class="fas fa-pen-to-square"></i>
                                        </button>
                                        @if($pk->status_revisi)
                                            <form action="{{ route('pk-tahunan.cancel-revisi', $pk) }}" method="POST" class="d-inline"
                                                onsubmit="return confirm('Batalkan revisi?')">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-2"
                                                    title="Batalkan Revisi">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                @endif
                            </tr>

                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <i class="fas fa-handshake text-muted d-block mb-2" style="font-size: 2rem;"></i>
                                    <span class="text-muted">Belum ada PK Tahunan untuk {{ $tahun }}.</span><br>
                                    <span class="text-muted small">Klik "Generate dari Renstra" untuk membuat PK otomatis dari
                                        target Renstra aktif.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal Revisi Target PK (Rendered Outside Table for DOM & DataTables Integrity) --}}
    @if(auth()->user()->isAdmin())
        @foreach($pkTahunans as $pk)
            <div class="modal fade" id="modalRevisi{{ $pk->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <form action="{{ route('pk-tahunan.update', $pk) }}" method="POST"
                        class="modal-content border-0 shadow-lg rounded-4">
                        @csrf @method('PUT')
                        <div class="modal-header border-0 pb-0 pt-4 px-4">
                            <h5 class="modal-title fw-bold text-dark d-flex align-items-center">
                                <span class="badge bg-warning bg-opacity-10 text-warning p-2 rounded-circle me-2">
                                    <i class="fas fa-pen-to-square"></i>
                                </span>
                                Revisi Target Perjanjian Kinerja
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="bg-light rounded-4 p-3 mb-3 border-0">
                                <div class="extra-small fw-bold text-muted text-uppercase mb-1">Indikator Kinerja</div>
                                <div class="fw-bold text-dark small mb-2">{{ $pk->indikator->indikator_kinerja }}</div>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-white rounded-3 px-3 py-1 border border-light shadow-sm">
                                        <span class="extra-small text-muted d-block">Satuan</span>
                                        <span class="fw-bold text-dark small">{{ $pk->indikator->satuan }}</span>
                                    </div>
                                    <div class="bg-white rounded-3 px-3 py-1 border border-light shadow-sm">
                                        <span class="extra-small text-muted d-block">Target Awal Renstra</span>
                                        <span class="fw-bold text-primary small">{{ number_format($pk->target_awal, 2) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark mb-1">Target Revisi Baru</label>
                                <div class="input-group">
                                    <input type="number" name="target_revisi" class="form-control rounded-3 shadow-none"
                                        value="{{ $pk->target_revisi ?? $pk->target_awal }}" step="0.01" min="0" required>
                                    <span
                                        class="input-group-text bg-light rounded-3 text-muted small">{{ $pk->indikator->satuan }}</span>
                                </div>
                                <div class="form-text extra-small">Isikan target baru yang disepakati untuk tahun berjalan.</div>
                            </div>

                            <div class="mb-2">
                                <label class="form-label small fw-bold text-dark mb-1">Alasan Revisi Target</label>
                                <textarea name="alasan_revisi" class="form-control rounded-3 shadow-none" rows="3" required
                                    placeholder="Contoh: Penyesuaian dengan refocusing anggaran / arahan pimpinan pusat">{{ $pk->alasan_revisi }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0 pb-4 px-4 justify-content-end">
                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-warning rounded-pill px-4 shadow-sm fw-bold">
                                <i class="fas fa-save me-1"></i>Simpan Revisi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    @endif
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            if ($.fn.DataTable && document.getElementById('tablePK') && {{ $pkTahunans->count() }} > 0) {
                $('#tablePK').DataTable({
                    language: window.DATATABLES_ID,
                    pageLength: 25,
                    order: [[0, 'asc']],
                });
            }
        });
    </script>
@endsection