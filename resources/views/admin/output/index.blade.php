@extends('layouts.dashboard')

@section('title', auth()->user()->isAdmin() ? 'Master Output' : 'Daftar Tanggung Jawab Output')

@section('content')
    <div class="card border-0 shadow-sm rounded-4 text-dark">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                @if(auth()->user()->isAdmin() || $indikators->count() > 0)
                    <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" data-bs-toggle="modal"
                        data-bs-target="#modalOutput">
                        <i class="fas fa-plus me-1"></i> Tambah Output
                    </button>

                @else
                    <div class="fw-bold text-dark"><i class="fas fa-box-archive me-2 text-primary"></i> Daftar Tanggung Jawab Output</div>
                @endif
            </div>
            @if(auth()->user()->isAdmin())
                <button type="button" class="btn btn-success rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalImportOutput">
                    <i class="fas fa-upload me-1"></i> Import Output
                </button>
            @endif
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="outputTable">
                    <thead class="table-light">
                        <tr>
                            <th width="40" class="text-center">No</th>
                            <th width="90">Kode IKU</th>
                            <th style="min-width: 200px;">Nama Output</th>
                            <th width="150">Jenis Output</th>
                            <th width="120">Periode</th>
                            <th width="130">Dokumen</th>
                            <th width="110" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($outputs as $o)
                            <tr id="row-{{ $o->id }}">
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td class="small fw-bold text-primary">{{ $o->indikator->kode ?: '-' }}</td>
                                <td class="fw-bold text-dark">{{ $o->nama_output }}</td>
                                <td>
                                    <span class="badge bg-{{ $o->jenis_output == 'Laporan' ? 'info' : 'success' }} bg-opacity-10 text-{{ $o->jenis_output == 'Laporan' ? 'info' : 'success' }} border border-{{ $o->jenis_output == 'Laporan' ? 'info' : 'success' }}-subtle rounded-pill px-3">
                                        {{ $o->jenis_output }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill px-3">
                                        {{ $o->periode }}
                                    </span>
                                </td>
                                <td>
                                    @if($o->file_path)
                                        <a href="javascript:void(0)" onclick="showPreview('{{ asset('storage/' . $o->file_path) }}', '{{ basename($o->file_path) }}')" class="text-primary small text-decoration-none fw-bold">
                                            <i class="fas fa-file-pdf me-1"></i> Lihat Dokumen
                                        </a>
                                    @else
                                        <span class="text-muted extra-small italic">Belum ada</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        @if(auth()->user()->isAdmin() || (auth()->user()->pegawai_id == $o->indikator->pic_id))
                                            <button class="btn btn-sm btn-outline-primary rounded-3 edit-output"
                                                data-id="{{ $o->id }}" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger rounded-3 delete-output"
                                                data-id="{{ $o->id }}" title="Hapus">
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

    <!-- Modal Tambah/Edit Output -->
    <div class="modal fade" id="modalOutput" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalTitle">Tambah Master Output</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formOutput">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <input type="hidden" id="output_id">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Indikator Terkait</label>
                            <select name="indikator_id" id="indikator_id"
                                class="form-select select2 rounded-3 shadow-none border-light-subtle" required>
                                <option value="">-- Pilih Indikator --</option>
                                @foreach($indikators as $i)
                                    <option value="{{ $i->id }}">{{ $i->kode }} - {{ $i->indikator_kinerja }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small">Nama Output</label>
                            <input type="text" name="nama_output" id="nama_output"
                                class="form-control rounded-3 shadow-none border-light-subtle"
                                placeholder="Contoh: Laporan Tahunan Kinerja Satker" required>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small">Jenis Output</label>
                                <select name="jenis_output" id="jenis_output" class="form-select rounded-3 shadow-none border-light-subtle" required>
                                    <option value="Laporan">Laporan</option>
                                    <option value="Publikasi">Publikasi</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small">Periode</label>
                                <select name="periode" id="periode" class="form-select rounded-3 shadow-none border-light-subtle" required>
                                    <option value="Tahunan">Tahunan</option>
                                    <option value="Triwulanan">Triwulanan</option>
                                    <option value="Bulanan">Bulanan</option>
                                </select>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm" id="btnSimpan">Simpan
                            Output</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Preview -->
    <div class="modal fade" id="modalPreview" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold" id="previewTitle">File Preview</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 text-center">
                    <div id="previewContent" style="min-height: 400px; display: flex; align-items: center; justify-content: center;">
                        <!-- Content will be injected here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function showPreview(url, fileName) {
            const ext = fileName.split('.').pop().toLowerCase();
            let html = '';
            
            $('#previewTitle').text(fileName);
            $('#previewContent').html('<div class="spinner-border text-primary"></div>');
            
            if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext)) {
                html = `<img src="${url}" class="img-fluid rounded shadow-sm" style="max-height: 80vh;">`;
            } else if (ext === 'pdf') {
                html = `<iframe src="${url}" width="100%" height="600px" style="border: none; border-radius: 8px;"></iframe>`;
            } else {
                html = `
                    <div class="text-center py-5">
                        <i class="fas fa-file-alt fs-1 text-muted mb-3"></i>
                        <p>Format file <b>.${ext}</b> tidak mendukung preview langsung.</p>
                        <a href="${url}" target="_blank" class="btn btn-primary px-4">Download / Buka File</a>
                    </div>
                `;
            }

            setTimeout(() => {
                $('#previewContent').html(html);
            }, 300);

            const modal = new bootstrap.Modal(document.getElementById('modalPreview'));
            modal.show();
        }

        $(document).ready(function () {
            $('#outputTable').DataTable({
                language: window.DATATABLES_ID
            });

            // Reset Modal on Close
            $('#modalOutput').on('hidden.bs.modal', function () {
                $('#formOutput')[0].reset();
                $('#modalTitle').text('Tambah Master Output');
                $('#formMethod').val('POST');
                $('#output_id').val('');
                $('#indikator_id').val('').trigger('change');
                $('#fileInfo').addClass('d-none');
            });

            // Tambahkan inisialisasi Select2 saat modal dibuka
            $('#modalOutput').on('shown.bs.modal', function () {
                $('#indikator_id').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    dropdownParent: $('#modalOutput')
                });
            });

            // Edit Button Click
            $(document).on('click', '.edit-output', function () {
                const id = $(this).data('id');
                $('#modalTitle').text('Edit Master Output');
                $('#formMethod').val('PUT');
                $('#output_id').val(id);
                $('#modalOutput').modal('show');

                $.get(`{{ url('output-master') }}/${id}`, function (data) {
                    $('#indikator_id').val(data.indikator_id).trigger('change');
                    $('#nama_output').val(data.nama_output);
                    $('#jenis_output').val(data.jenis_output);
                    $('#periode').val(data.periode);
                    
                    if (data.file_path) {
                        $('#currentFileName').text(data.file_path.split('/').pop());
                        $('#fileInfo').removeClass('d-none');
                    } else {
                        $('#fileInfo').addClass('d-none');
                    }
                });
            });

            // Form Submit
            $('#formOutput').on('submit', function (e) {
                e.preventDefault();
                const id = $('#output_id').val();
                const method = $('#formMethod').val();
                const url = method === 'POST' ? "{{ route('output-master.store') }}" : `{{ url('output-master') }}/${id}`;
                const btn = $('#btnSimpan');

                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

                const formData = new FormData(this);

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        toastr.success(response.message);
                        $('#modalOutput').modal('hide');
                        btn.prop('disabled', false).html('Simpan Output');

                        if (method === 'PUT') {
                            const data = response.data;
                            const row = $(`#row-${id}`);

                            const kodeIKU = $('#indikator_id option:selected').text().split(' - ')[0];
                            row.find('td:nth-child(2)').text(kodeIKU);
                            row.find('td:nth-child(3)').text(data.nama_output);
                            
                            const jenisClass = data.jenis_output === 'Laporan' ? 'info' : 'success';
                            row.find('td:nth-child(4)').html(`<span class="badge bg-${jenisClass} bg-opacity-10 text-${jenisClass} border border-${jenisClass}-subtle rounded-pill px-3">${data.jenis_output}</span>`);
                            
                            row.find('td:nth-child(5)').html(`<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill px-3">${data.periode}</span>`);

                            if (data.file_path) {
                                const fileName = data.file_path.split('/').pop();
                                const fileUrl = `{{ asset('storage') }}/${data.file_path}`;
                                row.find('td:nth-child(6)').html(`
                                    <a href="javascript:void(0)" onclick="showPreview('${fileUrl}', '${fileName}')" class="text-primary small text-decoration-none fw-bold">
                                        <i class="fas fa-file-pdf me-1"></i> Lihat Dokumen
                                    </a>
                                `);
                            } else {
                                row.find('td:nth-child(6)').html('<span class="text-muted extra-small italic">Belum ada</span>');
                            }

                            const table = $('#outputTable').DataTable();
                            table.row(row).invalidate().draw(false);
                        } else {
                            setTimeout(() => location.reload(), 1000);
                        }
                    },
                    error: function (xhr) {
                        btn.prop('disabled', false).html('Simpan Output');
                        const errors = xhr.responseJSON.errors;
                        if (errors) {
                            Object.values(errors).forEach(err => toastr.error(err[0]));
                        } else {
                            toastr.error('Terjadi kesalahan saat menyimpan data.');
                        }
                    }
                });
            });

            // Delete Button Click
            $(document).on('click', '.delete-output', function () {
                const id = $(this).data('id');
                const row = $(`#row-${id}`);
                const btn = $(this);
                const originalHtml = btn.html();

                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: 'Yakin ingin menghapus output ini?',
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
                            url: `{{ url('output-master') }}/${id}`,
                            method: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                                _method: 'DELETE'
                            },
                            success: function (response) {
                                toastr.success(response.message);
                                row.fadeOut(function () { $(this).remove(); });
                            },
                            error: function () {
                                btn.prop('disabled', false).html(originalHtml);
                                toastr.error('Gagal menghapus data.');
                            }
                        });
                    }
                });
            });
        });
    </script>
    <!-- Modal Import Output -->
    <div class="modal fade" id="modalImportOutput" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Import Data Output</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('output-master.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="alert alert-info border-0 rounded-4 shadow-sm mb-4">
                            <div class="small fw-bold"><i class="fas fa-info-circle me-1"></i> Silakan unduh template Excel terlebih dahulu, lalu isi data Master Output.</div>
                        </div>
                        <div class="mb-4 text-center">
                            <a href="{{ route('output-master.template') }}" class="btn btn-outline-success rounded-pill px-4 fw-bold">
                                <i class="fas fa-download me-1"></i> Download Template Output
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
