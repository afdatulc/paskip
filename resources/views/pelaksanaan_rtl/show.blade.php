@extends('layouts.dashboard')

@section('title', 'Daftar Rencana Tindak Lanjut')

@section('content')
<div class="mb-3">
    <a href="{{ route('pelaksanaan-rtl.index', ['tahun' => $tahun, 'triwulan' => $triwulan]) }}" class="btn btn-light border shadow-sm btn-sm px-3 rounded-pill text-nowrap">
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
</div>

<div class="alert alert-info border-0 shadow-sm rounded-4 small mb-4">
    <i class="fas fa-info-circle me-1"></i> Menampilkan daftar RTL yang disusun pada <strong>Triwulan {{ $target_triwulan }} Tahun {{ $target_tahun }}</strong> untuk dieksekusi pada <strong>Triwulan {{ $triwulan }} Tahun {{ $tahun }}</strong>.
</div>

<div class="card border-0 shadow-sm rounded-4 text-dark mb-4">
    <div class="card-body p-0">
        @if($rtls->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4 py-3">Rencana Tindak Lanjut</th>
                            <th class="py-3 text-center">Status</th>
                            <th class="text-center pe-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $combinedRtlText = '';
                            $allExecutions = collect();
                            $firstRtl = $rtls->first();
                            
                            foreach($rtls as $r) {
                                $text = trim($r->rtl);
                                $textLines = explode("\n", $text);
                                foreach($textLines as $line) {
                                    $line = trim($line);
                                    if(empty($line)) continue;
                                    if(!str_starts_with($line, '-')) {
                                        $line = '- ' . ltrim($line, '- ');
                                    }
                                    $combinedRtlText .= $line . "\n";
                                }
                                
                                foreach($r->executions as $ex) {
                                    $allExecutions->push($ex);
                                }
                            }
                            $allExecutions = $allExecutions->sortByDesc('created_at');
                            $hasExecutions = $allExecutions->count() > 0;
                            $isOverdue = $firstRtl->batas_waktu && $firstRtl->batas_waktu->format('Y-m-d') < date('Y-m-d') && !$hasExecutions;
                        @endphp
                        <tr>
                            <td class="ps-4 py-3" style="max-width: 450px;">
                                <div class="text-wrap">{!! nl2br(e(trim($combinedRtlText))) !!}</div>
                            </td>
                            <td class="py-3 text-center">
                                @if($hasExecutions)
                                    <span class="badge bg-success bg-opacity-75"><i class="fas fa-check-circle me-1"></i> Sudah Ada Tindak Lanjut</span>
                                @elseif($isOverdue)
                                    <span class="badge bg-danger bg-opacity-75"><i class="fas fa-clock me-1"></i> Terlambat</span>
                                @else
                                    <span class="badge bg-primary bg-opacity-75"><i class="fas fa-clock me-1"></i> Belum Ada Tindak Lanjut</span>
                                @endif
                                @if($hasExecutions)
                                    <div class="mt-1 small text-muted">{{ $allExecutions->count() }} Bukti Diunggah</div>
                                @endif
                            </td>
                            <td class="text-center pe-4 py-3">
                                <div class="d-flex gap-2 justify-content-center">
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal-aksi-{{ $firstRtl->id }}">
                                        <i class="fas {{ $hasExecutions ? 'fa-plus' : 'fa-bolt' }} me-1"></i> {{ $hasExecutions ? 'Tambah Bukti' : 'Aksi RTL' }}
                                    </button>
                                    @if($hasExecutions)
                                        <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#modal-detail-{{ $firstRtl->id }}">
                                            <i class="fas fa-eye me-1"></i> Lihat Bukti
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-clipboard-check fs-1 text-muted opacity-50 mb-3"></i>
                <h6 class="fw-bold text-muted">Tidak ada Rencana Tindak Lanjut (RTL) yang ditemukan.</h6>
            </div>
        @endif
    </div>
</div>

<!-- Modals -->
@if($rtls->count() > 0)
    @php
        // Prepare data for modals from grouped RTLs
        $combinedRtlTextForModal = '';
        $allExecutions = collect();
        $firstRtl = $rtls->first();
        
        foreach($rtls as $r) {
            $text = trim($r->rtl);
            $textLines = explode("\n", $text);
            foreach($textLines as $line) {
                $line = trim($line);
                if(empty($line)) continue;
                if(!str_starts_with($line, '-')) {
                    $line = '- ' . ltrim($line, '- ');
                }
                $combinedRtlTextForModal .= $line . "\n";
            }
            
            foreach($r->executions as $ex) {
                $allExecutions->push($ex);
            }
        }
        $allExecutions = $allExecutions->sortByDesc('created_at');
    @endphp

    <!-- Modal Aksi RTL -->
    <div class="modal fade" id="modal-aksi-{{ $firstRtl->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form action="{{ route('pelaksanaan-rtl.store', $firstRtl->id) }}" method="POST" class="form-ajax" enctype="multipart/form-data">
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
                            <p class="mb-0">{!! nl2br(e(trim($combinedRtlTextForModal))) !!}</p>
                        </div>

                        <div class="mb-4">
                            <h6 class="fw-bold mb-2">1. Unduh Template Word</h6>
                            <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded border">
                                <p class="mb-0 text-muted small">Unduh template bukti tindak lanjut, kemudian isi sesuai dengan RTL.</p>
                                <a href="{{ route('template.word.download.rtl', $firstRtl->id) }}" class="btn btn-outline-primary btn-sm text-nowrap">
                                    <i class="fas fa-download me-1"></i> Unduh Template
                                </a>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h6 class="fw-bold mb-2">2. Unggah Bukti Tindak Lanjut</h6>
                            <div class="p-4 rounded border bg-light text-center" style="border-style: dashed !important;">
                                <i class="fas fa-cloud-upload-alt fs-1 text-muted mb-2"></i>
                                <div class="mb-3">
                                    <input type="file" name="foto_bukti" id="foto_bukti_{{ $firstRtl->id }}" class="form-control" accept=".doc,.docx" required>
                                </div>
                                <p class="text-muted small mb-0">Format: DOC, DOCX (Max 10MB)</p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <h6 class="fw-bold mb-3">3. Konfirmasi Kelengkapan</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="is_dokumentasi_ada" value="1" id="check_doc_{{ $firstRtl->id }}">
                                <label class="form-check-label text-dark" for="check_doc_{{ $firstRtl->id }}">
                                    Di dalam file sudah terdapat Dokumentasi Tindak Lanjut
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_timestamp_ada" value="1" id="check_time_{{ $firstRtl->id }}">
                                <label class="form-check-label text-dark" for="check_time_{{ $firstRtl->id }}">
                                    Di dalam dokumentasi sudah terdapat Timestamp (Waktu dan Lokasi)
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

    <!-- Modal Lihat Bukti -->
    <div class="modal fade" id="modal-detail-{{ $firstRtl->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom-0 bg-light">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-eye text-success me-2"></i> Riwayat Bukti Tindak Lanjut
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="p-4 bg-white">
                        <div class="alert alert-info border-0 bg-info bg-opacity-10 mb-4">
                            <h6 class="fw-bold mb-1">Rencana Tindak Lanjut (Digabungkan):</h6>
                            <p class="mb-0">{!! nl2br(e(trim($combinedRtlTextForModal))) !!}</p>
                        </div>

                        <h6 class="fw-bold mb-3">Daftar Bukti Diunggah:</h6>
                        <div class="timeline-container">
                            @forelse($allExecutions as $index => $exec)
                                <div class="card border mb-4 shadow-sm" style="border-width: 2px !important;">
                                    <div class="card-header bg-light border-bottom d-flex justify-content-between align-items-center py-2">
                                        <div>
                                            <h6 class="mb-1 fw-bold text-dark"><i class="fas fa-file-word text-primary me-2"></i> Bukti Dukung #{{ $allExecutions->count() - $index }}</h6>
                                            <small class="text-muted"><i class="far fa-calendar-alt me-1"></i> Diunggah pada {{ \Carbon\Carbon::parse($exec->tanggal_pelaksanaan)->format('d M Y') }}</small>
                                        </div>
                                        <form action="{{ route('pelaksanaan-rtl.destroy-bukti', $exec->id) }}" method="POST" class="m-0" onsubmit="return confirm('Apakah Anda yakin ingin menghapus bukti ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex gap-2 mb-3">
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-3 py-2">
                                                <i class="fas fa-check-circle me-1"></i> Dokumentasi Tersedia
                                            </span>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-3 py-2">
                                                <i class="fas fa-check-circle me-1"></i> Timestamp Valid
                                            </span>
                                        </div>
                                        <div class="d-flex gap-2 pt-2 border-top">
                                            <button type="button" class="btn btn-outline-info flex-grow-1 btn-preview-doc fw-bold" data-url="{{ Storage::url($exec->foto_bukti) }}">
                                                <i class="fas fa-eye me-1"></i> Buka Preview Dokumen
                                            </button>
                                            <a href="{{ Storage::url($exec->foto_bukti) }}" target="_blank" class="btn btn-primary flex-grow-1 fw-bold">
                                                <i class="fas fa-download me-1"></i> Unduh File
                                            </a>
                                        </div>
                                        
                                        <div class="doc-preview-container mt-4 p-4 rounded bg-white d-none shadow-sm" style="max-height: 500px; overflow-y: auto; font-family: 'Times New Roman', serif; font-size: 14px; border: 1px solid #17a2b8; border-left: 5px solid #17a2b8;">
                                            <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                                                <h6 class="fw-bold mb-0 text-info"><i class="fas fa-search-plus me-2"></i> Tampilan Preview Dokumen</h6>
                                                <span class="badge bg-info text-white">Preview Mode</span>
                                            </div>
                                            <div class="text-center text-muted loading-indicator d-none py-4">
                                                <i class="fas fa-spinner fa-spin fa-3x mb-3 text-info"></i>
                                                <p class="mb-0 fw-bold">Memuat isi dokumen...</p>
                                            </div>
                                            <div class="preview-content bg-light p-3 rounded border"></div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-file-excel fs-1 mb-2 opacity-50"></i>
                                    <p class="mb-0">Belum ada bukti yang diunggah.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endif

@endsection

@section('scripts')
<!-- Mammoth.js for docx preview -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.4.21/mammoth.browser.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('.form-ajax');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!this.checkValidity()) {
                this.reportValidity();
                return;
            }
            
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
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(async response => {
                if (!response.ok) {
                    if (response.status === 422) {
                        const data = await response.json();
                        let errorMsg = data.message || 'Data tidak valid.';
                        if (data.errors) {
                            errorMsg = Object.values(data.errors).flat().join('<br>');
                        }
                        throw new Error(errorMsg);
                    }
                    throw new Error('Terjadi kesalahan pada server');
                }
                return response.json();
            })
            .then(data => {
                window.location.reload();
            })
            .catch(error => {
                errorContainer.innerHTML = `<i class="fas fa-exclamation-circle me-1"></i> ${error.message}`;
                errorContainer.classList.remove('d-none');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save me-1"></i> Simpan Bukti';
            });
        });
    });

    document.querySelectorAll('.btn-preview-doc').forEach(btn => {
        btn.addEventListener('click', function() {
            const container = this.closest('.card-body').querySelector('.doc-preview-container');
            const loadingIndicator = container.querySelector('.loading-indicator');
            const previewContent = container.querySelector('.preview-content');
            const docUrl = this.dataset.url;

            if (container.classList.contains('d-none')) {
                container.classList.remove('d-none');
                
                if (previewContent.innerHTML === '') {
                    loadingIndicator.classList.remove('d-none');
                    
                    fetch(docUrl)
                        .then(response => response.arrayBuffer())
                        .then(arrayBuffer => {
                            mammoth.convertToHtml({arrayBuffer: arrayBuffer})
                                .then(displayResult)
                                .catch(handleError);
                        })
                        .catch(handleError);
                }
            } else {
                container.classList.add('d-none');
            }

            function displayResult(result) {
                loadingIndicator.classList.add('d-none');
                previewContent.innerHTML = result.value;
            }

            function handleError(err) {
                loadingIndicator.classList.add('d-none');
                previewContent.innerHTML = '<div class="alert alert-danger mb-0">Gagal memuat pratinjau dokumen. Silakan unduh dokumen untuk melihat isinya.</div>';
                console.error(err);
            }
        });
    });
});
</script>
@endsection
