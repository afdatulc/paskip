@extends('layouts.dashboard')

@section('title', 'Evaluasi Kinerja Tahunan ' . $tahun)

@section('styles')
<style>
    .eval-summary {
        padding: 1.2rem;
        border-radius: 14px;
        color: #fff;
        position: relative;
        overflow: hidden;
    }
    .eval-summary::after {
        content: '';
        position: absolute;
        right: -15px;
        bottom: -15px;
        width: 80px;
        height: 80px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }
    .eval-status {
        font-size: 0.7rem;
        padding: 0.25rem 0.7rem;
        border-radius: 20px;
        font-weight: 600;
    }
    .eval-status.sudah {
        background: rgba(46, 196, 182, 0.12);
        color: #218380;
    }
    .eval-status.belum {
        background: rgba(108, 117, 125, 0.12);
        color: #6c757d;
    }
    .revisi-flag {
        font-size: 0.7rem;
        padding: 0.25rem 0.7rem;
        border-radius: 20px;
        font-weight: 600;
        background: rgba(231, 29, 54, 0.1);
        color: #e71d36;
    }
    .eval-card {
        border-left: 3px solid transparent;
        transition: all 0.2s;
    }
    .eval-card:hover {
        border-left-color: var(--primary-color);
    }
    .eval-card.has-revisi {
        border-left-color: #ff9f1c;
    }
</style>
@endsection

@section('content')
{{-- Filter Tahun --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <div class="row align-items-center g-3">
            <div class="col-auto">
                <h6 class="fw-bold mb-0"><i class="fas fa-filter me-2 text-primary"></i>Pilih Tahun Evaluasi</h6>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm bg-light border-0"
                        onchange="window.location.href='{{ route('evaluasi-tahunan.index') }}?tahun='+this.value">
                    @for($y = date('Y') - 2; $y <= date('Y') + 2; $y++)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
        </div>
    </div>
</div>

{{-- Summary --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="eval-summary bg-blue">
            <div class="small opacity-75">Total PK</div>
            <div class="fs-3 fw-bold">{{ $summary['total_pk'] }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="eval-summary bg-green">
            <div class="small opacity-75">Sudah Dievaluasi</div>
            <div class="fs-3 fw-bold">{{ $summary['sudah_evaluasi'] }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="eval-summary bg-yellow">
            <div class="small opacity-75">Belum Dievaluasi</div>
            <div class="fs-3 fw-bold">{{ $summary['belum_evaluasi'] }}</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="eval-summary bg-red">
            <div class="small opacity-75">Rekomendasi Revisi</div>
            <div class="fs-3 fw-bold">{{ $summary['rekomendasi_revisi'] }}</div>
        </div>
    </div>
</div>

{{-- Daftar Evaluasi --}}
<div class="card">
    <div class="card-header">
        <i class="fas fa-clipboard-check me-2 text-primary"></i>Evaluasi Akhir Tahun {{ $tahun }}
    </div>
    <div class="card-body p-0">
        @forelse($pkTahunans as $pk)
            @php
                $eval = $pk->evaluasiTahunan;
                $warna = $pk->capaian >= 100 ? 'success' : ($pk->capaian >= 80 ? 'warning' : 'danger');
            @endphp
            <div class="eval-card p-4 border-bottom {{ $eval && $eval->rekomendasi_revisi ? 'has-revisi' : '' }}">
                <div class="row align-items-center g-3">
                    {{-- Info IKU --}}
                    <div class="col-md-4">
                        <div class="d-flex align-items-start gap-2">
                            <div>
                                <div class="fw-bold small text-primary">{{ $pk->indikator->kode ?? '' }}</div>
                                <div class="fw-bold text-dark">{{ $pk->indikator->indikator_kinerja }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    Target: {{ number_format($pk->target_efektif, 2) }} {{ $pk->indikator->satuan }}
                                    @if($pk->status_revisi)
                                        <span class="ms-1 text-warning" style="font-size: 0.65rem;">
                                            <i class="fas fa-pen"></i> Revisi
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Capaian --}}
                    <div class="col-md-2 text-center">
                        <div class="text-muted" style="font-size: 0.7rem;">Realisasi / Capaian</div>
                        <div class="fw-bold">{{ number_format($pk->realisasi_saat_ini, 2) }}</div>
                        <span class="badge bg-{{ $warna }} bg-opacity-10 text-{{ $warna }} rounded-pill" style="font-size: 0.75rem;">
                            {{ number_format($pk->capaian, 1) }}%
                        </span>
                    </div>

                    {{-- Status Evaluasi --}}
                    <div class="col-md-2 text-center">
                        @if($eval)
                            <span class="eval-status sudah"><i class="fas fa-check me-1"></i>Sudah</span>
                            @if($eval->rekomendasi_revisi)
                                <span class="revisi-flag d-block mt-1">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Perlu Revisi
                                </span>
                            @endif
                        @else
                            <span class="eval-status belum"><i class="fas fa-clock me-1"></i>Belum</span>
                        @endif
                    </div>

                    {{-- Tombol Evaluasi --}}
                    <div class="col-md-4">
                        @if(auth()->user()->isAdminOrPimpinan())
                        <button class="btn btn-sm btn-outline-primary rounded-pill w-100"
                                data-bs-toggle="collapse" data-bs-target="#evalForm{{ $pk->id }}">
                            <i class="fas fa-{{ $eval ? 'edit' : 'plus' }} me-1"></i>
                            {{ $eval ? 'Edit Evaluasi' : 'Isi Evaluasi' }}
                        </button>
                        @endif
                    </div>
                </div>

                {{-- Form Evaluasi (Collapsible) --}}
                @if(auth()->user()->isAdminOrPimpinan())
                <div class="collapse mt-4 {{ old('pk_tahunan_id') == $pk->id ? 'show' : '' }}" id="evalForm{{ $pk->id }}">
                    <form action="{{ route('evaluasi-tahunan.store') }}" method="POST" class="bg-light rounded-4 p-4">
                        @csrf
                        <input type="hidden" name="pk_tahunan_id" value="{{ $pk->id }}">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold">Realisasi Akhir Tahun</label>
                                <input type="number" name="realisasi_akhir" class="form-control rounded-3"
                                       value="{{ $eval->realisasi_akhir ?? $pk->realisasi_saat_ini }}"
                                       step="0.01" min="0" required>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label small fw-bold">Analisis Hasil Kinerja</label>
                                <textarea name="analisis_hasil" class="form-control rounded-3" rows="3" required
                                          placeholder="Jelaskan mengapa target tercapai/tidak tercapai, faktor pendukung dan penghambat...">{{ $eval->analisis_hasil ?? '' }}</textarea>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check form-switch mt-2">
                                    <input type="hidden" name="rekomendasi_revisi" value="0">
                                    <input class="form-check-input" type="checkbox" name="rekomendasi_revisi" value="1"
                                           id="revisiCheck{{ $pk->id }}"
                                           {{ ($eval && $eval->rekomendasi_revisi) ? 'checked' : '' }}
                                           onchange="document.getElementById('catatanRevisi{{ $pk->id }}').style.display = this.checked ? 'block' : 'none'">
                                    <label class="form-check-label small fw-bold" for="revisiCheck{{ $pk->id }}">
                                        Rekomendasikan Revisi Target Tahun Depan
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-8" id="catatanRevisi{{ $pk->id }}"
                                 style="display: {{ ($eval && $eval->rekomendasi_revisi) ? 'block' : 'none' }};">
                                <label class="form-label small fw-bold">Catatan Revisi</label>
                                <textarea name="catatan_revisi" class="form-control rounded-3" rows="2"
                                          placeholder="Detail apa yang perlu direvisi di target tahun depan...">{{ $eval->catatan_revisi ?? '' }}</textarea>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                    <i class="fas fa-save me-1"></i>Simpan Evaluasi
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                @endif
            </div>
        @empty
            <div class="text-center py-5">
                <i class="fas fa-clipboard-check text-muted d-block mb-2" style="font-size: 2rem;"></i>
                <span class="text-muted">Belum ada PK Tahunan yang bisa dievaluasi untuk {{ $tahun }}.</span><br>
                <span class="text-muted small">Buat PK Tahunan terlebih dahulu melalui menu PK Tahunan.</span>
            </div>
        @endforelse
    </div>
</div>
@endsection
