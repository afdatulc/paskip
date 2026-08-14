@extends('layouts.dashboard')

@section('title', 'Pengisian Analisis & Kendala (Baru)')

@section('content')
<div class="card mb-4 shadow-sm border-0">
    <div class="card-body">
        <form action="{{ route('analisis-kendala-baru.index') }}" method="GET" class="d-flex gap-3 align-items-end flex-wrap">
            <div>
                <label class="form-label text-muted small fw-bold mb-1">Tahun</label>
                <select name="tahun" class="form-select">
                    @for($i = date('Y') - 2; $i <= date('Y') + 2; $i++)
                        <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="form-label text-muted small fw-bold mb-1">Triwulan</label>
                <select name="triwulan" class="form-select">
                    @for($i = 1; $i <= 4; $i++)
                        <option value="{{ $i }}" {{ $triwulan == $i ? 'selected' : '' }}>Triwulan {{ $i }}</option>
                    @endfor
                </select>
            </div>
            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-filter me-1"></i> Filter
            </button>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3">No</th>
                        <th class="py-3">Indikator</th>
                        <th class="py-3" style="min-width: 300px;">Detail Analisis</th>
                        <th class="text-end pe-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($indikators as $index => $indikator)
                        @php
                            $data = $kendalaRtls[$indikator->id] ?? null;
                        @endphp
                        <tr>
                            <td class="ps-4 text-muted">{{ $index + 1 }}</td>
                            <td>
                                <div class="mb-1"><span class="badge bg-secondary">{{ $indikator->kode }}</span></div>
                                <div class="text-wrap" style="max-width: 300px;">{{ $indikator->nama }}</div>
                            </td>
                            <td>
                                @if($data)
                                    <div class="d-flex flex-column gap-2 my-2">
                                        <div>
                                            <span class="badge bg-light text-dark border me-2">Kendala</span>
                                            <span class="text-muted">{{ Str::limit($data->kendala, 100) }}</span>
                                        </div>
                                        <div>
                                            <span class="badge bg-light text-dark border me-2">Solusi</span>
                                            <span class="text-muted">{{ Str::limit($data->solusi, 100) }}</span>
                                        </div>
                                        <div>
                                            <span class="badge bg-light text-dark border me-2">RTL</span>
                                            <span class="text-muted">{{ Str::limit($data->rtl, 100) }}</span>
                                        </div>
                                        <div>
                                            <span class="badge {{ $data->status == 'Sudah Ditindak Lanjut' ? 'bg-success' : 'bg-warning text-dark' }} bg-opacity-75">
                                                <i class="fas {{ $data->status == 'Sudah Ditindak Lanjut' ? 'fa-check-circle' : 'fa-clock' }} me-1"></i> 
                                                {{ $data->status }}
                                            </span>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-muted fst-italic py-2"><i class="fas fa-info-circle me-1"></i> Belum ada data</div>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <button type="button" class="btn btn-sm {{ $data ? 'btn-outline-primary' : 'btn-primary' }}" data-bs-toggle="modal" data-bs-target="#modal-{{ $indikator->id }}">
                                    <i class="fas {{ $data ? 'fa-edit' : 'fa-plus' }} me-1"></i>
                                    {{ $data ? 'Edit' : 'Isi Form' }}
                                </button>
                            </td>
                        </tr>

                        <!-- Modal -->
                        <div class="modal fade" id="modal-{{ $indikator->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                <form action="{{ route('analisis-kendala-baru.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="indikator_id" value="{{ $indikator->id }}">
                                    <input type="hidden" name="tahun" value="{{ $tahun }}">
                                    <input type="hidden" name="triwulan" value="{{ $triwulan }}">
                                    
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header border-bottom-0 bg-light">
                                            <h5 class="modal-title fw-bold">
                                                <i class="fas fa-pen-to-square text-primary me-2"></i> Form Analisis & Kendala
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="alert alert-info border-0 bg-info bg-opacity-10 d-flex gap-3">
                                                <i class="fas fa-info-circle text-info mt-1 fs-5"></i>
                                                <div>
                                                    <strong>{{ $indikator->kode }}</strong><br>
                                                    {{ $indikator->nama }}
                                                </div>
                                            </div>

                                            <div class="mb-4">
                                                <label class="form-label fw-bold">Kendala yang Dihadapi <span class="text-danger">*</span></label>
                                                <textarea name="kendala" class="form-control bg-light" rows="3" required placeholder="Jelaskan kendala yang dialami pada triwulan ini...">{{ $data->kendala ?? '' }}</textarea>
                                            </div>
                                            <div class="mb-4">
                                                <label class="form-label fw-bold">Solusi yang Telah Dilakukan <span class="text-danger">*</span></label>
                                                <textarea name="solusi" class="form-control bg-light" rows="3" required placeholder="Langkah apa yang sudah diambil untuk mengatasi kendala tersebut?">{{ $data->solusi ?? '' }}</textarea>
                                            </div>
                                            <div class="mb-4">
                                                <label class="form-label fw-bold">Rencana Tindak Lanjut (RTL) <span class="text-danger">*</span></label>
                                                <textarea name="rtl" class="form-control bg-light" rows="3" required placeholder="Apa rencana tindak lanjut ke depannya?">{{ $data->rtl ?? '' }}</textarea>
                                            </div>
                                            
                                            <div class="row mb-2">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">PIC Tindak Lanjut (Opsional)</label>
                                                    <select name="pic_nip" class="form-select bg-light">
                                                        <option value="">-- Pilih PIC --</option>
                                                        @foreach($pegawais as $p)
                                                            <option value="{{ $p->nip }}" {{ ($data->pic_nip ?? '') == $p->nip ? 'selected' : '' }}>{{ $p->nama }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold">Batas Waktu (Opsional)</label>
                                                    <input type="date" name="batas_waktu" class="form-control bg-light" value="{{ $data && $data->batas_waktu ? $data->batas_waktu->format('Y-m-d') : '' }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top-0 bg-light">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary px-4">
                                                <i class="fas fa-save me-1"></i> Simpan
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
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
