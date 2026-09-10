@extends('layouts.dashboard')

@section('title', 'Pengisian Analisis & Kendala')

@section('content')
<div class="card mb-4 shadow-sm border-0 rounded-4">
    <div class="card-body">
        <form action="{{ route('analisis-kendala.index') }}" method="GET" class="d-flex gap-3 align-items-end flex-wrap">
            <div>
                <label class="form-label text-muted small fw-bold mb-1">Tahun</label>
                <select name="tahun" class="form-select" onchange="this.form.submit()">
                    @for($i = date('Y') - 2; $i <= date('Y') + 2; $i++)
                        <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="form-label text-muted small fw-bold mb-1">Triwulan</label>
                <select name="triwulan" class="form-select" onchange="this.form.submit()">
                    @for($i = 1; $i <= 4; $i++)
                        <option value="{{ $i }}" {{ $triwulan == $i ? 'selected' : '' }}>Triwulan {{ $i }}</option>
                    @endfor
                </select>
            </div>
            
            @if(auth()->user()->isAdmin())
            <div class="ms-auto">
                <button type="button" class="btn btn-success px-4" data-bs-toggle="modal" data-bs-target="#modal-import">
                    <i class="fas fa-file-excel me-1"></i> Import Kendala
                </button>
            </div>
            @endif
        </form>
    </div>
</div>

@if(auth()->user()->isAdmin())
<!-- Modal Import -->
<div class="modal fade" id="modal-import" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('analisis-kendala.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 bg-light">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-file-excel text-success me-2"></i> Import Data Kendala
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 bg-info bg-opacity-10 mb-4 small">
                        Pastikan file Anda menggunakan format <strong>Template_Import_Capaian_Kinerja.xlsx</strong> yang valid. Sistem akan menarik data dari kolom <em>Kendala yg Dihadapi</em> hingga <em>Batas Waktu TL</em>.
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold">Tahun</label>
                            <input type="number" name="tahun" class="form-control bg-light" value="{{ $tahun }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold">Triwulan</label>
                            <select name="triwulan" class="form-select bg-light" required>
                                @for($i = 1; $i <= 4; $i++)
                                    <option value="{{ $i }}" {{ $triwulan == $i ? 'selected' : '' }}>Triwulan {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih File Excel <span class="text-danger">*</span></label>
                        <input type="file" name="file_import" class="form-control bg-light" accept=".xlsx,.xls,.csv" required>
                    </div>
                </div>
                <div class="modal-footer border-top-0 bg-light">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="fas fa-upload me-1"></i> Mulai Import
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

<div class="card border-0 shadow-sm rounded-4 text-dark">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="40" class="text-center py-3">No</th>
                        <th width="100" class="py-3">Kode</th>
                        <th style="min-width: 250px;" class="py-3">Indikator Kinerja</th>
                        <th class="py-3 text-center text-nowrap">Riwayat Catatan</th>
                        <th width="180" class="text-center pe-3 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($indikators as $index => $indikator)
                        @php
                            $dataList = $kendalaRtls[$indikator->id] ?? collect();
                        @endphp
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-2">{{ $indikator->kode }}</span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark small">{{ $indikator->indikator_kinerja }}</div>
                                <div class="text-muted small">{{ $indikator->sasaran }}</div>
                            </td>
                            <td class="text-center text-nowrap">
                                @if($dataList->count() > 0)
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-3 py-2 fs-6">
                                        {{ $dataList->count() }}
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted border px-3 py-2">
                                        0
                                    </span>
                                @endif
                            </td>
                            <td class="text-center pe-3">
                                <a href="{{ route('analisis-kendala.show', ['id' => $indikator->id, 'tahun' => $tahun, 'triwulan' => $triwulan]) }}" class="btn btn-sm {{ $dataList->count() > 0 ? 'btn-outline-primary' : 'btn-primary' }}">
                                    <i class="fas {{ $dataList->count() > 0 ? 'fa-folder-open' : 'fa-plus' }} me-1"></i>
                                    {{ $dataList->count() > 0 ? 'Lihat / Kelola' : 'Tambah Catatan' }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fs-2 mb-3"></i>
                                    <p class="mb-0">Tidak ada indikator yang tersedia untuk triwulan ini.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>



@endsection
