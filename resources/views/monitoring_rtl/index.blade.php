@extends('layouts.dashboard')

@section('title', 'Dashboard Tindak Lanjut')

@section('content')


    <!-- Filter Card -->
    <div class="card mb-4 shadow-sm border-0 rounded-4">
        <div class="card-body">
            <form action="{{ route('monitoring-rtl.index') }}" method="GET" class="d-flex gap-3 align-items-end flex-wrap">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div>
                    <label class="form-label text-muted small fw-bold mb-1">Tahun</label>
                    <select name="tahun" class="form-select" style="width: 120px;" onchange="this.form.submit()">
                        @php $currentYear = date('Y'); @endphp
                        @for($i = $currentYear - 2; $i <= $currentYear + 2; $i++)
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
            </form>
        </div>
    </div>

    <!-- Tab Filters -->
    <ul class="nav nav-pills mb-4 gap-2 border-bottom pb-3">
        <li class="nav-item">
            <a class="nav-link rounded-pill {{ $tab == 'semua' ? 'active' : 'bg-light text-dark' }}" href="{{ route('monitoring-rtl.index', ['tab' => 'semua', 'tahun' => $tahun, 'triwulan' => $triwulan]) }}">
                Semua <span class="badge bg-secondary ms-1">{{ $counts['semua'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill {{ $tab == 'belum_tindak_lanjut' ? 'active bg-primary' : 'bg-light text-dark' }}" href="{{ route('monitoring-rtl.index', ['tab' => 'belum_tindak_lanjut', 'tahun' => $tahun, 'triwulan' => $triwulan]) }}">
                Belum Ada Tindak Lanjut <span class="badge bg-primary ms-1">{{ $counts['belum_tindak_lanjut'] }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link rounded-pill {{ $tab == 'sudah_tindak_lanjut' ? 'active bg-success' : 'bg-light text-dark' }}" href="{{ route('monitoring-rtl.index', ['tab' => 'sudah_tindak_lanjut', 'tahun' => $tahun, 'triwulan' => $triwulan]) }}">
                Sudah Ada Tindak Lanjut <span class="badge bg-success ms-1">{{ $counts['sudah_tindak_lanjut'] }}</span>
            </a>
        </li>
    </ul>

    <div class="alert alert-info border-0 shadow-sm rounded-4 small mb-4">
        <i class="fas fa-info-circle me-1"></i> Menampilkan daftar RTL yang disusun pada <strong>Triwulan {{ $target_triwulan }} Tahun {{ $target_tahun }}</strong> untuk dieksekusi pada <strong>Triwulan {{ $triwulan }} Tahun {{ $tahun }}</strong>.
    </div>

    <!-- Table View -->
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="monitoringRtlTable">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 text-center" style="width: 5%;">No</th>
                            <th class="py-3" style="width: 35%;">Kendala & Indikator Kinerja</th>
                            <th class="py-3" style="width: 35%;">Rencana Tindak Lanjut</th>
                            <th class="py-3" style="width: 12%;">Batas Waktu</th>
                            <th class="py-3 text-center" style="width: 13%;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rtls as $index => $rtl)
                            @php
                                $isOverdue = $rtl->batas_waktu < date('Y-m-d') && $rtl->status == 'Belum Ditindak Lanjut';
                            @endphp
                            <tr class="{{ $isOverdue ? 'bg-danger bg-opacity-10' : '' }}">
                                <td class="text-muted text-center">{{ $index + 1 }}</td>
                                <td>
                                    <div class="small text-dark mb-2" style="white-space: pre-line;">{{ $rtl->kendala }}</div>
                                    <div class="small fw-bold text-primary"><i class="fas fa-award me-1"></i> {{ $rtl->indikator->kode ?? '-' }}</div>
                                </td>
                                <td>
                                    <div class="small text-dark fw-bold" style="white-space: pre-line;">{{ $rtl->rtl }}</div>
                                </td>
                                <td>
                                    <div class="small fw-bold {{ $isOverdue ? 'text-danger' : '' }}">
                                        {{ \Carbon\Carbon::parse($rtl->batas_waktu)->format('d M Y') }}
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($rtl->status == 'Sudah Ditindak Lanjut')
                                        <span class="badge bg-success px-2 py-1">Sudah Ada Tindak Lanjut</span>
                                    @else
                                        <span class="badge {{ $isOverdue ? 'bg-danger' : 'bg-primary' }} px-2 py-1">Belum Ada Tindak Lanjut</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <img src="https://illustrations.popsy.co/gray/success.svg" alt="empty" width="120" class="mb-3 opacity-50">
                                        <h5 class="fw-bold">Tidak ada RTL yang ditemukan</h5>
                                        <p>Semua beres! Tidak ada Rencana Tindak Lanjut pada kategori ini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Eksekusi RTL -->
    <div class="modal fade" id="modalEksekusi" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form action="" method="POST" id="formEksekusi" enctype="multipart/form-data" class="modal-content border-0 shadow-lg rounded-4">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Eksekusi Tindak Lanjut</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-3 mb-4 border border-primary-subtle">
                        <div class="fw-bold text-primary mb-1">Tugas Anda:</div>
                        <div class="text-dark small fw-bold mb-2" id="e_deskripsi"></div>
                        <div class="text-danger extra-small fw-bold"><i class="fas fa-clock me-1"></i> Tenggat: <span id="e_due"></span></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Catatan Pelaksanaan (Progres) <span class="text-danger">*</span></label>
                        <textarea name="catatan_progres" class="form-control rounded-3" rows="3" placeholder="Jelaskan apa yang sudah Anda kerjakan untuk menyelesaikan RTL ini..." required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Upload Dokumen Bukti Dukung (PDF/JPG)</label>
                        <input type="file" name="file_bukti_dukung" class="form-control rounded-3" accept=".pdf,.jpg,.jpeg,.png">
                        <div class="form-text extra-small">Maksimal 5MB.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Kirim untuk Verifikasi</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#monitoringRtlTable').DataTable({
            language: window.DATATABLES_ID,
            pageLength: 5,
            lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Semua"]],
            order: [], // Disable initial sort
            columnDefs: [
                { orderable: false, targets: 0 }
            ]
        });

        // Use event delegation for .btn-eksekusi to work properly with DataTables pagination
        $(document).on('click', '.btn-eksekusi', function() {
            const id = $(this).data('id');
            const deskripsi = $(this).data('deskripsi');
            const due = $(this).data('due');
            
            $('#e_deskripsi').text(deskripsi);
            $('#e_due').text(due);
            
            // Set form action dynamically
            $('#formEksekusi').attr('action', '{{ url("monitoring-rtl") }}/' + id + '/eksekusi');
            
            $('#modalEksekusi').modal('show');
        });
    });
</script>
@endsection
