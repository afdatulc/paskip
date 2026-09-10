@extends('layouts.dashboard')

@section('title', 'Capaian Kinerja')

@section('content')
    <style>
        .active-step { background-color: #0d6efd; }
        .inactive-step { background-color: #e9ecef; color: #6c757d !important; }
    </style>
    <!-- Filter Card -->
    <div class="card mb-4 shadow-sm border-0 rounded-4">
        <div class="card-body">
            <form action="{{ route('capaian-kinerja.index') }}" method="GET" class="d-flex gap-3 align-items-end flex-wrap">
                <div>
                    <label class="form-label text-muted small fw-bold mb-1">Tahun</label>
                <select name="tahun" class="form-select" onchange="this.form.submit()">
                    @php $currentYear = date('Y'); @endphp
                    @for($i = $currentYear - 2; $i <= $currentYear + 1; $i++)
                        <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="form-label text-muted small fw-bold mb-1">Triwulan</label>
                <select name="triwulan" class="form-select" onchange="this.form.submit()">
                    <option value="1" {{ $triwulan == 1 ? 'selected' : '' }}>Triwulan 1</option>
                    <option value="2" {{ $triwulan == 2 ? 'selected' : '' }}>Triwulan 2</option>
                    <option value="3" {{ $triwulan == 3 ? 'selected' : '' }}>Triwulan 3</option>
                    <option value="4" {{ $triwulan == 4 ? 'selected' : '' }}>Triwulan 4</option>
                </select>
            </div>
                
                <div class="ms-auto d-flex gap-2">
                    <button type="button" class="btn btn-success px-4" data-bs-toggle="modal" data-bs-target="#modalImport">
                        <i class="fas fa-upload me-1"></i> Import Capaian
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm rounded-4 text-dark">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="capaianTable">
                    <thead class="table-light">
                        <tr>
                            <th width="40" class="text-center py-3">No</th>
                            <th width="100" class="py-3">Kode</th>
                            <th style="min-width: 250px;" class="py-3">Indikator Kinerja</th>
                            <th class="py-3 text-center text-nowrap">Persetujuan</th>
                            <th width="180" class="text-center pe-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($indikators as $ind)
                            @php 
                                $capaian = $capaians->get($ind->id);
                                $realisasi = $ind->realisasis->first();
                                $targetField = 'target_tw' . $triwulan;
                                $targetVal = $ind->target ? $ind->target->$targetField : '-';
                                $targetYField = 'target_y_tw' . $triwulan;
                                $targetYVal = $ind->target ? $ind->target->$targetYField : '';
                                $hasRealisasi = $realisasi && $realisasi->realisasi_kumulatif !== null && $realisasi->realisasi_kumulatif !== '';
                            @endphp
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-2">{{ $ind->kode }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark small">{{ $ind->indikator_kinerja }}</div>
                                    <div class="text-muted small">{{ $ind->sasaran }}</div>
                                </td>
                                <td class="text-center text-nowrap">
                                    @php
                                        $statusApproval = $capaian->status_approval ?? 'Menunggu';
                                        $badgeClass = $statusApproval == 'Disetujui' ? 'success' : ($statusApproval == 'Ditolak' ? 'danger' : 'warning');
                                    @endphp
                                    <span class="badge bg-{{ $badgeClass }} bg-opacity-10 text-{{ $badgeClass }} border border-{{ $badgeClass }}-subtle rounded-pill">{{ $statusApproval }}</span>
                                    @if($statusApproval == 'Ditolak' && $capaian->catatan_pimpinan)
                                        <div class="text-danger extra-small mt-1 fst-italic">Catatan: {{ $capaian->catatan_pimpinan }}</div>
                                    @endif
                                </td>
                                <td class="text-center pe-3">
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ route('capaian-kinerja.edit', ['indikator' => $ind->kode, 'tahun' => $tahun, 'triwulan' => $triwulan]) }}" class="btn btn-sm btn-outline-primary rounded-3 px-2 shadow-sm" data-bs-toggle="tooltip" title="Isi Capaian">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        @if($capaian)
                                            <button class="btn btn-sm btn-outline-secondary rounded-3 px-2 shadow-sm"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalHistory{{ $capaian->id }}"
                                                title="Riwayat Perbaikan">
                                                <i class="fas fa-history"></i>
                                            </button>
                                        @else
                                            <button class="btn btn-sm btn-outline-secondary rounded-3 px-2 shadow-sm"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalHistoryEmpty"
                                                title="Riwayat Perbaikan">
                                                <i class="fas fa-history"></i>
                                            </button>
                                        @endif

                                        @if(auth()->user()->isAdminOrPimpinan() && $capaian)
                                            <button class="btn btn-sm btn-outline-info rounded-3 px-2 btn-acc shadow-sm" 
                                                data-id="{{ $capaian->id }}"
                                                data-status="{{ $statusApproval }}"
                                                data-catatan="{{ $capaian->catatan_pimpinan ?? '' }}"
                                                data-bs-toggle="tooltip" title="Persetujuan (ACC)">
                                                <i class="fas fa-check-double"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                    Tidak ada indikator kinerja yang tersedia.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Form Capaian -->
    <div class="modal fade" id="modalCapaian" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <form id="formCapaian" class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold">Isi Capaian Kinerja</h5>
                        <div class="text-muted small">Tahun <span id="modal-tahun" class="fw-bold text-dark">{{ $tahun }}</span>, Triwulan <span id="modal-triwulan" class="fw-bold text-dark">{{ $triwulan }}</span></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                @csrf
                    <div class="modal-body p-4">
                        <input type="hidden" name="indikator_id" id="indikator_id">
                        <input type="hidden" name="tahun" value="{{ $tahun }}">
                        <input type="hidden" name="triwulan" value="{{ $triwulan }}">

                        <div class="mb-4 bg-light p-3 rounded-3 border border-light-subtle">
                            <span class="badge bg-primary mb-2" id="modal-kode-indikator"></span>
                            <div class="fw-bold small text-dark" id="modal-nama-indikator"></div>
                            <div class="mt-2 text-muted small d-flex justify-content-between border-top pt-2">
                                <div>Satuan: <span id="modal-satuan" class="fw-bold text-dark"></span></div>
                                <div>Target TW: <span id="modal-target" class="fw-bold text-primary"></span></div>
                            </div>
                        </div>

                        <div class="wizard-header mb-4 px-3 position-relative mt-4">
                            <div class="progress" style="height: 3px; position: absolute; top: 15px; left: 10%; right: 10%; z-index: 1;">
                                <div class="progress-bar bg-primary" role="progressbar" id="wizardProgressCapaian" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            
                            <div class="d-flex justify-content-between position-relative" style="z-index: 2;">
                                <!-- Step 1 -->
                                <div class="text-center step-capaian-clickable" data-step="1" style="width: 25%; cursor: pointer;">
                                    <div id="step1-capaian-circle" class="rounded-circle d-inline-flex align-items-center justify-content-center text-white mb-2 active-step" style="width: 32px; height: 32px; font-weight: 600;">
                                        1
                                    </div>
                                    <div id="step1-capaian-label" class="small fw-bold text-primary">Realisasi</div>
                                </div>
                                <!-- Step 2 -->
                                <div class="text-center step-capaian-clickable" data-step="2" style="width: 25%; cursor: pointer;">
                                    <div id="step2-capaian-circle" class="rounded-circle d-inline-flex align-items-center justify-content-center text-white mb-2 inactive-step" style="width: 32px; height: 32px; font-weight: 600;">
                                        2
                                    </div>
                                    <div id="step2-capaian-label" class="small fw-bold text-muted">Dasar Hitung</div>
                                </div>
                                <!-- Step 3 -->
                                <div class="text-center step-capaian-clickable" data-step="3" style="width: 25%; cursor: pointer;">
                                    <div id="step3-capaian-circle" class="rounded-circle d-inline-flex align-items-center justify-content-center text-white mb-2 inactive-step" style="width: 32px; height: 32px; font-weight: 600;">
                                        3
                                    </div>
                                    <div id="step3-capaian-label" class="small fw-bold text-muted">Argumen Logis</div>
                                </div>
                                <!-- Step 4 -->
                                <div class="text-center step-capaian-clickable" data-step="4" style="width: 25%; cursor: pointer;">
                                    <div id="step4-capaian-circle" class="rounded-circle d-inline-flex align-items-center justify-content-center text-white mb-2 inactive-step" style="width: 32px; height: 32px; font-weight: 600;">
                                        4
                                    </div>
                                    <div id="step4-capaian-label" class="small fw-bold text-muted">Lainnya</div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 1 Content -->
                        <div id="step1-capaian-content" class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-bold small">Realisasi Kumulatif</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" name="realisasi_kumulatif" id="realisasi_kumulatif" class="form-control rounded-start-3 shadow-none border-light-subtle" required>
                                    <span class="input-group-text rounded-end-3" id="satuan-addon"></span>
                                </div>
                            </div>

                            <div class="col-6 xy-input" style="display: none;">
                                <label class="form-label fw-bold small">Nilai X <span class="text-muted fw-normal" id="label-def-x"></span></label>
                                <input type="number" step="0.01" name="realisasi_x" id="realisasi_x" class="form-control form-control-sm rounded-3 shadow-none border-light-subtle">
                            </div>

                            <div class="col-6 xy-input" style="display: none;">
                                <label class="form-label fw-bold small">Nilai Y <span class="text-muted fw-normal" id="label-def-y"></span></label>
                                <input type="number" step="0.01" name="realisasi_y" id="realisasi_y" class="form-control form-control-sm rounded-3 shadow-none border-light-subtle">
                            </div>
                        </div>

                        <!-- Step 2 Content -->
                        <div id="step2-capaian-content" class="row g-3" style="display: none;">
                            <div class="col-12 mt-2 d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                                <div class="d-flex align-items-center gap-2 w-100 justify-content-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill py-1 px-3 btn-generate-formula" title="Otomatis susun rumus LaTeX dengan variabel X dan Y">
                                        <i class="fas fa-calculator me-1"></i> Buat Rumus (X/Y)
                                    </button>
                                    @if($triwulan > 1)
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle rounded-pill py-1 px-3" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-copy me-1"></i> Salin Narasi...
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                            @for($i = 1; $i < $triwulan; $i++)
                                            <li><a class="dropdown-item btn-copy-narasi small" href="#" data-tw="{{ $i }}">Dari Triwulan {{ $i }}</a></li>
                                            @endfor
                                        </ul>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fw-bold small">Dasar Hitung</label>
                                <textarea name="dasar_hitung" id="dasar_hitung" class="form-control rounded-3 shadow-none border-light-subtle" rows="3" placeholder="Jelaskan cara menghitung capaian ini..."></textarea>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label fw-bold small">Target Triwulanan</label>
                                <textarea name="target_realisasi" id="target_realisasi" class="form-control rounded-3 shadow-none border-light-subtle" rows="3" placeholder="Jelaskan mengenai target dan realisasi..."></textarea>
                            </div>
                        </div>
                        
                        <!-- Step 3 Content -->
                        <div id="step3-capaian-content" class="row g-3" style="display: none;">
                            <div class="col-12">
                                <label class="form-label fw-bold small">Argumen Logis</label>
                                <textarea name="argumen_logis" id="argumen_logis" class="form-control rounded-3 shadow-none border-light-subtle" rows="3" placeholder="Masukkan argumen logis terkait capaian periode ini..."></textarea>
                            </div>
                        </div>
                        
                        <!-- Step 4 Content -->
                        <div id="step4-capaian-content" class="row g-3" style="display: none;">
                            <div class="col-12">
                                <label class="form-label fw-bold small">Tautan Bukti Dukung Kinerja</label>
                                <div class="input-group">
                                    <input type="text" name="link_bukti_kinerja" id="link_bukti_kinerja" class="form-control rounded-start-3 shadow-none border-light-subtle" placeholder="https://...">
                                    <a href="#" target="_blank" class="btn btn-outline-primary" id="btn-open-kinerja" style="display:none;" title="Buka Tautan"><i class="fas fa-external-link-alt"></i></a>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small">Tautan Bukti Dukung Rencana Tindak Lanjut (RTL)</label>
                                <div class="input-group">
                                    <input type="text" name="link_bukti_tindak_lanjut" id="link_bukti_tindak_lanjut" class="form-control rounded-start-3 shadow-none border-light-subtle" placeholder="https://...">
                                    <a href="#" target="_blank" class="btn btn-outline-primary" id="btn-open-rtl" style="display:none;" title="Buka Tautan"><i class="fas fa-external-link-alt"></i></a>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small">Penjelasan atau Pembahasan Lainnya</label>
                                <textarea name="penjelasan_lainnya" id="penjelasan_lainnya" class="form-control rounded-3 shadow-none border-light-subtle" rows="3" placeholder="Tambahkan penjelasan lainnya..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 d-flex justify-content-between">
                        <button type="button" class="btn btn-light rounded-pill px-4 shadow-sm" id="btnPrevStepCapaian" style="display: none;"><i class="fas fa-arrow-left me-1"></i> Kembali</button>
                        <div class="ms-auto">
                            <button type="button" class="btn btn-light rounded-pill px-4 shadow-sm me-2" data-bs-dismiss="modal">Batal</button>
                            <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" id="btnNextStepCapaian">Lanjut <i class="fas fa-arrow-right ms-1"></i></button>
                            <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm" id="btnSimpan"><i class="fas fa-save me-1"></i> Simpan Data</button>
                        </div>
                    </div>
            </form>
        </div>
    </div>

    <!-- Modal Import -->
    <div class="modal fade" id="modalImport" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Import Capaian Kinerja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('capaian-kinerja.import') }}" method="POST" enctype="multipart/form-data" id="formImport">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="alert alert-info border-0 rounded-4 shadow-sm mb-4">
                            <div class="small fw-bold"><i class="fas fa-info-circle me-1"></i> Silakan unduh template Excel terlebih dahulu, lalu isi data Capaian Kinerja.</div>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-bold small mb-0">Pilih Indikator yang Ingin Diisi</label>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" id="btnSelectAllIku">Pilih Semua</button>
                            </div>
                            <div class="border rounded-3 p-2" style="max-height: 200px; overflow-y: auto;">
                                @foreach($indikators as $ind)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input iku-checkbox" type="checkbox" name="indikator_ids[]" value="{{ $ind->id }}" id="iku_{{ $ind->id }}" checked>
                                        <label class="form-check-label small" for="iku_{{ $ind->id }}">
                                            <strong>{{ $ind->kode }}</strong> - {{ Str::limit($ind->indikator_kinerja, 60) }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-4 text-center">
                            <button type="button" class="btn btn-outline-success rounded-pill px-4 fw-bold" onclick="downloadTemplate()">
                                <i class="fas fa-download me-1"></i> Download Template Capaian
                            </button>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Tahun</label>
                            <input type="number" name="tahun" class="form-control rounded-3" value="{{ $tahun }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Pilih Triwulan</label>
                            <select name="triwulan" class="form-select rounded-3" required>
                                <option value="1">Triwulan I</option>
                                <option value="2">Triwulan II</option>
                                <option value="3">Triwulan III</option>
                                <option value="4">Triwulan IV</option>
                            </select>
                            <div class="form-text small">Narasi & Tautan akan disimpan untuk triwulan yang dipilih, sedangkan angka Realisasi TW1-TW4 akan disimpan sesuai kolom di Excel.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Pilih File Excel</label>
                            <input type="file" name="file" class="form-control rounded-3" accept=".xlsx, .xls" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4 shadow-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm"><i class="fas fa-upload me-1"></i> Proses Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Approval -->
    @if(auth()->user()->isAdminOrPimpinan())
    <div class="modal fade" id="modalApproval" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Persetujuan (ACC) Pimpinan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formApproval" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Status Persetujuan</label>
                            <select name="status_approval" id="status_approval" class="form-select rounded-3" required onchange="toggleCatatan(this.value)">
                                <option value="Menunggu">Menunggu</option>
                                <option value="Disetujui">Disetujui</option>
                                <option value="Ditolak">Ditolak (Perlu Perbaikan)</option>
                            </select>
                        </div>
                        <div class="mb-3" id="catatan_container" style="display: none;">
                            <label class="form-label fw-bold small text-danger">Catatan Perbaikan</label>
                            <textarea name="catatan_pimpinan" id="catatan_pimpinan" class="form-control rounded-3 border-danger-subtle" rows="3" placeholder="Masukkan catatan perbaikan jika ditolak..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4 shadow-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-info text-white rounded-pill px-4 shadow-sm"><i class="fas fa-save me-1"></i> Simpan Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
    <!-- Modals for History -->
    @foreach($capaians as $capaian)
        @if($capaian)
            <div class="modal fade" id="modalHistory{{ $capaian->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
                    <div class="modal-content rounded-4 border-0 shadow">
                        <div class="modal-header bg-secondary text-white rounded-top-4">
                            <h5 class="modal-title fw-bold"><i class="fas fa-history me-2"></i> Riwayat Perbaikan: {{ $capaian->indikator->kode ?? '' }}</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4 bg-light">
                            @if($capaian->histories && $capaian->histories->count() > 0)
                                @foreach($capaian->histories as $history)
                                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                                        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                                            <div class="fw-bold text-dark"><i class="fas fa-clock text-secondary me-2"></i> Disimpan pada: {{ $history->created_at->format('d M Y, H:i') }}</div>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill">Ditolak</span>
                                        </div>
                                        <div class="card-body">
                                            @if($history->catatan_pimpinan)
                                                <div class="alert alert-danger rounded-3 mb-4">
                                                    <strong><i class="fas fa-exclamation-triangle me-2"></i> Catatan Pimpinan (Alasan Penolakan):</strong><br>
                                                    {{ $history->catatan_pimpinan }}
                                                </div>
                                            @endif
                                            
                                            <div class="row g-4">
                                                <div class="col-md-6">
                                                    <div class="p-3 bg-light rounded-3 h-100 border border-light-subtle">
                                                        <h6 class="fw-bold text-primary mb-3 border-bottom pb-2">Realisasi & Bukti Kinerja</h6>
                                                        
                                                        <label class="fw-bold text-muted small mt-2">Realisasi Kumulatif</label>
                                                        <div class="mb-3 small bg-white p-2 rounded border border-light-subtle">
                                                            {{ $history->realisasi_kumulatif ?? '-' }} %
                                                        </div>

                                                        <label class="fw-bold text-muted small">Variabel X (Realisasi)</label>
                                                        <div class="mb-3 small bg-white p-2 rounded border border-light-subtle">
                                                            {{ $history->realisasi_x ?? '-' }}
                                                        </div>

                                                        <label class="fw-bold text-muted small">Variabel Y (Target)</label>
                                                        <div class="mb-3 small bg-white p-2 rounded border border-light-subtle">
                                                            {{ $history->realisasi_y ?? '-' }}
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="p-3 bg-light rounded-3 h-100 border border-light-subtle">
                                                        <h6 class="fw-bold text-success mb-3 border-bottom pb-2">Tautan Bukti Dukung</h6>

                                                        <label class="fw-bold text-muted small">Link Bukti Kinerja</label>
                                                        <div class="mb-3 small bg-white p-2 rounded border border-light-subtle">
                                                            @if($history->link_bukti_kinerja)
                                                                <a href="{{ $history->link_bukti_kinerja }}" target="_blank" class="text-break"><i class="fas fa-link"></i> {{ $history->link_bukti_kinerja }}</a>
                                                            @else
                                                                <span class="text-muted fst-italic">Kosong</span>
                                                            @endif
                                                        </div>
                                                        
                                                        <label class="fw-bold text-muted small">Link Bukti Tindak Lanjut</label>
                                                        <div class="small bg-white p-2 rounded border border-light-subtle">
                                                            @if($history->link_bukti_tindak_lanjut)
                                                                <a href="{{ $history->link_bukti_tindak_lanjut }}" target="_blank" class="text-break"><i class="fas fa-link"></i> {{ $history->link_bukti_tindak_lanjut }}</a>
                                                            @else
                                                                <span class="text-muted fst-italic">Kosong</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 text-secondary opacity-50"></i>
                                    <h5>Belum ada riwayat perbaikan</h5>
                                    <p class="mb-0">Indikator ini belum pernah ditolak atau belum ada revisi yang dicatat.</p>
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer bg-white rounded-bottom-4">
                            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    <!-- Modal History Empty -->
    <div class="modal fade" id="modalHistoryEmpty" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header bg-secondary text-white rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="fas fa-history me-2"></i> Riwayat Perbaikan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-5 text-center">
                    <i class="fas fa-inbox fa-3x mb-3 text-secondary opacity-50"></i>
                    <h5 class="text-dark">Belum ada isian atau riwayat</h5>
                    <p class="text-muted mb-0">Capaian kinerja untuk indikator ini belum diisi.</p>
                </div>
                <div class="modal-footer bg-white border-0 rounded-bottom-4 justify-content-center">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function downloadTemplate() {
            const form = document.getElementById('formImport');
            const oldAction = form.action;
            form.action = "{{ route('capaian-kinerja.template') }}";
            form.submit();
            
            // revert back for import
            setTimeout(() => {
                form.action = oldAction;
            }, 100);
        }

        $(document).ready(function () {
            $('#btnSelectAllIku').on('click', function() {
                const checkboxes = $('.iku-checkbox');
                const allChecked = checkboxes.length === checkboxes.filter(':checked').length;
                checkboxes.prop('checked', !allChecked);
                $(this).text(!allChecked ? 'Batal Pilih Semua' : 'Pilih Semua');
            });

            if (typeof window.initTinyMCE === 'function') {
                window.initTinyMCE('#dasar_hitung');
                window.initTinyMCE('#argumen_logis');
                window.initTinyMCE('#penjelasan_lainnya');
                window.initTinyMCE('#target_realisasi');
            }

            // Show modal and populate data
            $('.btn-edit-capaian').on('click', function () {
                const btn = $(this);
                
                $('#indikator_id').val(btn.data('id'));
                $('#modal-kode-indikator').text(btn.data('kode'));
                $('#modal-nama-indikator').text(btn.data('nama'));
                $('#modal-satuan').text(btn.data('satuan'));
                $('#modal-target').text(btn.data('target'));
                $('#satuan-addon').text(btn.data('satuan'));
                
                $('#realisasi_kumulatif').val(btn.data('rkumulatif'));
                $('#realisasi_x').val(btn.data('rx'));
                $('#realisasi_y').val(btn.data('ry'));
                
                const defX = btn.data('defx');
                const defY = btn.data('defy');
                
                if (defX || defY) {
                    $('.xy-input').show();
                    $('#label-def-x').text(defX ? `(${defX})` : '');
                    $('#label-def-y').text(defY ? `(${defY})` : '');
                    
                    // Auto-fill Nilai Y with target Y if empty
                    if (!$('#realisasi_y').val()) {
                        $('#realisasi_y').val(btn.data('targety'));
                    }
                } else {
                    $('.xy-input').hide();
                }
                
                if (typeof tinymce !== 'undefined' && tinymce.get('dasar_hitung')) {
                    tinymce.get('dasar_hitung').setContent(btn.data('dasar') ? String(btn.data('dasar')) : '');
                } else {
                    $('#dasar_hitung').val(btn.data('dasar'));
                }
                
                if (typeof tinymce !== 'undefined' && tinymce.get('argumen_logis')) {
                    tinymce.get('argumen_logis').setContent(btn.data('argumen') ? String(btn.data('argumen')) : '');
                } else {
                    $('#argumen_logis').val(btn.data('argumen'));
                }
                
                if (typeof tinymce !== 'undefined' && tinymce.get('penjelasan_lainnya')) {
                    tinymce.get('penjelasan_lainnya').setContent(btn.data('penjelasan') ? String(btn.data('penjelasan')) : '');
                } else {
                    $('#penjelasan_lainnya').val(btn.data('penjelasan'));
                }
                
                if (typeof tinymce !== 'undefined' && tinymce.get('target_realisasi')) {
                    tinymce.get('target_realisasi').setContent(btn.data('targetrealisasi') ? String(btn.data('targetrealisasi')) : '');
                } else {
                    $('#target_realisasi').val(btn.data('targetrealisasi'));
                }

                const linkKinerja = btn.data('kinerja');
                const linkRtl = btn.data('rtl');
                
                $('#link_bukti_kinerja').val(linkKinerja);
                $('#link_bukti_tindak_lanjut').val(linkRtl);
                
                if (linkKinerja) {
                    $('#btn-open-kinerja').attr('href', linkKinerja).show();
                } else {
                    $('#btn-open-kinerja').hide();
                }
                
                if (linkRtl) {
                    $('#btn-open-rtl').attr('href', linkRtl).show();
                } else {
                    $('#btn-open-rtl').hide();
                }

                // Add listeners for typing to show/hide the button
                $('#link_bukti_kinerja').on('input', function() {
                    const val = $(this).val();
                    if(val) { $('#btn-open-kinerja').attr('href', val).show(); } else { $('#btn-open-kinerja').hide(); }
                });
                $('#link_bukti_tindak_lanjut').on('input', function() {
                    const val = $(this).val();
                    if(val) { $('#btn-open-rtl').attr('href', val).show(); } else { $('#btn-open-rtl').hide(); }
                });

                const modal = new bootstrap.Modal(document.getElementById('modalCapaian'));
                modal.show();
            });

            // Wizard Logic for Capaian
            let currentStepCapaian = 1;

            function updateWizardCapaian() {
                // Update Progress bar (25% per step, max 100%)
                const progress = ((currentStepCapaian - 1) / 3) * 100;
                $('#wizardProgressCapaian').css('width', progress + '%');

                // Update Circles & Labels
                for (let i = 1; i <= 4; i++) {
                    const circle = $(`#step${i}-capaian-circle`);
                    const label = $(`#step${i}-capaian-label`);
                    const content = $(`#step${i}-capaian-content`);
                    
                    if (i === currentStepCapaian) {
                        circle.removeClass('inactive-step').addClass('active-step');
                        label.removeClass('text-muted').addClass('text-primary');
                        content.show();
                    } else if (i < currentStepCapaian) {
                        circle.removeClass('inactive-step').addClass('active-step');
                        label.removeClass('text-primary').addClass('text-muted');
                        content.hide();
                    } else {
                        circle.removeClass('active-step').addClass('inactive-step');
                        label.removeClass('text-primary').addClass('text-muted');
                        content.hide();
                    }
                }

                // Update Buttons
                if (currentStepCapaian === 1) {
                    $('#btnPrevStepCapaian').hide();
                    $('#btnNextStepCapaian').show();
                } else if (currentStepCapaian === 4) {
                    $('#btnPrevStepCapaian').show();
                    $('#btnNextStepCapaian').hide();
                } else {
                    $('#btnPrevStepCapaian').show();
                    $('#btnNextStepCapaian').show();
                }
            }

            $('.step-capaian-clickable').click(function() {
                const targetStep = parseInt($(this).data('step'));
                
                // Simple validation for step 1 if moving forward
                if (currentStepCapaian === 1 && targetStep > 1) {
                    const input = $('#realisasi_kumulatif')[0];
                    if (!input.checkValidity()) {
                        input.reportValidity();
                        return;
                    }
                }
                
                currentStepCapaian = targetStep;
                updateWizardCapaian();
            });

            $('#btnNextStepCapaian').click(function() {
                // Simple validation for step 1
                if (currentStepCapaian === 1) {
                    const input = $('#realisasi_kumulatif')[0];
                    if (!input.checkValidity()) {
                        input.reportValidity();
                        return;
                    }
                }
                
                if (currentStepCapaian < 4) {
                    currentStepCapaian++;
                    updateWizardCapaian();
                }
            });

            $('#btnPrevStepCapaian').click(function() {
                if (currentStepCapaian > 1) {
                    currentStepCapaian--;
                    updateWizardCapaian();
                }
            });
            
            // On Modal Open event: reset wizard to step 1
            $('#modalCapaian').on('show.bs.modal', function () {
                currentStepCapaian = 1;
                updateWizardCapaian();
            });

            // Form Submit
            $('#formCapaian').on('submit', function (e) {
                e.preventDefault();
                
                if (typeof tinymce !== 'undefined') {
                    tinymce.triggerSave();
                }
                
                const btn = $('#btnSimpan');
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

                $.ajax({
                    url: '{{ route("capaian-kinerja.store") }}',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function (response) {
                        btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Simpan Data');
                        
                        // Close modal and show success toast
                        bootstrap.Modal.getInstance(document.getElementById('modalCapaian')).hide();
                        toastr.success(response.message);
                        
                        // Reload page to reflect changes
                        setTimeout(() => window.location.reload(), 1000);
                    },
                    error: function (xhr) {
                        btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Simpan Data');
                        const msg = xhr.responseJSON?.message || 'Gagal menyimpan data.';
                        toastr.error(msg);
                    }
                });
            });
        });

        // Copy Narasi Button
        $(document).on('click', '.btn-copy-narasi', function(e) {
            e.preventDefault();
            const tw = $(this).data('tw');
            const indikatorId = $('#indikator_id').val();
            const tahun = $('input[name="tahun"]').val();
            
            const btn = $(this).closest('.dropdown').find('.dropdown-toggle');
            const originalText = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Menyalin...').prop('disabled', true);

            $.get(`{{ url('capaian-kinerja') }}/${indikatorId}/previous-data`, { tahun: tahun, triwulan: tw }, function(res) {
                if(res.status === 'success') {
                    const data = res.data;
                    
                    if(data.dasar_hitung !== undefined && window.tinymce && tinymce.get('dasar_hitung')) {
                        tinymce.get('dasar_hitung').setContent(data.dasar_hitung);
                    } else if (data.dasar_hitung !== undefined) {
                        $('#dasar_hitung').val(data.dasar_hitung);
                    }
                    
                    if(data.argumen_logis !== undefined && window.tinymce && tinymce.get('argumen_logis')) {
                        tinymce.get('argumen_logis').setContent(data.argumen_logis);
                    } else if (data.argumen_logis !== undefined) {
                        $('#argumen_logis').val(data.argumen_logis);
                    }
                    
                    if(data.penjelasan_lainnya !== undefined && window.tinymce && tinymce.get('penjelasan_lainnya')) {
                        tinymce.get('penjelasan_lainnya').setContent(data.penjelasan_lainnya);
                    } else if (data.penjelasan_lainnya !== undefined) {
                        $('#penjelasan_lainnya').val(data.penjelasan_lainnya);
                    }
                    
                    if(data.target_realisasi !== undefined && window.tinymce && tinymce.get('target_realisasi')) {
                        tinymce.get('target_realisasi').setContent(data.target_realisasi);
                    } else if (data.target_realisasi !== undefined) {
                        $('#target_realisasi').val(data.target_realisasi);
                    }
                    
                    toastr.success(`Berhasil menyalin narasi dari Triwulan ${tw}`);
                }
            }).fail(function() {
                toastr.warning(`Data narasi Triwulan ${tw} masih kosong atau belum diisi.`);
            }).always(function() {
                btn.html(originalText).prop('disabled', false);
            });
        });

        // Generate Formula Button (Variabel X & Y)
        $(document).on('click', '.btn-generate-formula', function(e) {
            e.preventDefault();
            const rx = $('#realisasi_x').val() || '0';
            const ry = $('#realisasi_y').val() || '0';
            const rkum = $('#realisasi_kumulatif').val() || '0';
            const targetVal = $('#modal-target').text() || '0';
            const tw = '{{ $triwulan }}';
            const th = '{{ $tahun }}';
            const romanTw = tw == 1 ? 'I' : (tw == 2 ? 'II' : (tw == 3 ? 'III' : 'IV'));

            const formulaHtml = `<p><strong>Target Triwulan ${romanTw} ${th}</strong></p>` +
                `<p>$$y = \\frac{X}{Y} \\times 100\\% = \\frac{0}{${ry}} \\times 100\\% = ${targetVal} \\text{ persen}$$</p>` +
                `<br>` +
                `<p><strong>Realisasi Triwulan ${romanTw} ${th}</strong></p>` +
                `<p>$$y = \\frac{X}{Y} \\times 100\\% = \\frac{${rx}}{${ry}} \\times 100\\% = ${rkum} \\text{ persen}$$</p>`;

            if (window.tinymce && tinymce.get('target_realisasi')) {
                tinymce.get('target_realisasi').setContent(formulaHtml);
            } else {
                $('#target_realisasi').val(formulaHtml);
            }

            toastr.success('Rumus LaTeX dengan variabel X dan Y berhasil dibuat!');
        });

        // ACC Button
        $('.btn-acc').on('click', function() {
            const btn = $(this);
            const id = btn.data('id');
            const status = btn.data('status');
            const catatan = btn.data('catatan');

            $('#status_approval').val(status);
            $('#catatan_pimpinan').val(catatan);
            toggleCatatan(status);

            const form = $('#formApproval');
            form.attr('action', `{{ url('capaian-kinerja') }}/${id}/approve`);
            
            const modal = new bootstrap.Modal(document.getElementById('modalApproval'));
            modal.show();
        });

        function toggleCatatan(status) {
            if(status === 'Ditolak') {
                $('#catatan_container').show();
            } else {
                $('#catatan_container').hide();
                $('#catatan_pimpinan').val('');
            }
        }
    </script>
@endsection

