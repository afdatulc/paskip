@extends('layouts.dashboard')

@section('title', auth()->user()->isAdmin() ? 'Master Indikator' : 'Daftar Tanggung Jawab Indikator Kinerja')

@section('content')
    <div class="card border-0 shadow-sm rounded-4 text-dark">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                @if(auth()->user()->isAdmin())
                    <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" data-bs-toggle="modal"
                        data-bs-target="#modalIndikator">
                        <i class="fas fa-plus me-1"></i> Tambah Indikator
                    </button>
                    <button type="button" class="btn btn-outline-info rounded-pill px-3 ms-2 fw-bold" data-bs-toggle="modal"
                        data-bs-target="#modalImportXY">
                        <i class="fas fa-file-excel me-1"></i> Import Target X/Y
                    </button>
                @else
                    <div class="fw-bold text-dark"><i class="fas fa-list-check me-2 text-primary"></i> Daftar Tanggung Jawab
                        Indikator Kinerja</div>
                @endif
            </div>
            @if(auth()->user()->isAdmin())
                <button type="button" class="btn btn-success rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalImportIndikator">
                    <i class="fas fa-upload me-1"></i> Import Indikator
                </button>
            @endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="indikatorTable">
                    <thead class="table-light">
                        <tr>
                            <th width="50" class="text-center">No</th>
                            <th style="min-width: 350px;">Sasaran & Indikator Kinerja</th>
                            <th width="120">Jenis / Periode</th>
                            <th width="80" class="text-center">Output</th>
                            <th width="120">Tipe / Satuan</th>
                            <th width="100">Target</th>
                            <th width="150">PIC</th>
                            <th width="120" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($indikators as $i)
                            <tr id="row-{{ $i->id }}">
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="fw-bold text-dark mb-1">
                                        @if($i->kode_indikator_kinerja)
                                            <span class="text-primary me-1">[{{ $i->kode_indikator_kinerja }}]</span>
                                        @endif
                                        {{ $i->indikator_kinerja }}
                                    </div>
                                    <div class="small text-muted" style="font-size: 0.75rem;"><i
                                            class="fas fa-crosshairs me-1 text-secondary"></i>
                                        @if($i->kode_sasaran)
                                            <span class="fw-semibold me-1">[{{ $i->kode_sasaran }}]</span>
                                        @endif
                                        {{ $i->sasaran }}</div>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-2 mb-1">{{ $i->jenis_indikator }}</span>
                                    <div class="extra-small text-muted ps-1">{{ $i->periode }} ({{ $i->tahun }})</div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle rounded-pill px-2">
                                        {{ $i->output_masters_count }}
                                    </span>
                                </td>

                                <td>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill px-2 mb-1">{{ $i->tipe ?: '-' }}</span>
                                    <div class="extra-small text-muted ps-1">{{ $i->satuan ?: '-' }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary">{{ number_format($i->target_tahunan_efektif, 2) }}</div>
                                    @php $pkModel = $i->pkTahunans->firstWhere('tahun', $i->tahun); @endphp
                                    @if($pkModel)
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill extra-small"
                                              title="Target terhubung otomatis dengan PK Tahunan {{ $i->tahun }}">
                                            <i class="fas fa-handshake me-1"></i>PK {{ $pkModel->status_revisi ? '(Revisi)' : '' }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($i->pic)
                                        <div class="small fw-bold text-dark">{{ $i->pic->nama }}</div>
                                    @else
                                        <span class="text-muted small italic">- Belum diatur -</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center gap-2">

                                        @if(auth()->user()->isAdmin() || $i->pic_id == auth()->user()->pegawai_id)
                                            <button class="btn btn-sm btn-primary rounded-3 manage-indikator d-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px;"
                                                data-id="{{ $i->id }}" data-kode="{{ $i->kode }}" title="Kelola Indikator">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                        @endif
                                        @if(auth()->user()->isAdmin())
                                            <button class="btn btn-sm btn-outline-danger rounded-3 delete-indikator d-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px;"
                                                data-id="{{ $i->id }}" data-kode="{{ $i->kode }}" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    
    <!-- Unified Modal Tambah/Edit Indikator -->
    <div class="modal fade" id="modalIndikator" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
            <form id="formIndikator" class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 px-4 pt-4 pb-0">
                    <h5 class="modal-title fw-bold" id="modalTitle">Tambah Indikator Kinerja Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <input type="hidden" id="indikator_id">
                    
                    <div class="modal-body p-4 pt-3 d-flex flex-column" style="min-height: 55vh;">
                        <style>
                            .wizard-step-indicator { width: 40px; height: 40px; line-height: 40px; text-align: center; font-weight: bold; font-size: 1.1rem; border-radius: 50%; display: inline-block; transition: all 0.3s; z-index: 2; position: relative; }
                            .wizard-step-indicator.active { background-color: #0d6efd; color: white; border: 2px solid #0d6efd; }
                            .wizard-step-indicator.inactive { background-color: white; color: #6c757d; border: 2px solid #dee2e6; }
                            .wizard-line { position: absolute; top: 20px; left: 25%; right: 25%; height: 4px; background-color: #dee2e6; z-index: 1; border-radius: 2px; }
                            .wizard-line-progress { position: absolute; top: 0; left: 0; height: 100%; background-color: #0d6efd; width: 0%; transition: width 0.3s; border-radius: 2px; }
                        </style>
                        <div class="position-relative text-center mb-5 mt-2">
                            <div class="wizard-line">
                                <div class="wizard-line-progress" id="wizardProgress"></div>
                            </div>
                            <div class="d-flex justify-content-around">
                                <div class="step-indikator-clickable text-center" data-step="1" style="cursor: pointer;">
                                    <div class="wizard-step-indicator active" id="indicator1">1</div>
                                    <div class="small fw-bold mt-2 text-primary" id="label1">Metadata Indikator</div>
                                </div>
                                <div class="step-indikator-clickable text-center" data-step="2" style="cursor: pointer;">
                                    <div class="wizard-step-indicator inactive" id="indicator2">2</div>
                                    <div class="small fw-bold mt-2 text-muted" id="label2">Target Triwulanan</div>
                                </div>
                            </div>
                        </div>

                        <div id="manageTabsContent">
                            <!-- Step 1: Metadata -->
                            <div class="wizard-step" id="meta">
                                <div class="row g-3">
                                      <!-- Input Kode -->
                                      <div class="col-md-3">
                                          <label class="form-label fw-bold small">Kode Utama</label>
                                          <input type="text" name="kode" id="kode" class="form-control form-control-sm rounded-3 border-light-subtle" placeholder="Contoh: 1.1.1.1" required>
                                      </div>
                                      <div class="col-md-3">
                                          <label class="form-label fw-bold small">Kode Tujuan</label>
                                          <input type="text" name="kode_tujuan" id="kode_tujuan" class="form-control form-control-sm rounded-3 border-light-subtle" placeholder="Contoh: T1">
                                      </div>
                                      <div class="col-md-3">
                                          <label class="form-label fw-bold small">Kode Sasaran</label>
                                          <input type="text" name="kode_sasaran" id="kode_sasaran" class="form-control form-control-sm rounded-3 border-light-subtle" placeholder="Contoh: 1.1.1">
                                      </div>
                                      <div class="col-md-3">
                                          <label class="form-label fw-bold small">Kode Indikator Kinerja</label>
                                          <input type="text" name="kode_indikator_kinerja" id="kode_indikator_kinerja" class="form-control form-control-sm rounded-3 border-light-subtle" placeholder="Contoh: 1.1.1.1">
                                      </div>
                                    
                                    <div class="col-12">
                                        <label class="form-label fw-bold small">Sasaran</label>
                                        <input type="text" name="sasaran" id="sasaran" class="form-control form-control-sm rounded-3 border-light-subtle" required>
                                    </div>
                                    <div class="col-12 mt-4 border-top pt-3">
                                        <label class="form-label fw-bold small">Indikator Kinerja</label>
                                        <input type="text" name="indikator_kinerja" id="indikator_kinerja" class="form-control form-control-sm rounded-3 border-light-subtle" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Jenis</label>
                                        <select name="jenis_indikator" id="jenis_indikator" class="form-select form-select-sm rounded-3 border-light-subtle" required>
                                            <option value="" disabled selected>-- Pilih Jenis --</option>
                                            <option value="IKU">IKU</option>
                                            <option value="Proksi">Proksi</option>
                                            <option value="IK">IK</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Periode</label>
                                        <select name="periode" id="periode" class="form-select form-select-sm rounded-3 border-light-subtle">
                                            <option value="" disabled selected>-- Pilih Periode --</option>
                                            <option value="Tahunan">Tahunan</option>
                                            <option value="Triwulanan">Triwulanan</option>
                                            <option value="Bulanan">Bulanan</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Tipe</label>
                                        <select name="tipe" id="tipe" class="form-select form-select-sm rounded-3 border-light-subtle">
                                            <option value="" disabled selected>-- Pilih Tipe --</option>
                                            <option value="%">%</option>
                                            <option value="Non %">Non %</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Target</label>
                                        <input type="number" step="0.01" name="target_tahunan" id="target_tahunan" class="form-control form-control-sm rounded-3 border-light-subtle" placeholder="100">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Satuan</label>
                                        <input type="text" name="satuan" id="satuan" class="form-control form-control-sm rounded-3 border-light-subtle" placeholder="Persen">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Tahun</label>
                                        <input type="number" name="tahun" id="tahun" value="{{ date('Y') }}" class="form-control form-control-sm rounded-3 border-light-subtle" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Penanggung Jawab (PIC)</label>
                                        <select name="pic_id" id="pic_id" class="form-select form-select-sm rounded-3 border-light-subtle" {{ !auth()->user()->isAdmin() ? 'disabled' : '' }}>
                                            <option value="">-- Tanpa PIC --</option>
                                            @foreach($pegawais as $p)
                                                <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->nip }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <!-- Dasar Hitung Formula -->
                                    <div class="col-12 mt-3">
                                        <div class="p-3 rounded-3 border border-secondary-subtle bg-light">
                                            <div class="small fw-bold text-dark mb-1">
                                                <i class="fas fa-calculator me-1"></i> Formula Dasar Hitung <span class="fw-normal text-muted">(opsional)</span>
                                            </div>
                                            <div class="row g-2 mt-1">
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold"><span class="badge bg-secondary text-white me-1">X</span>Deskripsi Pembilang (X)</label>
                                                    <input type="text" name="definisi_x" id="definisi_x" class="form-control form-control-sm rounded-3" placeholder="Misal: Jumlah Publikasi berkualitas">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold"><span class="badge bg-secondary text-white me-1">Y</span>Deskripsi Penyebut (Y)</label>
                                                    <input type="text" name="definisi_y" id="definisi_y" class="form-control form-control-sm rounded-3" placeholder="Misal: Jumlah seluruh Publikasi">
                                                </div>
                                            </div>
                                            <div class="row g-2 mt-2">
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold">Target Tahunan Pembilang (X)</label>
                                                    <input type="number" step="0.01" name="target_tahunan_x" id="target_tahunan_x" class="form-control form-control-sm rounded-3">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold">Target Tahunan Penyebut (Y)</label>
                                                    <input type="number" step="0.01" name="target_tahunan_y" id="target_tahunan_y" class="form-control form-control-sm rounded-3">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            </div>

                            <!-- Step 2: Target TW -->
                            <div class="wizard-step" id="target" style="display:none;">
                                <div class="alert alert-info border-0 rounded-3 shadow-sm mb-4 mx-2">
                                    <div class="small fw-bold"><i class="fas fa-info-circle me-1"></i> Atur target kumulatif untuk masing-masing triwulan.</div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle text-center mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th rowspan="2" class="align-middle" style="width: 20%;">Target (TW)</th>
                                                <th colspan="4">Alokasi Target Kumulatif</th>
                                            </tr>
                                            <tr>
                                                <th style="width: 20%;">TW I</th>
                                                <th style="width: 20%;">TW II</th>
                                                <th style="width: 20%;">TW III</th>
                                                <th style="width: 20%;">TW IV</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="text-start fw-bold small bg-light">Indikator Utama</td>
                                                <td><input type="number" step="0.01" name="target_tw1" id="target_tw1" class="form-control form-control-sm text-center" placeholder="0"></td>
                                                <td><input type="number" step="0.01" name="target_tw2" id="target_tw2" class="form-control form-control-sm text-center" placeholder="0"></td>
                                                <td><input type="number" step="0.01" name="target_tw3" id="target_tw3" class="form-control form-control-sm text-center" placeholder="0"></td>
                                                <td><input type="number" step="0.01" name="target_tw4" id="target_tw4" class="form-control form-control-sm text-center" placeholder="0"></td>
                                            </tr>
                                            <tr class="xy-row" style="display:none;">
                                                <td class="text-start fw-bold small bg-light text-primary"><span class="badge bg-primary me-1">X</span> Pembilang</td>
                                                <td><input type="number" step="0.01" name="target_x_tw1" id="target_x_tw1" class="form-control form-control-sm text-center" placeholder="0"></td>
                                                <td><input type="number" step="0.01" name="target_x_tw2" id="target_x_tw2" class="form-control form-control-sm text-center" placeholder="0"></td>
                                                <td><input type="number" step="0.01" name="target_x_tw3" id="target_x_tw3" class="form-control form-control-sm text-center" placeholder="0"></td>
                                                <td><input type="number" step="0.01" name="target_x_tw4" id="target_x_tw4" class="form-control form-control-sm text-center" placeholder="0"></td>
                                            </tr>
                                            <tr class="xy-row" style="display:none;">
                                                <td class="text-start fw-bold small bg-light text-secondary"><span class="badge bg-secondary me-1">Y</span> Penyebut</td>
                                                <td><input type="number" step="0.01" name="target_y_tw1" id="target_y_tw1" class="form-control form-control-sm text-center" placeholder="0"></td>
                                                <td><input type="number" step="0.01" name="target_y_tw2" id="target_y_tw2" class="form-control form-control-sm text-center" placeholder="0"></td>
                                                <td><input type="number" step="0.01" name="target_y_tw3" id="target_y_tw3" class="form-control form-control-sm text-center" placeholder="0"></td>
                                                <td><input type="number" step="0.01" name="target_y_tw4" id="target_y_tw4" class="form-control form-control-sm text-center" placeholder="0"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    <div class="modal-footer border-0 pt-0 mt-auto d-flex justify-content-between w-100">
                        <button type="button" class="btn btn-light rounded-pill px-4 shadow-sm border" id="btnPrevStep" style="display:none;"><i class="fas fa-arrow-left me-1"></i> Kembali</button>
                        <div class="ms-auto">
                            <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm me-2" id="btnNextStep">Selanjutnya <i class="fas fa-arrow-right ms-1"></i></button>
                            <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm" id="btnSimpanBaru"><i class="fas fa-save me-1"></i> Simpan Indikator</button>
                        </div>
                    </div>
            </form>
        </div>
    </div>

    <!-- Modal Import Target X/Y -->
    <div class="modal fade" id="modalImportXY" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Import Target & Definisi X/Y</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('indikator.import-xy') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="alert alert-info border-0 rounded-4 shadow-sm mb-4">
                            <div class="small fw-bold"><i class="fas fa-info-circle me-1"></i> Silakan unduh template Excel terlebih dahulu, lalu isi data Definisi dan Target X/Y sesuai Kode Indikator.</div>
                        </div>
                        <div class="mb-3 text-center">
                            <a href="{{ route('indikator.template-xy') }}" class="btn btn-outline-success rounded-pill px-4 fw-bold">
                                <i class="fas fa-download me-1"></i> Download Template X/Y
                            </a>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Upload File Excel (.xlsx)</label>
                            <input type="file" name="file" class="form-control rounded-3 border-light-subtle" required accept=".xlsx, .xls, .csv">
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                            <i class="fas fa-upload me-1"></i> Import Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal Import Indikator -->
    <div class="modal fade" id="modalImportIndikator" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Import Indikator</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('indikator.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="alert alert-info border-0 rounded-4 shadow-sm mb-4">
                            <div class="small fw-bold"><i class="fas fa-info-circle me-1"></i> Silakan unduh template Excel terlebih dahulu, lalu isi data Indikator Kinerja.</div>
                        </div>
                        <div class="mb-4 text-center">
                            <a href="{{ route('indikator.template') }}" class="btn btn-outline-success rounded-pill px-4 fw-bold">
                                <i class="fas fa-download me-1"></i> Download Template Indikator
                            </a>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Upload File Excel (.xlsx)</label>
                            <input type="file" name="file" class="form-control rounded-3 border-light-subtle" required accept=".xlsx, .xls, .csv">
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                            <i class="fas fa-upload me-1"></i> Import Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('#indikatorTable').DataTable({
                language: window.DATATABLES_ID,
                order: [],
                columnDefs: [
                    { orderable: false, targets: 0 }
                ]
            });

            // Toggle XY Rows based on Tipe
            $('#tipe').on('change', function() {
                if($(this).val() === '%') {
                    $('.xy-row').show();
                } else {
                    $('.xy-row').hide();
                }
            });

            // Reset Modal when hidden
            $('#modalIndikator').on('hidden.bs.modal', function () {
                $('#formIndikator')[0].reset();
                $('#formMethod').val('POST');
                $('#indikator_id').val('');
                $('#modalTitle').text('Tambah Indikator Kinerja Baru');
                $('#meta-tab').tab('show'); // Reset to first tab
                $('#manageTabsContent').scrollTop(0);
                $('.xy-row').hide();
            });

            // Reset scroll when switching tabs
            $('#manageTabs').on('shown.bs.tab', function () {
                $('#manageTabsContent').animate({ scrollTop: 0 }, 200);
            });

            // Open Modal for Add
            $('[data-bs-target="#modalIndikator"]').on('click', function() {
                $('#formIndikator')[0].reset();
                $('#formMethod').val('POST');
                $('#indikator_id').val('');
                $('#modalTitle').text('Tambah Indikator Kinerja Baru');
                $('.xy-row').hide();
            });

            // Open Modal for Edit
            $(document).on('click', '.manage-indikator', function () {
                const id = $(this).data('id');
                const kode = $(this).data('kode');
                
                $('#indikator_id').val(kode); // Use kode because model binding getRouteKeyName() uses 'kode'
                $('#formMethod').val('PUT');
                $('#modalTitle').text('Edit Indikator Kinerja');
                
                $('#modalIndikator').modal('show');

                // Load Metadata and Kumulatif Data
                $.get(`{{ url('indikator') }}/${kode}`, function (data) {
                    $('#kode').val(data.kode);
                    $('#kode_tujuan').val(data.kode_tujuan || '');
                    $('#kode_sasaran').val(data.kode_sasaran || '');
                    $('#kode_indikator_kinerja').val(data.kode_indikator_kinerja || '');
                    $('#sasaran').val(data.sasaran);
                    $('#indikator_kinerja').val(data.indikator_kinerja);
                    $('#jenis_indikator').val(data.jenis_indikator);
                    $('#periode').val(data.periode);
                    $('#tipe').val(data.tipe).trigger('change');
                    $('#satuan').val(data.satuan);
                    $('#target_tahunan').val(data.target_tahunan);
                    $('#tahun').val(data.tahun);
                    $('#pic_id').val(data.pic_id).trigger('change');
                    
                    $('#dasar_hitung').val(data.dasar_hitung || '');
                    $('#definisi_x').val(data.definisi_x || '');
                    $('#definisi_y').val(data.definisi_y || '');
                    $('#target_tahunan_x').val(data.target_tahunan_x || '');
                    $('#target_tahunan_y').val(data.target_tahunan_y || '');

                    // Target
                    if (data.target) {
                        $('#target_tw1').val(data.target.target_tw1);
                        $('#target_tw2').val(data.target.target_tw2);
                        $('#target_tw3').val(data.target.target_tw3);
                        $('#target_tw4').val(data.target.target_tw4);
                        
                        $('#target_x_tw1').val(data.target.target_x_tw1);
                        $('#target_x_tw2').val(data.target.target_x_tw2);
                        $('#target_x_tw3').val(data.target.target_x_tw3);
                        $('#target_x_tw4').val(data.target.target_x_tw4);
                        
                        $('#target_y_tw1').val(data.target.target_y_tw1);
                        $('#target_y_tw2').val(data.target.target_y_tw2);
                        $('#target_y_tw3').val(data.target.target_y_tw3);
                        $('#target_y_tw4').val(data.target.target_y_tw4);
                    }

                    // Realisasi (array of realisasis)
                    if (data.realisasis && data.realisasis.length > 0) {
                        data.realisasis.forEach(r => {
                            $(`#realisasi_tw${r.triwulan}`).val(r.realisasi_kumulatif);
                            $(`#realisasi_x_tw${r.triwulan}`).val(r.realisasi_x);
                            $(`#realisasi_y_tw${r.triwulan}`).val(r.realisasi_y);
                        });
                    }
                });
            });

            // Form Submit: Add & Edit
            $('#formIndikator').on('submit', function (e) {
                e.preventDefault();
                const btn = $('#btnSimpanBaru');
                const method = $('#formMethod').val();
                const routeKey = $('#indikator_id').val();
                const url = method === 'PUT' ? `{{ url('indikator') }}/${routeKey}` : `{{ route('indikator.store') }}`;
                
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...');

                $.ajax({
                    url: url,
                    type: method === 'PUT' ? 'PUT' : 'POST',
                    data: $(this).serialize(),
                    success: function (res) {
                        if (res.status === 'success') {
                            window.showToast('success', res.message);
                            $('#modalIndikator').modal('hide');
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            window.showToast('error', res.message || 'Terjadi kesalahan.');
                            btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Simpan Indikator');
                        }
                    },
                    error: function (xhr) {
                        btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Simpan Indikator');
                        let err = 'Terjadi kesalahan sistem.';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            err = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            err = xhr.responseJSON.message;
                        }
                        window.showToast('error', err);
                    }
                });
            });

            // Wizard Logic
            let currentStep = 1;

            $('#btnNextStep').click(function() {
                // Basic HTML5 validation for Step 1
                const form = $('#formIndikator')[0];
                let step1Valid = true;
                $('#meta [required]').each(function() {
                    if (!this.checkValidity()) {
                        step1Valid = false;
                        this.reportValidity();
                        return false; // break loop
                    }
                });

                if (step1Valid) {
                    currentStep = 2;
                    updateWizard();
                }
            });

            $('#btnPrevStep').click(function() {
                currentStep = 1;
                updateWizard();
            });

            $('.step-indikator-clickable').click(function() {
                const targetStep = parseInt($(this).data('step'));
                
                if (currentStep === 1 && targetStep === 2) {
                    let step1Valid = true;
                    $('#meta [required]').each(function() {
                        if (!this.checkValidity()) {
                            step1Valid = false;
                            this.reportValidity();
                            return false;
                        }
                    });
                    if (!step1Valid) return;
                }
                
                currentStep = targetStep;
                updateWizard();
            });

            function updateWizard() {
                if (currentStep === 1) {
                    $('#meta').show();
                    $('#target').hide();
                    $('#btnPrevStep').hide();
                    $('#btnNextStep').show();
                    
                    $('#indicator1').removeClass('inactive').addClass('active');
                    $('#label1').removeClass('text-muted').addClass('text-primary');
                    
                    $('#indicator2').removeClass('active').addClass('inactive');
                    $('#label2').removeClass('text-primary').addClass('text-muted');
                    
                    $('#wizardProgress').css('width', '0%');
                } else {
                    $('#meta').hide();
                    $('#target').show();
                    $('#btnPrevStep').show();
                    $('#btnNextStep').hide();
                    
                    $('#indicator2').removeClass('inactive').addClass('active');
                    $('#label2').removeClass('text-muted').addClass('text-primary');
                    
                    $('#wizardProgress').css('width', '100%');
                }
            }

            // Reset wizard when modal is opened for 'Tambah'
            $('#btnTambah').click(function () {
                $('#modalTitle').text('Tambah Indikator Kinerja Baru');
                $('#formMethod').val('POST');
                $('#indikator_id').val('');
                $('#formIndikator')[0].reset();
                $('#pic_id').val('').trigger('change');
                $('.xy-row').hide();
                currentStep = 1;
                updateWizard();
                $('#modalIndikator').modal('show');
            });

            // Modify Edit modal opening to reset wizard
            $(document).on('click', '.edit-indikator', function () {
                // ... (Existing edit code is below, but we need to reset wizard)
                currentStep = 1;
                updateWizard();
                // ...
            });
            // End Wizard Logic

            // Delete Indikator
            $(document).on('click', '.delete-indikator', function() {
                const id = $(this).data('id');
                const kode = $(this).data('kode');
                const btn = $(this);
                
                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: `Yakin ingin menghapus Indikator ${kode}?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-trash me-1"></i> Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const originalHtml = btn.html();
                        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                        $.ajax({
                            url: `{{ url('indikator') }}/${kode}`,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function (res) {
                                if (res.status === 'success') {
                                    window.showToast('success', res.message);
                                    $(`#row-${id}`).fadeOut(300, function() { $(this).remove(); });
                                }
                            },
                            error: function (xhr) {
                                btn.prop('disabled', false).html(originalHtml);
                                window.showToast('error', xhr.responseJSON?.message || 'Terjadi kesalahan');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
