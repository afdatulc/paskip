@extends('layouts.dashboard')

@section('title', 'Pelaksanaan Rencana Tindak Lanjut (Baru)')

@section('content')
<div class="card mb-4 shadow-sm border-0">
    <div class="card-body">
        <form action="{{ route('pelaksanaan-rtl-baru.index') }}" method="GET" class="d-flex gap-3 align-items-end flex-wrap">
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
            <div>
                <label class="form-label text-muted small fw-bold mb-1">Status</label>
                <select name="status" class="form-select">
                    <option value="Semua" {{ $status == 'Semua' ? 'selected' : '' }}>Semua Status</option>
                    <option value="Belum Ditindak Lanjut" {{ $status == 'Belum Ditindak Lanjut' ? 'selected' : '' }}>Belum Ditindak Lanjut</option>
                    <option value="Sudah Ditindak Lanjut" {{ $status == 'Sudah Ditindak Lanjut' ? 'selected' : '' }}>Sudah Ditindak Lanjut</option>
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
                        <th class="py-3">Rencana Tindak Lanjut</th>
                        <th class="py-3">Batas Waktu</th>
                        <th class="py-3">Status</th>
                        <th class="text-end pe-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rtls as $index => $rtl)
                        <tr>
                            <td class="ps-4 text-muted">{{ $index + 1 }}</td>
                            <td>
                                <div class="mb-1"><span class="badge bg-secondary">{{ $rtl->indikator->kode }}</span></div>
                                <div class="text-wrap" style="max-width: 250px;">{{ $rtl->indikator->nama }}</div>
                            </td>
                            <td>
                                <div class="text-wrap" style="max-width: 300px;">{{ Str::limit($rtl->rtl, 100) }}</div>
                                @if($rtl->pic)
                                    <div class="mt-2 small text-muted"><i class="fas fa-user-circle me-1"></i> PIC: {{ $rtl->pic->nama }}</div>
                                @endif
                            </td>
                            <td>
                                @if($rtl->batas_waktu)
                                    {{ $rtl->batas_waktu->format('d M Y') }}
                                @else
                                    <span class="text-muted fst-italic">Tidak ditentukan</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $rtl->status == 'Sudah Ditindak Lanjut' ? 'bg-success' : 'bg-warning text-dark' }} bg-opacity-75">
                                    <i class="fas {{ $rtl->status == 'Sudah Ditindak Lanjut' ? 'fa-check-circle' : 'fa-clock' }} me-1"></i> 
                                    {{ $rtl->status }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                @if($rtl->status == 'Belum Ditindak Lanjut')
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal-aksi-{{ $rtl->id }}">
                                        <i class="fas fa-bolt me-1"></i> Aksi RTL
                                    </button>
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#modal-detail-{{ $rtl->id }}">
                                        <i class="fas fa-eye me-1"></i> Lihat Bukti
                                    </button>
                                @endif
                            </td>
                        </tr>

                        @if($rtl->status == 'Belum Ditindak Lanjut')
                        <!-- Modal Aksi RTL -->
                        <div class="modal fade" id="modal-aksi-{{ $rtl->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                <form action="{{ route('pelaksanaan-rtl-baru.store', $rtl->id) }}" method="POST" class="form-ajax" enctype="multipart/form-data">
                                    @csrf
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header border-bottom-0 bg-light">
                                            <h5 class="modal-title fw-bold">
                                                <i class="fas fa-bolt text-primary me-2"></i> Bukti Tindak Lanjut RTL
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="alert alert-warning border-0 bg-warning bg-opacity-10">
                                                <h6 class="fw-bold mb-1">Rencana Tindak Lanjut:</h6>
                                                <p class="mb-0">{{ $rtl->rtl }}</p>
                                            </div>

                                            <div class="mb-4">
                                                <label class="form-label fw-bold">Narasi Penjelasan Pelaksanaan <span class="text-danger">*</span></label>
                                                <textarea name="narasi_tindak_lanjut" class="form-control bg-light" rows="4" required placeholder="Ceritakan progres atau pelaksanaan dari RTL ini..."></textarea>
                                            </div>
                                            
                                            <div class="mb-4">
                                                <label class="form-label fw-bold">Tanggal Pelaksanaan <span class="text-danger">*</span></label>
                                                <input type="date" name="tanggal_pelaksanaan" class="form-control bg-light w-50" required>
                                                <div class="form-text text-danger">Penting: Tanggal pelaksanaan harus berada dalam periode kegiatan (Triwulan {{ $rtl->triwulan }} Tahun {{ $rtl->tahun }}).</div>
                                            </div>

                                            <div class="mb-4">
                                                <label class="form-label fw-bold">Foto Bukti Tindak Lanjut (Opsional)</label>
                                                <input type="file" name="foto_bukti" class="form-control bg-light" accept="image/*">
                                                <div class="form-text">Maksimal ukuran file: 5MB. Format yang didukung: JPG, PNG.</div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Konfirmasi Dokumentasi <span class="text-danger">*</span></label>
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" name="is_dokumentasi_ada" value="1" id="check-dok-{{ $rtl->id }}" required>
                                                    <label class="form-check-label" for="check-dok-{{ $rtl->id }}">
                                                        Apakah sudah tersedia narasi dokumentasi?
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="is_timestamp_ada" value="1" id="check-time-{{ $rtl->id }}" required>
                                                    <label class="form-check-label" for="check-time-{{ $rtl->id }}">
                                                        Jika tersedia dokumentasi, apakah sudah terdapat keterangan waktu/timestamp yang jelas?
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="alert alert-danger d-none error-message-container"></div>
                                        </div>
                                        <div class="modal-footer border-top-0 bg-light">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary px-4">
                                                <i class="fas fa-save me-1"></i> Simpan Bukti
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @else
                        <!-- Modal Detail Bukti -->
                        <div class="modal fade" id="modal-detail-{{ $rtl->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content border-0 shadow">
                                    <div class="modal-header border-bottom-0 bg-light">
                                        <h5 class="modal-title fw-bold">
                                            <i class="fas fa-info-circle text-success me-2"></i> Detail Bukti Tindak Lanjut
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body p-4">
                                        <div class="alert alert-success border-0 bg-success bg-opacity-10 mb-4">
                                            <h6 class="fw-bold mb-1">Rencana Tindak Lanjut:</h6>
                                            <p class="mb-0">{{ $rtl->rtl }}</p>
                                        </div>
                                        @foreach($rtl->executions as $exec)
                                            <div class="border rounded p-3 bg-light mb-3">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <div class="fw-bold"><i class="fas fa-calendar-alt text-primary me-2"></i> {{ $exec->tanggal_pelaksanaan->format('d F Y') }}</div>
                                                    <span class="badge bg-secondary">{{ $exec->pegawai->nama ?? 'Unknown' }}</span>
                                                </div>
                                                <p class="mb-2">{{ $exec->narasi_tindak_lanjut }}</p>
                                                
                                                @if($exec->foto_bukti)
                                                    <div class="mb-3">
                                                        <a href="{{ Storage::url($exec->foto_bukti) }}" target="_blank">
                                                            <img src="{{ Storage::url($exec->foto_bukti) }}" alt="Foto Bukti" class="img-thumbnail" style="max-height: 200px; width: auto;">
                                                        </a>
                                                    </div>
                                                @endif

                                                <div class="d-flex gap-2">
                                                    @if($exec->is_dokumentasi_ada)
                                                        <span class="badge bg-success bg-opacity-75"><i class="fas fa-check me-1"></i> Dokumentasi Terkonfirmasi</span>
                                                    @endif
                                                    @if($exec->is_timestamp_ada)
                                                        <span class="badge bg-success bg-opacity-75"><i class="fas fa-check me-1"></i> Timestamp Terkonfirmasi</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="modal-footer border-top-0 bg-light">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-clipboard-check fs-2 mb-3"></i>
                                    <p class="mb-0">Tidak ada Rencana Tindak Lanjut (RTL) yang sesuai dengan filter.</p>
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

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('.form-ajax');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const errorContainer = this.querySelector('.error-message-container');
            const submitBtn = this.querySelector('button[type="submit"]');
            
            errorContainer.classList.add('d-none');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...';

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.reload();
                } else if (data.status === 'error') {
                    errorContainer.textContent = data.message;
                    errorContainer.classList.remove('d-none');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-save me-1"></i> Simpan Bukti';
                }
            })
            .catch(error => {
                errorContainer.textContent = 'Terjadi kesalahan pada sistem.';
                errorContainer.classList.remove('d-none');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save me-1"></i> Simpan Bukti';
            });
        });
    });
});
</script>
@endsection
