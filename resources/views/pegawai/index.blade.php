@extends('layouts.dashboard')

@section('title', 'Master Pegawai')

@section('content')
    <div class="card border-0 shadow-sm rounded-4 text-dark">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" data-bs-toggle="modal"
                    data-bs-target="#modalPegawai">
                    <i class="fas fa-plus me-1"></i> Tambah Pegawai
                </button>
                <button type="button" class="btn btn-info rounded-pill px-3 ms-2 fw-bold text-white" id="btnSyncApi">
                    <i class="fas fa-sync-alt me-1"></i> Sync IPIN
                </button>
            </div>
            <button type="button" class="btn btn-success rounded-pill px-4 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalImportPegawai">
                <i class="fas fa-upload me-1"></i> Import Pegawai
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle" id="pegawaiTable">
                    <thead class="table-light">
                        <tr>
                            <th width="50">No</th>
                            <th width="150">NIP / Email BPS</th>
                            <th>Nama Lengkap</th>
                            <th width="120">Kontak</th>
                            <th width="150">Seksi / Status</th>
                            <th>Jabatan & Unit Kerja</th>
                            <th width="120" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pegawais as $p)
                            <tr id="row-{{ $p->id }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div class="small fw-bold text-dark">{{ $p->nip }}</div>
                                    <div class="extra-small text-muted">{{ $p->email_bps ?: '-' }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark mb-1">{{ $p->nama }}</div>
                                    @if($p->user)
                                        <span
                                            class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill extra-small"><i
                                                class="fas fa-check-circle me-1"></i>Akun Aktif</span>
                                    @else
                                        <span
                                            class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill extra-small"><i
                                                class="fas fa-clock me-1"></i>Menunggu Aktivasi</span>
                                    @endif
                                </td>
                                <td>
                                    @if($p->no_hp)
                                        @php
                                            $waNumber = preg_replace('/[^0-9]/', '', $p->no_hp);
                                            if (str_starts_with($waNumber, '0')) {
                                                $waNumber = '62' . substr($waNumber, 1);
                                            }
                                        @endphp
                                        <div class="small fw-bold text-dark">
                                            <a href="https://wa.me/{{ $waNumber }}" target="_blank" class="text-decoration-none">
                                                <i class="fab fa-whatsapp me-1 text-success"></i>{{ $p->no_hp }}
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span
                                        class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-2 mb-1">{{ $p->seksi }}</span>
                                    <div class="extra-small text-muted">{{ $p->status }}</div>
                                </td>
                                <td>
                                    <div class="small text-dark fw-bold">{{ $p->jabatan ?: '-' }}</div>
                                    <div class="extra-small text-muted">{{ $p->pangkat_golongan ?: '-' }} &bull;
                                        {{ $p->unit_kerja ?: '-' }}</div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center gap-1">
                                        @if(!$p->user)
                                            <button
                                                class="btn btn-sm btn-outline-success rounded-3 activate-pegawai d-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px;" data-id="{{ $p->id }}" title="Aktifkan Akun">
                                                <i class="fas fa-user-plus"></i>
                                            </button>
                                        @endif
                                        <button
                                            class="btn btn-sm btn-outline-primary rounded-3 edit-pegawai d-flex align-items-center justify-content-center"
                                            style="width: 32px; height: 32px;" data-id="{{ $p->id }}" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button
                                            class="btn btn-sm btn-outline-danger rounded-3 delete-pegawai d-flex align-items-center justify-content-center"
                                            style="width: 32px; height: 32px;" data-id="{{ $p->id }}" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Import Pegawai -->
    <div class="modal fade" id="modalImportPegawai" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Import Data Pegawai</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('pegawai.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="alert alert-info border-0 rounded-4 shadow-sm mb-4">
                            <div class="small fw-bold"><i class="fas fa-info-circle me-1"></i> Silakan unduh template Excel terlebih dahulu, lalu isi data pegawai.</div>
                        </div>
                        <div class="mb-4 text-center">
                            <a href="{{ route('pegawai.template') }}" class="btn btn-outline-success rounded-pill px-4 fw-bold">
                                <i class="fas fa-download me-1"></i> Download Template Pegawai
                            </a>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small">Upload File Excel (.xlsx)</label>
                            <input type="file" name="file" class="form-control rounded-3 border-light-subtle" required accept=".xlsx, .xls, .csv">
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm fw-bold">
                            <i class="fas fa-upload me-1"></i> Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Tambah/Edit Pegawai -->
    <div class="modal fade" id="modalPegawai" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="modalTitle">Tambah Pegawai Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formPegawai">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <input type="hidden" id="pegawai_id">
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">NIP</label>
                                <input type="text" name="nip" id="nip"
                                    class="form-control rounded-3 shadow-none border-light-subtle" required
                                    placeholder="NIP 18 digit">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Email BPS</label>
                                <input type="email" name="email_bps" id="email_bps"
                                    class="form-control rounded-3 shadow-none border-light-subtle"
                                    placeholder="username@bps.go.id">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small">Nama Lengkap</label>
                                <input type="text" name="nama" id="nama"
                                    class="form-control rounded-3 shadow-none border-light-subtle" required
                                    placeholder="Nama lengkap dengan gelar">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Nomor HP / WhatsApp</label>
                                <input type="text" name="no_hp" id="no_hp"
                                    class="form-control rounded-3 shadow-none border-light-subtle"
                                    placeholder="08xxxxxxxxxx">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Status</label>
                                <select name="status" id="status"
                                    class="form-select rounded-3 shadow-none border-light-subtle" required>
                                    <option value="PNS">PNS</option>
                                    <option value="PPPK">PPPK</option>
                                    <option value="Outsourcing">Outsourcing</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small">Seksi / Tim</label>
                                <select name="seksi" id="seksi"
                                    class="form-select rounded-3 shadow-none border-light-subtle" required>
                                    <option value="Sosial">Sosial</option>
                                    <option value="Produksi">Produksi</option>
                                    <option value="Distribusi">Distribusi</option>
                                    <option value="Nerwilis">Nerwilis</option>
                                    <option value="IPDS">IPDS</option>
                                    <option value="Umum">Umum</option>
                                    <option value="Kepala">Kepala</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small">Jabatan</label>
                                <input type="text" name="jabatan" id="jabatan"
                                    class="form-control rounded-3 shadow-none border-light-subtle"
                                    placeholder="Contoh: Statistisi Ahli Pertama">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small">Pangkat / Golongan</label>
                                <input type="text" name="pangkat_golongan" id="pangkat_golongan"
                                    class="form-control rounded-3 shadow-none border-light-subtle"
                                    placeholder="Contoh: Penata Muda Tk. I (III/b)">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small">Unit Kerja</label>
                                <input type="text" name="unit_kerja" id="unit_kerja"
                                    class="form-control rounded-3 shadow-none border-light-subtle"
                                    placeholder="Contoh: BPS Kabupaten Tapin">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm" id="btnSimpan">Simpan
                            Pegawai</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('#pegawaiTable').DataTable({
                language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json' }
            });

            // Reset Modal on Close
            $('#modalPegawai').on('hidden.bs.modal', function () {
                $('#formPegawai')[0].reset();
                $('#modalTitle').text('Tambah Pegawai Baru');
                $('#formMethod').val('POST');
                $('#pegawai_id').val('');
            });

            // Edit Button Click
            $(document).on('click', '.edit-pegawai', function () {
                const id = $(this).data('id');
                $('#modalTitle').text('Edit Data Pegawai');
                $('#formMethod').val('PUT');
                $('#pegawai_id').val(id);
                $('#modalPegawai').modal('show');

                $.get(`{{ url('pegawai') }}/${id}`, function (data) {
                    $('#nip').val(data.nip);
                    $('#nama').val(data.nama);
                    $('#email_bps').val(data.email_bps);
                    $('#no_hp').val(data.no_hp);
                    $('#jabatan').val(data.jabatan);
                    $('#pangkat_golongan').val(data.pangkat_golongan);
                    $('#unit_kerja').val(data.unit_kerja);
                    $('#status').val(data.status);
                    $('#seksi').val(data.seksi);
                });
            });

            // Form Submit
            $('#formPegawai').on('submit', function (e) {
                e.preventDefault();
                const id = $('#pegawai_id').val();
                const method = $('#formMethod').val();
                const url = method === 'POST' ? "{{ route('pegawai.store') }}" : `{{ url('pegawai') }}/${id}`;
                const btn = $('#btnSimpan');

                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function (response) {
                        toastr.success(response.message);
                        $('#modalPegawai').modal('hide');

                        if (method === 'PUT') {
                            const data = response.data;
                            const row = $(`#row-${id}`);

                            // Update Columns Manually
                            row.find('td:nth-child(2)').html(`
                                <div class="small fw-bold text-dark">${data.nip}</div>
                                <div class="extra-small text-muted">${data.email_bps || '-'}</div>
                            `);
                            row.find('td:nth-child(3)').find('.fw-bold').text(data.nama);

                            let waLink = '-';
                            if (data.no_hp) {
                                let waNum = data.no_hp.replace(/[^0-9]/g, '');
                                if (waNum.startsWith('0')) waNum = '62' + waNum.substring(1);
                                waLink = `<div class="small fw-bold text-dark">
                                    <a href="https://wa.me/${waNum}" target="_blank" class="text-decoration-none">
                                        <i class="fab fa-whatsapp me-1 text-success"></i>${data.no_hp}
                                    </a>
                                </div>`;
                            }
                            row.find('td:nth-child(4)').html(waLink);
                            row.find('td:nth-child(5)').html(`
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-2 mb-1">${data.seksi}</span>
                                <div class="extra-small text-muted">${data.status}</div>
                            `);
                            row.find('td:nth-child(6)').html(`
                                <div class="small text-dark fw-bold">${data.jabatan || '-'}</div>
                                <div class="extra-small text-muted">${data.pangkat_golongan || '-'} &bull; ${data.unit_kerja || '-'}</div>
                            `);

                            // Invalidate and draw (keep paging)
                            const table = $('#pegawaiTable').DataTable();
                            table.row(row).invalidate().draw(false);
                        } else {
                            setTimeout(() => location.reload(), 1000);
                        }
                    },
                    error: function (xhr) {
                        btn.prop('disabled', false).html('Simpan Pegawai');
                        const errors = xhr.responseJSON.errors;
                        if (errors) {
                            Object.values(errors).forEach(err => toastr.error(err[0]));
                        } else {
                            toastr.error('Terjadi kesalahan saat menyimpan data.');
                        }
                    }
                });
            });

            // Activate Account Click
            $(document).on('click', '.activate-pegawai', function () {
                const id = $(this).data('id');
                const btn = $(this);

                Swal.fire({
                    title: 'Konfirmasi',
                    text: "Aktifkan akun user untuk pegawai ini?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Aktifkan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

                        $.ajax({
                            url: `{{ url('pegawai') }}/${id}/activate`,
                            method: 'POST',
                            data: { _token: "{{ csrf_token() }}" },
                            success: function (response) {
                                toastr.success(response.message);
                                setTimeout(() => location.reload(), 1500);
                            },
                            error: function (xhr) {
                                btn.prop('disabled', false).html('<i class="fas fa-user-plus"></i>');
                                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal mengaktifkan akun.';
                                toastr.error(msg);
                            }
                        });
                    }
                });
            });

            // Delete Button Click
            $(document).on('click', '.delete-pegawai', function () {
                const id = $(this).data('id');
                const row = $(`#row-${id}`);

                Swal.fire({
                    title: 'Konfirmasi Hapus',
                    text: "Hapus data pegawai ini?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url('pegawai') }}/${id}`,
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
                                toastr.error('Gagal menghapus data.');
                            }
                        });
                    }
                });
            });

            // Sync API Button Click
            $('#btnSyncApi').on('click', function () {
                const btn = $(this);
                const originalContent = btn.html();

                Swal.fire({
                    title: 'Konfirmasi Sinkronisasi',
                    text: "Tarik data master pegawai terbaru dari API IPIN Tapin?",
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Tarik Data',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Syncing...');

                        $.ajax({
                            url: "{{ route('pegawai.sync-api') }}",
                            method: 'POST',
                            data: { _token: "{{ csrf_token() }}" },
                            success: function (response) {
                                toastr.success(response.message);
                                setTimeout(() => location.reload(), 2000);
                            },
                            error: function (xhr) {
                                btn.prop('disabled', false).html(originalContent);
                                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal sinkronisasi data.';
                                toastr.error(msg);
                            }
                        });
                    }
                });
            });
        });
    </script>
    <style>
        .extra-small {
            font-size: 0.7rem;
        }
    </style>
@endsection