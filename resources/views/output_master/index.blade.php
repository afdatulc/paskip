@extends('layouts.dashboard')

@section('title', 'Master Output')

@section('content')
<div class="card border-0 shadow-sm rounded-4 text-dark">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
            @if((auth()->user()->isAdmin() || auth()->user()->isPic()) && $indikators->count() > 0)
                <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalOutput">
                    <i class="fas fa-plus me-1"></i> Tambah Output
                </button>
            @else
                <div class="fw-bold text-dark"><i class="fas fa-boxes text-primary me-2"></i> Daftar Tanggung Jawab Output</div>
            @endif
        </div>
        
        @if(auth()->user()->isAdmin() || auth()->user()->isPic())
            <button type="button" class="btn btn-success rounded-pill px-3 fw-bold" data-bs-toggle="modal" data-bs-target="#modalImport">
                <i class="fas fa-upload me-1"></i> Import Output
            </button>
        @endif
    </div>
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover align-middle" id="outputTable">
                <thead class="table-light">
                    <tr>
                        <th width="40" class="text-center">No</th>
                        <th width="120">Kode Indikator</th>
                        <th>Nama Output</th>
                        <th width="150">Jenis Output</th>
                        <th width="150">Periode</th>
                        @if(auth()->user()->isAdmin() || auth()->user()->isPic())
                            <th width="100" class="text-center pe-3">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($outputs as $output)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>
                                @if($output->indikator)
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-2">
                                        {{ $output->indikator->kode }}
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill px-2">N/A</span>
                                @endif
                            </td>
                            <td><div class="fw-bold text-dark small">{{ $output->nama_output }}</div></td>
                            <td>
                                <span class="badge {{ $output->jenis_output == 'Laporan' ? 'bg-info text-dark' : 'bg-success' }} bg-opacity-25 rounded-pill px-2">
                                    {{ $output->jenis_output }}
                                </span>
                            </td>
                            <td>{{ $output->periode }}</td>
                            @if(auth()->user()->isAdmin() || (auth()->user()->isPic() && $output->indikator && $output->indikator->pic_id == auth()->user()->pegawai_id))
                                <td class="text-center pe-3">
                                    <button type="button" class="btn btn-sm btn-outline-warning rounded-circle shadow-sm me-1 btn-edit" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalEdit"
                                        data-id="{{ $output->id }}"
                                        data-indikator="{{ $output->indikator_id }}"
                                        data-nama="{{ $output->nama_output }}"
                                        data-jenis="{{ $output->jenis_output }}"
                                        data-periode="{{ $output->periode }}"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('output-master.destroy', $output->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus output ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle shadow-sm" title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            @elseif(auth()->user()->isPic() || auth()->user()->isAnggota())
                                <td class="text-center pe-3">-</td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ (auth()->user()->isAdmin() || auth()->user()->isPic()) ? 6 : 5 }}" class="text-center py-4 text-muted">
                                <i class="fas fa-box-open fs-2 mb-3 text-secondary opacity-50 d-block"></i>
                                Belum ada data Output Master
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Import -->
<div class="modal fade" id="modalImport" tabindex="-1" aria-labelledby="modalImportLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('output-master.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header bg-success text-white rounded-top-4">
                    <h5 class="modal-title fw-bold" id="modalImportLabel"><i class="fas fa-upload me-2"></i> Import Excel Output</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 shadow-sm rounded-3">
                        <i class="fas fa-info-circle me-2"></i> <strong>Format Excel:</strong><br>
                        Pastikan kolom Excel (baris 1) memiliki urutan/nama persis berikut:
                        <ul class="mb-0 mt-2 small">
                            <li><strong>kode_indikator</strong> (sesuai kode di Master Indikator)</li>
                            <li><strong>nama_output</strong> (Deskripsi/Nama Output)</li>
                            <li><strong>jenis_output</strong> (Laporan / Publikasi)</li>
                            <li><strong>periode</strong> (Tahunan / Triwulanan / Bulanan)</li>
                        </ul>
                    </div>
                    <div class="mb-3">
                        <label for="file" class="form-label fw-bold">Pilih File Excel (.xlsx, .csv)</label>
                        <input class="form-control form-control-lg bg-light" type="file" id="file" name="file" accept=".xlsx, .xls, .csv" required>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm">Upload & Import</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Output -->
<div class="modal fade" id="modalOutput" tabindex="-1" aria-labelledby="modalOutputLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('output-master.store') }}" method="POST" id="formAdd">
            @csrf
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header bg-white border-bottom-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold text-dark" id="modalOutputLabel">Tambah Master Output</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pt-3">
                    <div class="mb-3">
                        <label for="indikator_id" class="form-label fw-bold text-dark small">Indikator Terkait</label>
                        <select class="form-select bg-white border-light-subtle rounded-3" id="indikator_id" name="indikator_id" required>
                            <option value="">-- Pilih Indikator --</option>
                            @foreach($indikators as $ind)
                                <option value="{{ $ind->id }}">{{ $ind->kode }} - {{ Str::limit($ind->indikator_kinerja, 50) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="nama_output" class="form-label fw-bold text-dark small">Nama Output</label>
                        <input type="text" class="form-control bg-white border-light-subtle rounded-3" id="nama_output" name="nama_output" required placeholder="Contoh: Laporan Kinerja...">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="jenis_output" class="form-label fw-bold text-dark small">Jenis Output</label>
                            <select class="form-select bg-white border-light-subtle rounded-3" id="jenis_output" name="jenis_output" required>
                                <option value="Laporan">Laporan</option>
                                <option value="Publikasi">Publikasi</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="periode" class="form-label fw-bold text-dark small">Periode</label>
                            <select class="form-select bg-white border-light-subtle rounded-3" id="periode" name="periode" required>
                                <option value="Tahunan">Tahunan</option>
                                <option value="Triwulanan">Triwulanan</option>
                                <option value="Bulanan">Bulanan</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top-0 rounded-bottom-4 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm" style="background-color: #4361ee; border-color: #4361ee;">Simpan Output</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="" method="POST" id="formEdit">
            @csrf
            @method('PUT')
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header bg-white border-bottom-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold text-dark" id="modalEditLabel">Edit Master Output</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pt-3">
                    <div class="mb-3">
                        <label for="edit_indikator_id" class="form-label fw-bold text-dark small">Indikator Terkait</label>
                        <select class="form-select bg-white border-light-subtle rounded-3" id="edit_indikator_id" name="indikator_id" required>
                            @foreach($indikators as $ind)
                                <option value="{{ $ind->id }}">{{ $ind->kode }} - {{ Str::limit($ind->indikator_kinerja, 50) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_nama_output" class="form-label fw-bold text-dark small">Nama Output</label>
                        <input type="text" class="form-control bg-white border-light-subtle rounded-3" id="edit_nama_output" name="nama_output" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_jenis_output" class="form-label fw-bold text-dark small">Jenis Output</label>
                            <select class="form-select bg-white border-light-subtle rounded-3" id="edit_jenis_output" name="jenis_output" required>
                                <option value="Laporan">Laporan</option>
                                <option value="Publikasi">Publikasi</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_periode" class="form-label fw-bold text-dark small">Periode</label>
                            <select class="form-select bg-white border-light-subtle rounded-3" id="edit_periode" name="periode" required>
                                <option value="Tahunan">Tahunan</option>
                                <option value="Triwulanan">Triwulanan</option>
                                <option value="Bulanan">Bulanan</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white border-top-0 rounded-bottom-4 px-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm" style="background-color: #4361ee; border-color: #4361ee;">Simpan Output</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#outputTable').DataTable({
            language: window.DATATABLES_ID,
            order: [],
            columnDefs: [
                { orderable: false, targets: 0 }
            ]
        });

        const formEdit = document.getElementById('formEdit');
        const selectIndikator = document.getElementById('edit_indikator_id');
        const inputNamaOutput = document.getElementById('edit_nama_output');
        const selectJenisOutput = document.getElementById('edit_jenis_output');
        const selectPeriode = document.getElementById('edit_periode');

        $(document).on('click', '.btn-edit', function() {
            const id = $(this).attr('data-id');
            const indikator = $(this).attr('data-indikator');
            const nama = $(this).attr('data-nama');
            const jenis = $(this).attr('data-jenis');
            const periode = $(this).attr('data-periode');

            formEdit.action = `/output-master/${id}`;
            selectIndikator.value = indikator;
            inputNamaOutput.value = nama;
            selectJenisOutput.value = jenis;
            selectPeriode.value = periode;
        });
    });
</script>
@endsection
