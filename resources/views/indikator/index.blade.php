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
                    <a href="{{ route('indikator.template') }}" class="btn btn-outline-success rounded-pill px-3 ms-2 fw-bold">
                        <i class="fas fa-download me-1"></i> Template
                    </a>
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
                <form action="{{ route('indikator.import') }}" method="POST" enctype="multipart/form-data"
                    class="d-flex align-items-center">
                    @csrf
                    <div class="input-group input-group-sm">
                        <input type="file" name="file" class="form-control rounded-start-pill border-success"
                            style="width: 250px;" required>
                        <button type="submit" class="btn btn-success rounded-end-pill px-3">
                            <i class="fas fa-upload me-1"></i> Import
                        </button>
                    </div>
                </form>
            @endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="indikatorTable">
                    <thead class="table-light">
                        <tr>
                            <th width="50">No</th>
                            <th width="100">Kode</th>
                            <th>Sasaran & Indikator Kinerja</th>
                            <th width="120">Jenis / Periode</th>
                            <th width="80" class="text-center">Kegiatan</th>
                            <th width="100" class="text-center">Output</th>
                            <th width="120">Tipe / Satuan</th>
                            <th width="100">Target</th>
                            <th width="150">PIC</th>
                            <th width="120" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($indikators as $i)
                            <tr id="row-{{ $i->id }}">
                                <td>{{ $loop->iteration }}</td>
                                <td class="small fw-bold text-primary">{{ $i->kode ?: '-' }}</td>
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
                                        {{ $i->kegiatan_masters_count }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @php
                                        $progress = $i->output_progress;
                                        $isDone = $progress !== '-' && explode('/', $progress)[0] === explode('/', $progress)[1];
                                    @endphp
                                    <span class="badge bg-{{ $isDone ? 'success' : 'secondary' }} bg-opacity-10 text-{{ $isDone ? 'success' : 'secondary' }} border border-{{ $isDone ? 'success' : 'secondary' }}-subtle rounded-pill px-2">
                                        {{ $progress }}
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
                                        <div class="extra-small text-muted">{{ $i->pic->nip }}</div>
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

    <!-- Modal Unified Manage Indikator -->
    <div class="modal fade" id="modalManageIndikator" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Kelola Indikator Kinerja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <!-- Segmented Control Navigation -->
                    <div class="px-4 pt-4">
                        <div class="bg-light p-1 rounded-4 d-flex">
                            <ul class="nav nav-pills nav-fill w-100" id="manageTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active fw-bold small py-2 rounded-3" id="meta-tab" data-bs-toggle="tab" data-bs-target="#meta" type="button" role="tab"><i class="fas fa-info-circle me-1"></i> Metadata</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link fw-bold small py-2 rounded-3" id="target-tab" data-bs-toggle="tab" data-bs-target="#target" type="button" role="tab"><i class="fas fa-bullseye me-1"></i> Target TW</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link fw-bold small py-2 rounded-3" id="tautan-tab" data-bs-toggle="tab" data-bs-target="#tautan" type="button" role="tab"><i class="fas fa-link me-1"></i> Tautan & Basis Data</button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="tab-content p-4" id="manageTabsContent">
                        <!-- Tab 1: Metadata -->
                        <div class="tab-pane fade show active" id="meta" role="tabpanel">
                            <form id="formIndikator">
                                @csrf
                                <input type="hidden" name="_method" id="formMethod" value="POST">
                                <input type="hidden" id="indikator_id">
                                <div class="row g-2">
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small">Kode Route (Key)</label>
                                        <input type="text" name="kode" id="kode" class="form-control form-control-sm rounded-3 shadow-none border-light-subtle" placeholder="Contoh: 1.1.1.1" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small">Kode Tujuan</label>
                                        <input type="text" name="kode_tujuan" id="kode_tujuan" class="form-control form-control-sm rounded-3 shadow-none border-light-subtle" placeholder="Contoh: T1">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small">Kode Sasaran</label>
                                        <input type="text" name="kode_sasaran" id="kode_sasaran" class="form-control form-control-sm rounded-3 shadow-none border-light-subtle" placeholder="Contoh: 1.1.1">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label fw-bold small">Kode Indikator Kinerja</label>
                                        <input type="text" name="kode_indikator_kinerja" id="kode_indikator_kinerja" class="form-control form-control-sm rounded-3 shadow-none border-light-subtle" placeholder="Contoh: 1.1.1.1">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Jenis</label>
                                        <select name="jenis_indikator" id="jenis_indikator" class="form-select form-select-sm rounded-3 shadow-none border-light-subtle" required>
                                            <option value="" disabled selected>-- Pilih Jenis --</option>
                                            <option value="IKU">IKU</option>
                                            <option value="Proksi">Proksi</option>
                                            <option value="IK">IK</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small">Tahun</label>
                                        <input type="number" name="tahun" id="tahun" class="form-control form-control-sm rounded-3 shadow-none border-light-subtle" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold small">Sasaran</label>
                                        <input type="text" name="sasaran" id="sasaran" class="form-control form-control-sm rounded-3 shadow-none border-light-subtle" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-bold small">Indikator Kinerja</label>
                                        <input type="text" name="indikator_kinerja" id="indikator_kinerja" class="form-control form-control-sm rounded-3 shadow-none border-light-subtle" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Periode</label>
                                        <select name="periode" id="periode" class="form-select form-select-sm rounded-3 shadow-none border-light-subtle">
                                            <option value="" disabled selected>-- Pilih Periode --</option>
                                            <option value="Tahunan">Tahunan</option>
                                            <option value="Bulanan">Bulanan</option>
                                            <option value="Triwulanan">Triwulanan</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Tipe</label>
                                        <select name="tipe" id="tipe" class="form-select form-select-sm rounded-3 shadow-none border-light-subtle">
                                            <option value="" disabled selected>-- Pilih Tipe --</option>
                                            <option value="%">%</option>
                                            <option value="Non %">Non %</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Satuan</label>
                                        <input type="text" name="satuan" id="satuan" class="form-control form-control-sm rounded-3 shadow-none border-light-subtle" placeholder="Persen">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-bold small">Target Tahunan</label>
                                        <input type="number" step="0.01" name="target_tahunan" id="target_tahunan" class="form-control form-control-sm rounded-3 shadow-none border-light-subtle">
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label fw-bold small">Penanggung Jawab (PIC)</label>
                                        <select name="pic_id" id="pic_id" class="form-select form-select-sm rounded-3 shadow-none border-light-subtle" {{ !auth()->user()->isAdmin() ? 'disabled' : '' }}>
                                            <option value="">-- Tanpa PIC --</option>
                                            @foreach($pegawais as $p)
                                                <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->nip }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    {{-- Definisi X & Y --}}
                                    <div class="col-12">
                                        <div class="p-3 rounded-3 border border-secondary-subtle bg-light">
                                            <div class="small fw-bold text-dark mb-2">
                                                <i class="fas fa-calculator me-1"></i> Formula Dasar Hitung (Opsional)
                                            </div>
                                            <div class="form-text text-muted mb-2">
                                                Definisikan variabel X (pembilang) dan Y (penyebut) untuk formula capaian.
                                                Capaian = (X / Y) &times; 100%
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold small">
                                                        <span class="badge bg-secondary text-white me-1">X</span>
                                                        Deskripsi Pembilang (X)
                                                    </label>
                                                    <input type="text" name="definisi_x" id="definisi_x"
                                                        class="form-control form-control-sm rounded-3 shadow-none border-light-subtle"
                                                        placeholder="Misal: Jumlah Publikasi yang berkualitas">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold small">
                                                        <span class="badge bg-secondary text-white me-1">Y</span>
                                                        Deskripsi Penyebut (Y)
                                                    </label>
                                                    <input type="text" name="definisi_y" id="definisi_y"
                                                        class="form-control form-control-sm rounded-3 shadow-none border-light-subtle"
                                                        placeholder="Misal: Jumlah seluruh Publikasi yang dihasilkan">
                                                </div>
                                            </div>
                                            <div class="row g-2 mt-2">
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold small">
                                                        <span class="badge bg-secondary text-white me-1">X</span>
                                                        Target Tahunan Pembilang (X)
                                                    </label>
                                                    <input type="number" step="0.01" name="target_tahunan_x" id="target_tahunan_x"
                                                        class="form-control form-control-sm rounded-3 shadow-none border-light-subtle">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label fw-bold small">
                                                        <span class="badge bg-secondary text-white me-1">Y</span>
                                                        Target Tahunan Penyebut (Y)
                                                    </label>
                                                    <input type="number" step="0.01" name="target_tahunan_y" id="target_tahunan_y"
                                                        class="form-control form-control-sm rounded-3 shadow-none border-light-subtle">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 text-end">
                                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm" id="btnSimpan"><i class="fas fa-save me-1"></i> Simpan Metadata</button>
                                </div>
                            </form>
                        </div>

                        <!-- Tab 2: Target Triwulanan -->
                        <div class="tab-pane fade" id="target" role="tabpanel">
                            <form id="formTarget" class="d-flex flex-column" style="min-height: 380px;">
                                @csrf
                                <input type="hidden" name="_method" value="PUT">
                                <input type="hidden" id="target_indikator_id">
                                <div class="flex-grow-1">
                                    <div class="alert alert-info border-0 rounded-4 shadow-sm mb-4">
                                        <div class="small fw-bold"><i class="fas fa-info-circle me-1"></i> Atur target kumulatif untuk masing-masing triwulan.</div>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small">Target TW I</label>
                                            <input type="number" step="0.01" name="target_tw1" id="target_tw1" class="form-control rounded-3 border-light-subtle">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small">Target TW II</label>
                                            <input type="number" step="0.01" name="target_tw2" id="target_tw2" class="form-control rounded-3 border-light-subtle">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small">Target TW III</label>
                                            <input type="number" step="0.01" name="target_tw3" id="target_tw3" class="form-control rounded-3 border-light-subtle">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-bold small">Target TW IV</label>
                                            <input type="number" step="0.01" name="target_tw4" id="target_tw4" class="form-control rounded-3 border-light-subtle">
                                        </div>
                                    </div>
                                    
                                    {{-- Target X dan Y (Akan di-toggle via JS jika ada definisi X/Y) --}}
                                    <div id="targetXYSection" class="mt-4 pt-3 border-top" style="display: none;">
                                        <div class="small fw-bold text-primary mb-3">
                                            <i class="fas fa-calculator me-1"></i> Target X & Y (Berdasarkan Definisi Dasar Hitung)
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="p-2 border rounded-3 bg-light">
                                                    <label class="form-label fw-bold small text-primary mb-2">
                                                        <span class="badge bg-primary text-white me-1">X</span> Target TW I - IV
                                                    </label>
                                                    <input type="number" step="0.01" name="target_x_tw1" id="target_x_tw1" class="form-control form-control-sm mb-2" placeholder="Target X TW I">
                                                    <input type="number" step="0.01" name="target_x_tw2" id="target_x_tw2" class="form-control form-control-sm mb-2" placeholder="Target X TW II">
                                                    <input type="number" step="0.01" name="target_x_tw3" id="target_x_tw3" class="form-control form-control-sm mb-2" placeholder="Target X TW III">
                                                    <input type="number" step="0.01" name="target_x_tw4" id="target_x_tw4" class="form-control form-control-sm" placeholder="Target X TW IV">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="p-2 border rounded-3 bg-light">
                                                    <label class="form-label fw-bold small text-secondary mb-2">
                                                        <span class="badge bg-secondary text-white me-1">Y</span> Target TW I - IV
                                                    </label>
                                                    <input type="number" step="0.01" name="target_y_tw1" id="target_y_tw1" class="form-control form-control-sm mb-2" placeholder="Target Y TW I">
                                                    <input type="number" step="0.01" name="target_y_tw2" id="target_y_tw2" class="form-control form-control-sm mb-2" placeholder="Target Y TW II">
                                                    <input type="number" step="0.01" name="target_y_tw3" id="target_y_tw3" class="form-control form-control-sm mb-2" placeholder="Target Y TW III">
                                                    <input type="number" step="0.01" name="target_y_tw4" id="target_y_tw4" class="form-control form-control-sm" placeholder="Target Y TW IV">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 text-end">
                                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm" id="btnSimpanTarget"><i class="fas fa-save me-1"></i> Simpan Target</button>
                                </div>
                            </form>
                        </div>

                        <!-- Tab 3: Tautan & Basis Data -->
                        <div class="tab-pane fade" id="tautan" role="tabpanel">
                            <form id="formTautan">
                                @csrf
                                <input type="hidden" id="tautan_kode">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label fw-bold small">Dasar Hitung & Basis Data Realisasi IKU</label>
                                        <textarea name="dasar_hitung" id="tautan_dasar_hitung" class="form-control rounded-3 shadow-none border-light-subtle tinymce-editor" rows="3" placeholder="Jelaskan dasar perhitungan..."></textarea>
                                        <div class="form-text text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Untuk konten lebih lengkap (foto, rumus), gunakan editor di halaman
                                            <a href="javascript:void(0)" id="linkKeBukti" class="text-primary fw-bold">Input Realisasi &rarr; Tab Dasar Hitung</a>.
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 text-end">
                                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm" id="btnSimpanTautan"><i class="fas fa-save me-1"></i> Simpan Tautan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Tambah Indikator -->
    <div class="modal fade" id="modalIndikator" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Tambah Indikator Kinerja Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formTambahIndikator">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Kode Route (Key)</label>
                                <input type="text" name="kode" class="form-control rounded-3 border-light-subtle" placeholder="1.1.1.1" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Kode Tujuan</label>
                                <input type="text" name="kode_tujuan" class="form-control rounded-3 border-light-subtle" placeholder="T1">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Kode Sasaran</label>
                                <input type="text" name="kode_sasaran" class="form-control rounded-3 border-light-subtle" placeholder="1.1.1">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Kode Indikator Kinerja</label>
                                <input type="text" name="kode_indikator_kinerja" class="form-control rounded-3 border-light-subtle" placeholder="1.1.1.1">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Jenis</label>
                                <select name="jenis_indikator" class="form-select rounded-3" required>
                                    <option value="" disabled selected>-- Pilih jenis indikator --</option>
                                    <option value="IKU">IKU</option>
                                    <option value="Proksi">Proksi</option>
                                    <option value="IK">IK</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Tahun</label>
                                <input type="number" name="tahun" value="{{ date('Y') }}" class="form-control rounded-3" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small">Sasaran</label>
                                <input type="text" name="sasaran" class="form-control rounded-3" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small">Indikator Kinerja</label>
                                <input type="text" name="indikator_kinerja" class="form-control rounded-3" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small">Penanggung Jawab (PIC)</label>
                                <select name="pic_id" class="form-select rounded-3" required>
                                    <option value="" disabled selected>-- Pilih PIC --</option>
                                    @foreach($pegawais as $p)
                                        <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->nip }})</option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- Definisi X & Y --}}
                            <div class="col-12">
                                <div class="p-3 rounded-3 border border-secondary-subtle bg-light">
                                    <div class="small fw-bold text-dark mb-1">
                                        <i class="fas fa-calculator me-1"></i> Formula Dasar Hitung <span class="fw-normal text-muted">(opsional, dapat diisi nanti)</span>
                                    </div>
                                    <div class="row g-2 mt-1">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">
                                                <span class="badge bg-secondary text-white me-1">X</span>
                                                Deskripsi Pembilang
                                            </label>
                                            <input type="text" name="definisi_x" class="form-control form-control-sm rounded-3"
                                                placeholder="Misal: Jumlah Publikasi berkualitas">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">
                                                <span class="badge bg-secondary text-white me-1">Y</span>
                                                Deskripsi Penyebut
                                            </label>
                                            <input type="text" name="definisi_y" class="form-control form-control-sm rounded-3"
                                                placeholder="Misal: Jumlah seluruh Publikasi">
                                        </div>
                                    </div>
                                    <div class="row g-2 mt-2">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">
                                                Target Tahunan Pembilang (X)
                                            </label>
                                            <input type="number" step="0.01" name="target_tahunan_x" class="form-control form-control-sm rounded-3">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold">
                                                Target Tahunan Penyebut (Y)
                                            </label>
                                            <input type="number" step="0.01" name="target_tahunan_y" class="form-control form-control-sm rounded-3">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4" id="btnSimpanBaru">Simpan</button>
                    </div>
                </form>
            </div>
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
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('#indikatorTable').DataTable({
                language: window.DATATABLES_ID
            });

            // Unified Manage Modal Reset
            $('#modalManageIndikator').on('hidden.bs.modal', function () {
                $('#formIndikator')[0].reset();
                $('#formTarget')[0].reset();
                $('#formTautan')[0].reset();
                tinymce.get('tautan_dasar_hitung')?.setContent('');
                $('#meta-tab').tab('show'); // Reset to first tab
                $('#manageTabsContent').scrollTop(0);
            });

            // Reset scroll when switching tabs
            $('#manageTabs').on('shown.bs.tab', function () {
                $('#manageTabsContent').animate({ scrollTop: 0 }, 200);
            });

            $('#modalManageIndikator').on('shown.bs.modal', function () {
                if (!tinymce.get('tautan_dasar_hitung')) {
                    window.initTinyMCE('#tautan_dasar_hitung');
                }
            });

            // Open Unified Manage Modal
            $(document).on('click', '.manage-indikator', function () {
                const id = $(this).data('id');
                const kode = $(this).data('kode');
                
                $('#indikator_id').val(id);
                $('#target_indikator_id').val(id);
                $('#tautan_kode').val(kode);
                $('#formIndikator').data('kode', kode);
                
                $('#modalManageIndikator').modal('show');

                // Load Data
                $.get(`{{ url('indikator') }}/${kode}`, function (data) {
                    // Fill Metadata
                    $('#kode').val(data.kode);
                    $('#kode_tujuan').val(data.kode_tujuan || '');
                    $('#kode_sasaran').val(data.kode_sasaran || '');
                    $('#kode_indikator_kinerja').val(data.kode_indikator_kinerja || '');
                    $('#sasaran').val(data.sasaran);
                    $('#indikator_kinerja').val(data.indikator_kinerja);
                    $('#jenis_indikator').val(data.jenis_indikator);
                    $('#periode').val(data.periode);
                    $('#tipe').val(data.tipe);
                    $('#satuan').val(data.satuan);
                    $('#target_tahunan').val(data.target_tahunan);
                    $('#tahun').val(data.tahun);
                    $('#pic_id').val(data.pic_id).trigger('change');
                    // Fill Definisi X/Y
                    $('#definisi_x').val(data.definisi_x || '');
                    $('#definisi_y').val(data.definisi_y || '');
                    $('#target_tahunan_x').val(data.target_tahunan_x || '');
                    $('#target_tahunan_y').val(data.target_tahunan_y || '');

                    // Fill Tautan
                    $('#tautan_dasar_hitung').val(data.dasar_hitung || '');
                    tinymce.get('tautan_dasar_hitung')?.setContent(data.dasar_hitung || '');

                    // Update link ke halaman entry realisasi
                    $('#linkKeBukti').attr('href', `{{ url('realisasi/entry') }}/${data.kode}`);
                    
                    // Kondisi Show/Hide Target X/Y
                    if (data.definisi_x || data.definisi_y) {
                        $('#targetXYSection').slideDown();
                    } else {
                        $('#targetXYSection').hide();
                    }
                });

                // Load Target Data via separate show route
                $.get(`{{ url('target') }}/${id}`, function (data) {
                    $('#target_tw1').val(data.target_tw1);
                    $('#target_tw2').val(data.target_tw2);
                    $('#target_tw3').val(data.target_tw3);
                    $('#target_tw4').val(data.target_tw4);
                    
                    $('#target_x_tw1').val(data.target_x_tw1);
                    $('#target_x_tw2').val(data.target_x_tw2);
                    $('#target_x_tw3').val(data.target_x_tw3);
                    $('#target_x_tw4').val(data.target_x_tw4);
                    
                    $('#target_y_tw1').val(data.target_y_tw1);
                    $('#target_y_tw2').val(data.target_y_tw2);
                    $('#target_y_tw3').val(data.target_y_tw3);
                    $('#target_y_tw4').val(data.target_y_tw4);
                });
            });

            // Form Submit: Metadata
            $('#formIndikator').on('submit', function (e) {
                e.preventDefault();
                const id = $('#indikator_id').val();
                const kode = $(this).data('kode');
                const btn = $('#btnSimpan');

                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

                $.ajax({
                    url: `{{ url('indikator') }}/${kode}`,
                    method: 'POST',
                    data: $(this).serialize() + '&_method=PUT',
                    success: function (response) {
                        toastr.success('Metadata berhasil diperbarui');
                        btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Simpan Metadata');
                        // Update table row if needed...
                    },
                    error: function (xhr) {
                        btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Simpan Metadata');
                        toastr.error('Gagal menyimpan metadata.');
                    }
                });
            });

            // Form Submit: Target
            $('#formTarget').on('submit', function (e) {
                e.preventDefault();
                const id = $('#target_indikator_id').val();
                const btn = $('#btnSimpanTarget');

                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

                $.ajax({
                    url: `{{ url('target') }}/${id}`,
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function (response) {
                        toastr.success(response.message);
                        btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Simpan Target');
                    },
                    error: function (xhr) {
                        btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Simpan Target');
                        toastr.error('Gagal menyimpan target.');
                    }
                });
            });

            // Form Submit: Tautan
            $('#formTautan').on('submit', function (e) {
                e.preventDefault();
                const kode = $('#tautan_kode').val();
                const btn = $('#btnSimpanTautan');

                tinymce.triggerSave();
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

                $.ajax({
                    url: `{{ url('indikator') }}/${kode}/tautan`,
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function (response) {
                        toastr.success(response.message);
                        btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Simpan Tautan');
                    },
                    error: function (xhr) {
                        btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Simpan Tautan');
                        toastr.error('Gagal menyimpan tautan.');
                    }
                });
            });

            // Form Submit: Tambah Baru
            $('#formTambahIndikator').on('submit', function (e) {
                e.preventDefault();
                const btn = $('#btnSimpanBaru');
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

                $.ajax({
                    url: "{{ route('indikator.store') }}",
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function (response) {
                        toastr.success(response.message);
                        setTimeout(() => location.reload(), 1000);
                    },
                    error: function (xhr) {
                        btn.prop('disabled', false).html('Simpan');
                        const errors = xhr.responseJSON?.errors;
                        if (errors) Object.values(errors).forEach(err => toastr.error(err[0]));
                    }
                });
            });

            // Delete Button Click
            $(document).on('click', '.delete-indikator', function () {
                if (!confirm('Hapus indikator ini?')) return;
                const id = $(this).data('id');
                const kode = $(this).data('kode');
                const row = $(`#row-${id}`);

                $.ajax({
                    url: `{{ url('indikator') }}/${kode}`,
                    method: 'POST',
                    data: { _token: "{{ csrf_token() }}", _method: 'DELETE' },
                    success: function (response) {
                        toastr.success(response.message);
                        row.fadeOut(function () { $(this).remove(); });
                    },
                    error: function () {
                        toastr.error('Gagal menghapus data.');
                    }
                });
            });
        });
    </script>
    <style>
        .table-hover tbody tr:hover {
            background-color: rgba(67, 97, 238, 0.02);
        }
        .nav-pills .nav-link {
            color: #6c757d;
            border: none;
            transition: all 0.2s;
        }
        .nav-pills .nav-link.active {
            color: #fff;
            background-color: var(--bs-primary);
            box-shadow: 0 4px 10px rgba(67, 97, 238, 0.2);
        }
        .nav-pills .nav-link:not(.active):hover {
            background-color: rgba(0,0,0,0.05);
            color: var(--bs-primary);
        }
        .extra-small { font-size: 0.7rem; }

        #manageTabsContent {
            max-height: 550px;
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: thin;
            scrollbar-color: #dee2e6 transparent;
            transition: all 0.3s ease-in-out;
        }

        #manageTabsContent::-webkit-scrollbar {
            width: 6px;
        }

        #manageTabsContent::-webkit-scrollbar-track {
            background: transparent;
        }

        #manageTabsContent::-webkit-scrollbar-thumb {
            background-color: #dee2e6;
            border-radius: 10px;
        }
    </style>
@endsection