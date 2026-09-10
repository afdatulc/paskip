@extends('layouts.dashboard')

@section('title', auth()->user()->isAdmin() ? 'Master Kegiatan' : 'Daftar Tanggung Jawab Kegiatan')

@section('content')
    <div class="card border-0 shadow-sm rounded-4 text-dark">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                @if(auth()->user()->isAdmin() || $indikators->count() > 0)
                    <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" data-bs-toggle="modal"
                        data-bs-target="#modalKegiatan">
                        <i class="fas fa-plus me-1"></i> Tambah Kegiatan
                    </button>

                @else
                    <div class="fw-bold text-dark"><i class="fas fa-tasks me-2 text-primary"></i> Daftar Tanggung Jawab Kegiatan & Tim Anda
                    </div>
                @endif
            </div>
            @if(auth()->user()->isAdmin())
                <button type="button" class="btn btn-success rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalImportKegiatan">
                    <i class="fas fa-upload me-1"></i> Import Kegiatan
                </button>
            @endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="kegiatanTable">
                    <thead class="table-light">
                        <tr>
                            <th width="40" class="text-center">No</th>
                            <th width="90">Kode IKU</th>
                            <th style="min-width: 200px;">Nama Kegiatan</th>
                            <th width="180">Ketua Tim</th>
                            <th>Milestone Pekerjaan</th>
                            <th width="110" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($kegiatans as $k)
                            <tr id="row-{{ $k->id }}">
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td class="small fw-bold text-primary">{{ $k->indikator->kode ?: '-' }}</td>
                                <td class="fw-bold text-dark">{{ $k->nama_kegiatan }}</td>
                                <td>
                                    @if($k->ketuaTim)
                                        <div class="small fw-bold text-dark">{{ $k->ketuaTim->nama }}</div>
                                    @else
                                        <span class="text-muted small italic">- Belum ditunjuk -</span>
                                    @endif
                                </td>
                                <td>
                                    @foreach($k->tahapan_json as $t)
                                        <span
                                            class="badge bg-light text-primary border border-primary-subtle rounded-pill small px-2 py-1 mb-1 me-1">{{ $t }}</span>
                                    @endforeach
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center gap-1">
                                        @php
                                            $canEdit = auth()->user()->isAdmin() || 
                                                       (auth()->user()->pegawai_id == $k->indikator->pic_id) || 
                                                       (auth()->user()->pegawai_id == $k->ketua_tim_id);
                                        @endphp
                                        @if($canEdit)
                                            <button class="btn btn-sm btn-primary rounded-3 edit-kegiatan d-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px;"
                                                data-id="{{ $k->id }}" title="Kelola Kegiatan">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            @if(auth()->user()->isAdmin())
                                                <button class="btn btn-sm btn-outline-danger rounded-3 delete-kegiatan d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px;"
                                                    data-id="{{ $k->id }}" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
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

    <!-- Modal Unified Manage Kegiatan -->
    <div class="modal fade" id="modalKegiatan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalTitle">Kelola Kegiatan Master</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formKegiatan">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <input type="hidden" id="kegiatan_id">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold small">Indikator Terkait</label>
                                <select name="indikator_id" id="indikator_id" class="form-select rounded-3 shadow-none border-light-subtle" required>
                                    <option value="">-- Pilih Indikator --</option>
                                    @foreach($indikators as $i)
                                        <option value="{{ $i->id }}">{{ $i->kode }} - {{ $i->indikator_kinerja }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label class="form-label fw-bold small">Nama Kegiatan Master</label>
                                <input type="text" name="nama_kegiatan" id="nama_kegiatan" class="form-control rounded-3 shadow-none border-light-subtle" placeholder="Contoh: Pengumpulan Data Lapangan" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Ketua Tim Kegiatan</label>
                                <select name="ketua_tim_id" id="ketua_tim_id" class="form-select rounded-3">
                                    <option value="">-- Pilih Ketua Tim --</option>
                                    @foreach($pegawais as $p)
                                        <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->nip }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Anggota Tim</label>
                                <select name="anggotas[]" id="select_anggotas" class="form-select rounded-3" multiple="multiple">
                                    @foreach($pegawais as $p)
                                        <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->nip }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 mt-4">
                                <label class="form-label fw-bold small d-flex justify-content-between">
                                    Tahapan Kegiatan
                                    <button type="button" class="btn btn-xs btn-primary rounded-pill py-0 px-2" style="font-size: 0.65rem;" onclick="addTahapanRow()">
                                        <i class="fas fa-plus me-1"></i> Tambah Tahapan
                                    </button>
                                </label>
                                <div id="tahapan-container" class="mt-2 border rounded-4 p-3 bg-light bg-opacity-50">
                                    <!-- Baris tahapan -->
                                </div>
                                <small class="text-muted extra-small mt-1"><i class="fas fa-info-circle me-1"></i>Tahapan ini akan muncul sebagai progres yang bisa dipilih oleh petugas.</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm" id="btnSimpan"><i class="fas fa-save me-1"></i> Simpan Kegiatan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function addTahapanRow(value = '') {
            const container = $('#tahapan-container');
            const html = `
                    <div class="input-group input-group-sm mb-2 shadow-sm">
                        <input type="text" name="tahapan[]" class="form-control rounded-start-3 border-0" value="${value}" placeholder="Nama tahapan..." required>
                        <button type="button" class="btn btn-white text-danger border-0 rounded-end-3" onclick="$(this).closest('.input-group').remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
            container.append(html);
        }

        $(document).ready(function () {
            $('#kegiatanTable').DataTable({
                language: window.DATATABLES_ID,
                order: [],
                columnDefs: [
                    { orderable: false, targets: 0 }
                ]
            });

            // Initialize Select2
            function initSelect2() {
                $('#indikator_id, #ketua_tim_id, #select_anggotas').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    dropdownParent: $('#modalKegiatan')
                });
            }

            // Reset Modal on Close
            $('#modalKegiatan').on('hidden.bs.modal', function () {
                $('#formKegiatan')[0].reset();
                $('#modalTitle').text('Tambah Kegiatan Master');
                $('#formMethod').val('POST');
                $('#kegiatan_id').val('');
                $('#tahapan-container').empty();
                $('#indikator_id, #ketua_tim_id, #select_anggotas').val(null).trigger('change');
            });

            $('#modalKegiatan').on('shown.bs.modal', function () {
                if ($('#tahapan-container').is(':empty')) {
                    addTahapanRow();
                }
                initSelect2();
            });

            // Edit Button Click
            $(document).on('click', '.edit-kegiatan', function () {
                const id = $(this).data('id');
                $('#modalTitle').text('Kelola Kegiatan Master');
                $('#formMethod').val('PUT');
                $('#kegiatan_id').val(id);
                $('#tahapan-container').empty();
                
                $.get(`{{ url('kegiatan-master') }}/${id}`, function (data) {
                    $('#indikator_id').val(data.indikator_id).trigger('change');
                    $('#nama_kegiatan').val(data.nama_kegiatan);
                    $('#ketua_tim_id').val(data.ketua_tim_id).trigger('change');
                    
                    if (data.anggotas) {
                        const memberIds = data.anggotas.map(a => a.id);
                        $('#select_anggotas').val(memberIds).trigger('change');
                    }

                    if (data.tahapan_json) {
                        data.tahapan_json.forEach(t => addTahapanRow(t));
                    }
                    $('#modalKegiatan').modal('show');
                });
            });

            // Form Submit
            $('#formKegiatan').on('submit', function (e) {
                e.preventDefault();
                const id = $('#kegiatan_id').val();
                const method = $('#formMethod').val();
                const url = method === 'POST' ? "{{ route('kegiatan-master.store') }}" : `{{ url('kegiatan-master') }}/${id}`;
                const btn = $('#btnSimpan');

                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function (response) {
                        toastr.success(response.message);
                        $('#modalKegiatan').modal('hide');
                        setTimeout(() => location.reload(), 500);
                    },
                    error: function (xhr) {
                        btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Simpan Kegiatan');
                        const errors = xhr.responseJSON?.errors;
                        if (errors) Object.values(errors).forEach(err => toastr.error(err[0]));
                    }
                });
            });

            // Delete Button Click
            $(document).on('click', '.delete-kegiatan', function () {
                const id = $(this).data('id');
                const btn = $(this);
                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: 'Yakin ingin menghapus kegiatan ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-trash me-1"></i> Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
                        $.ajax({
                            url: `{{ url('kegiatan-master') }}/${id}`,
                            method: 'POST',
                            data: { _token: "{{ csrf_token() }}", _method: 'DELETE' },
                            success: function(res) {
                                if (res.status === 'success' || res.message) {
                                    toastr.success(res.message || 'Berhasil dihapus');
                                    $(`#row-${id}`).fadeOut(300, function() { $(this).remove(); });
                                }
                            },
                            error: function(xhr) {
                                btn.prop('disabled', false).html('<i class="fas fa-trash"></i>');
                                toastr.error('Gagal menghapus data.');
                            }
                        });
                    }
                });
            });
        });
    </script>
    <style>
        .btn-xs { padding: 0.1rem 0.4rem; font-size: 0.75rem; }
        .select2-container--bootstrap-5 .select2-selection {
            border-radius: 0.75rem;
            border-color: #dee2e6;
        }
    </style>
    <!-- Modal Import Kegiatan -->
    <div class="modal fade" id="modalImportKegiatan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Import Data Kegiatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('kegiatan-master.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="alert alert-info border-0 rounded-4 shadow-sm mb-4">
                            <div class="small fw-bold"><i class="fas fa-info-circle me-1"></i> Silakan unduh template Excel terlebih dahulu, lalu isi data Master Kegiatan.</div>
                        </div>
                        <div class="mb-4 text-center">
                            <a href="{{ route('kegiatan-master.template') }}" class="btn btn-outline-success rounded-pill px-4 fw-bold">
                                <i class="fas fa-download me-1"></i> Download Template Kegiatan
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
