@extends('layouts.dashboard')

@section('title', 'Riwayat Kendala & RTL')

@section('content')
<div class="mb-3">
    <a href="{{ route('analisis-kendala.index', ['tahun' => $tahun, 'triwulan' => $triwulan]) }}" class="btn btn-light border shadow-sm btn-sm px-3 rounded-pill text-nowrap">
        <i class="fas fa-arrow-left me-1"></i> Kembali
    </a>
</div>
<div class="d-flex justify-content-between align-items-center mb-4 gap-3">
    <div class="flex-grow-1 pe-3">
        <div class="mb-0 text-dark lh-base fs-6">
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-2 me-1 align-middle">{{ $indikator->kode }}</span>
            <span class="fw-bold align-middle">{{ $indikator->indikator_kinerja }}</span>
            <span class="text-muted align-middle ms-1">— {{ Str::limit($indikator->sasaran, 150) }}</span>
        </div>
    </div>
    <div class="flex-shrink-0">
        <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#modal-create">
            <i class="fas fa-plus me-1"></i> Catat Kendala Baru
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 text-dark mb-4">
    <div class="card-body p-0">
        @if($kendalaRtls->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0 bg-white">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 text-center" style="width: 4%;">No</th>
                            <th class="py-3" style="width: 20%;">Kendala</th>
                            <th class="py-3" style="width: 20%;">Solusi</th>
                            <th class="py-3" style="width: 21%;">Rencana Tindak Lanjut</th>
                            <th class="py-3 text-center" style="width: 13%;">PIC & Batas Waktu</th>
                            <th class="py-3 text-center" style="width: 14%;">Status</th>
                            <th class="py-3 text-center" style="width: 8%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kendalaRtls as $index => $data)
                            <tr>
                                <td class="text-center text-muted">{{ $index + 1 }}</td>
                                <td class="small" style="white-space: pre-line; vertical-align: top;">{{ $data->kendala }}</td>
                                <td class="small" style="white-space: pre-line; vertical-align: top;">{{ $data->solusi }}</td>
                                <td class="small" style="white-space: pre-line; vertical-align: top;">{{ $data->rtl }}</td>
                                <td style="vertical-align: top;">
                                    <div class="small fw-bold">{{ $data->pic->nama ?? '-' }}</div>
                                    <div class="small text-danger">{{ $data->batas_waktu ? $data->batas_waktu->format('d M Y') : '-' }}</div>
                                </td>
                                <td class="text-center" style="vertical-align: top;">
                                    @php
                                        $isOverdue = $data->batas_waktu && $data->batas_waktu->format('Y-m-d') < date('Y-m-d') && $data->status == 'Belum Ditindak Lanjut';
                                    @endphp
                                    @if($data->status == 'Sudah Ditindak Lanjut')
                                        <span class="badge bg-success px-2 py-1">Sudah Ada Tindak Lanjut</span>
                                    @elseif($isOverdue)
                                        <span class="badge bg-danger px-2 py-1">Terlambat</span>
                                    @else
                                        <span class="badge bg-primary px-2 py-1">Belum Ada Tindak Lanjut</span>
                                    @endif
                                </td>
                                <td class="text-center" style="vertical-align: top;">
                                    <div class="d-flex justify-content-center gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-edit-{{ $data->id }}" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('analisis-kendala.destroy', $data->id) }}" method="POST" class="m-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan kendala ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-folder-open fs-1 text-muted opacity-50 mb-3"></i>
                <h6 class="fw-bold text-muted">Belum ada riwayat kendala untuk triwulan ini.</h6>
            </div>
        @endif
    </div>
</div>

<!-- Modal Create -->
<div class="modal fade" id="modal-create" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form action="{{ route('analisis-kendala.store') }}" method="POST">
            @csrf
            <input type="hidden" name="indikator_id" value="{{ $indikator->id }}">
            <input type="hidden" name="tahun" value="{{ $tahun }}">
            <input type="hidden" name="triwulan" value="{{ $triwulan }}">
            
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 bg-light">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-plus-circle text-primary me-2"></i> Tambah Catatan Baru
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 bg-info bg-opacity-10 mb-4">
                        Anda menambahkan riwayat kendala baru untuk Triwulan {{ $triwulan }} Tahun {{ $tahun }}.
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Kendala yang Dihadapi <span class="text-danger">*</span></label>
                        <textarea name="kendala" class="form-control bg-light" rows="3" required placeholder="Gunakan '-' untuk bullet points jika ada banyak..."></textarea>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Solusi yang Telah Dilakukan <span class="text-danger">*</span></label>
                            <textarea name="solusi" class="form-control bg-light" rows="4" required placeholder="Gunakan '-' untuk bullet points jika ada banyak..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Rencana Tindak Lanjut (RTL) <span class="text-danger">*</span></label>
                            <textarea name="rtl" class="form-control bg-light" rows="4" required placeholder="Gunakan '-' untuk bullet points jika ada banyak..."></textarea>
                        </div>
                    </div>
                    
                    <div class="row mb-2">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">PIC Tindak Lanjut</label>
                            <select name="pic_nip" class="form-select bg-light">
                                <option value="">-- Pilih PIC --</option>
                                @foreach($pegawais as $p)
                                    <option value="{{ $p->nip }}">{{ $p->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Batas Waktu</label>
                            <input type="date" name="batas_waktu" class="form-control bg-light">
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

<!-- Modal Edit (for each record) -->
@foreach($kendalaRtls as $data)
    <div class="modal fade" id="modal-edit-{{ $data->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form action="{{ route('analisis-kendala.update', $data->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-bottom-0 bg-light">
                        <h5 class="modal-title fw-bold">
                            <i class="fas fa-edit text-primary me-2"></i> Edit Riwayat Kendala
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Kendala yang Dihadapi <span class="text-danger">*</span></label>
                            <textarea name="kendala" class="form-control bg-light" rows="3" required>{{ $data->kendala }}</textarea>
                        </div>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Solusi yang Telah Dilakukan <span class="text-danger">*</span></label>
                                <textarea name="solusi" class="form-control bg-light" rows="4" required>{{ $data->solusi }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Rencana Tindak Lanjut (RTL) <span class="text-danger">*</span></label>
                                <textarea name="rtl" class="form-control bg-light" rows="4" required>{{ $data->rtl }}</textarea>
                            </div>
                        </div>
                        
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">PIC Tindak Lanjut</label>
                                <select name="pic_nip" class="form-select bg-light">
                                    <option value="">-- Pilih PIC --</option>
                                    @foreach($pegawais as $p)
                                        <option value="{{ $p->nip }}" {{ $data->pic_nip == $p->nip ? 'selected' : '' }}>{{ $p->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Batas Waktu</label>
                                <input type="date" name="batas_waktu" class="form-control bg-light" value="{{ $data->batas_waktu ? $data->batas_waktu->format('Y-m-d') : '' }}">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 bg-light justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endforeach

@endsection
