@extends('layouts.dashboard')

@section('title', 'Master Anggaran & Realisasi')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center mb-4 gap-3">
            <!-- <div>
                <h2 class="h3 mb-0 text-gray-800 fw-bold">Master Anggaran & Realisasi</h2>
                <p class="text-muted mb-0">Kelola pagu awal, pagu revisi, dan realisasi anggaran per Indikator Kinerja Utama (IKU)</p>
            </div> -->
            <div class="d-flex flex-wrap align-items-center gap-2">
                <button type="button" class="btn btn-success rounded-pill px-3 fw-bold m-0" data-bs-toggle="modal" data-bs-target="#modalImportAnggaran">
                    <i class="fas fa-upload me-1"></i> Import Anggaran
                </button>
                <form method="GET" action="{{ route('anggaran.index') }}"
                    class="d-flex align-items-center bg-white p-1 ps-3 rounded-pill shadow-sm border border-light-subtle m-0">
                    <label class="me-2 mb-0 small fw-bold text-muted">Tahun:</label>
                    <input type="number" name="tahun" value="{{ $tahun }}"
                        class="form-control form-control-sm border-0 shadow-none text-center fw-bold bg-light text-primary rounded-pill"
                        style="width: 85px;" onchange="this.form.submit()">
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="anggaranTable">
                        <thead class="bg-light text-secondary">
                            <tr>
                                <th class="border-0 px-4 py-3 rounded-top-start-4">Kode IKU</th>
                                <th class="border-0 py-3">Indikator Kinerja</th>
                                <th class="border-0 py-3 text-end">Pagu Awal</th>
                                <th class="border-0 py-3 text-end">Pagu Revisi</th>
                                <th class="border-0 py-3 text-end">Total Realisasi</th>
                                <th class="border-0 px-4 py-3 rounded-top-end-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            @forelse($groupedIndikators as $kode => $group)
                                @php
                                    $sAnggaran = $group['anggaran'];
                                    $paguAwal = floatval($sAnggaran ? $sAnggaran->pagu_awal : 0);
                                    $paguRevisi = floatval($sAnggaran ? $sAnggaran->pagu_revisi : 0);
                                    $realisasiTw1 = floatval($sAnggaran ? $sAnggaran->realisasi_tw1 : 0);
                                    $realisasiTw2 = floatval($sAnggaran ? $sAnggaran->realisasi_tw2 : 0);
                                    $realisasiTw3 = floatval($sAnggaran ? $sAnggaran->realisasi_tw3 : 0);
                                    $realisasiTw4 = floatval($sAnggaran ? $sAnggaran->realisasi_tw4 : 0);
                                    $totalRealisasi = $realisasiTw1 + $realisasiTw2 + $realisasiTw3 + $realisasiTw4;
                                    $persentase = $paguRevisi > 0 ? ($totalRealisasi / $paguRevisi) * 100 : ($paguAwal > 0 ? ($totalRealisasi / $paguAwal) * 100 : 0);
                                @endphp
                                <tr class="bg-light">
                                    <td class="px-4">
                                        <span class="badge bg-dark px-2 py-1">{{ $kode }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">[SASARAN] {{ $group['sasaran'] }}</div>
                                    </td>
                                    <td class="text-end fw-bold">Rp {{ number_format($paguAwal, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold text-warning">Rp {{ number_format($paguRevisi, 0, ',', '.') }}</td>
                                    <td class="text-end">
                                        <div class="fw-bold text-success">Rp {{ number_format($totalRealisasi, 0, ',', '.') }}</div>
                                        <div class="small text-muted mt-1">
                                            <div class="progress" style="height: 4px;">
                                                <div class="progress-bar {{ $persentase > 100 ? 'bg-danger' : 'bg-success' }}" role="progressbar" style="width: {{ min($persentase, 100) }}%"></div>
                                            </div>
                                            <span class="mt-1 d-block">{{ number_format($persentase, 1, ',', '.') }}%</span>
                                        </div>
                                    </td>
                                    <td class="px-4 text-center">
                                        <button type="button" class="btn btn-sm btn-dark rounded-pill btn-edit-sasaran"
                                            data-kode="{{ $kode }}"
                                            data-nama="[SASARAN] {{ $group['sasaran'] }}"
                                            data-awal="{{ floatval($paguAwal) }}" data-revisi="{{ floatval($paguRevisi) }}"
                                            data-tw1="{{ floatval($realisasiTw1) }}" data-tw2="{{ floatval($realisasiTw2) }}"
                                            data-tw3="{{ floatval($realisasiTw3) }}" data-tw4="{{ floatval($realisasiTw4) }}"
                                            data-kode_kegiatan="{{ $sAnggaran ? $sAnggaran->kode_kegiatan : '' }}"
                                            data-nama_kegiatan="{{ $sAnggaran ? $sAnggaran->nama_kegiatan : '' }}"
                                            data-kode_ro="{{ $sAnggaran ? $sAnggaran->kode_ro : '' }}"
                                            data-nama_ro="{{ $sAnggaran ? $sAnggaran->nama_ro : '' }}">
                                            <i class="fas fa-edit me-1"></i> Edit
                                        </button>
                                    </td>
                                </tr>
                                
                                @foreach($group['indikators'] as $indikator)
                                    @php
                                        $anggaran = $indikator->anggarans->first();
                                        $paguAwal = floatval($anggaran ? $anggaran->pagu_awal : 0);
                                        $paguRevisi = floatval($anggaran ? $anggaran->pagu_revisi : 0);
                                        $realisasiTw1 = floatval($anggaran ? $anggaran->realisasi_tw1 : 0);
                                        $realisasiTw2 = floatval($anggaran ? $anggaran->realisasi_tw2 : 0);
                                        $realisasiTw3 = floatval($anggaran ? $anggaran->realisasi_tw3 : 0);
                                        $realisasiTw4 = floatval($anggaran ? $anggaran->realisasi_tw4 : 0);
                                        $totalRealisasi = $realisasiTw1 + $realisasiTw2 + $realisasiTw3 + $realisasiTw4;
                                        $persentase = $paguRevisi > 0 ? ($totalRealisasi / $paguRevisi) * 100 : ($paguAwal > 0 ? ($totalRealisasi / $paguAwal) * 100 : 0);
                                    @endphp
                                    <tr>
                                        <td class="px-4 ps-5">
                                            <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1">{{ $indikator->kode }}</span>
                                        </td>
                                        <td>
                                            <div class="text-dark">{{ $indikator->indikator_kinerja }}</div>
                                        </td>
                                        <td class="text-end fw-semibold">Rp {{ number_format($paguAwal, 0, ',', '.') }}</td>
                                        <td class="text-end fw-semibold text-warning">Rp {{ number_format($paguRevisi, 0, ',', '.') }}</td>
                                        <td class="text-end">
                                            <div class="fw-bold text-success">Rp {{ number_format($totalRealisasi, 0, ',', '.') }}</div>
                                            <div class="small text-muted mt-1">
                                                <div class="progress" style="height: 4px;">
                                                    <div class="progress-bar {{ $persentase > 100 ? 'bg-danger' : 'bg-success' }}" role="progressbar" style="width: {{ min($persentase, 100) }}%"></div>
                                                </div>
                                                <span class="mt-1 d-block">{{ number_format($persentase, 1, ',', '.') }}%</span>
                                            </div>
                                        </td>
                                        <td class="px-4 text-center">
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill btn-edit"
                                                data-id="{{ $indikator->id }}" data-kode="{{ $indikator->kode }}"
                                                data-nama="{{ $indikator->indikator_kinerja }}"
                                                data-awal="{{ floatval($paguAwal) }}" data-revisi="{{ floatval($paguRevisi) }}"
                                                data-tw1="{{ floatval($realisasiTw1) }}" data-tw2="{{ floatval($realisasiTw2) }}"
                                                data-tw3="{{ floatval($realisasiTw3) }}" data-tw4="{{ floatval($realisasiTw4) }}"
                                                data-kode_kegiatan="{{ $anggaran ? $anggaran->kode_kegiatan : '' }}"
                                                data-nama_kegiatan="{{ $anggaran ? $anggaran->nama_kegiatan : '' }}"
                                                data-kode_ro="{{ $anggaran ? $anggaran->kode_ro : '' }}"
                                                data-nama_ro="{{ $anggaran ? $anggaran->nama_ro : '' }}">
                                                <i class="fas fa-edit me-1"></i> Edit
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3 d-block text-light"></i>
                                        Tidak ada data untuk tahun ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Anggaran -->
    <div class="modal fade" id="modalAnggaran" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold">Kelola Anggaran & Realisasi</h5>
                        <div class="text-muted small">Tahun <span class="fw-bold text-dark">{{ $tahun }}</span></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('anggaran.store') }}" method="POST" id="formAnggaran">
                    @csrf
                    <input type="hidden" name="indikator_id" id="modal_indikator_id">
                    <input type="hidden" name="kode" id="modal_kode_input">
                    <input type="hidden" name="tahun" value="{{ $tahun }}">

                    <div class="modal-body p-4">
                        <div class="mb-4 bg-light p-3 rounded-3 border border-light-subtle">
                            <span class="badge bg-primary mb-2" id="modal_kode"></span>
                            <div class="fw-bold small text-dark" id="modal_nama"></div>
                        </div>

                        <div class="col-12 mb-4">
                            <h6 class="fw-bold small text-primary border-bottom pb-2 mb-3">Informasi Kegiatan & RO</h6>
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold small">Kode Kegiatan</label>
                                    <input type="text" name="kode_kegiatan" id="modal_kode_kegiatan" class="form-control rounded-3 shadow-none border-light-subtle">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold small">Nama Kegiatan</label>
                                    <input type="text" name="nama_kegiatan" id="modal_nama_kegiatan" class="form-control rounded-3 shadow-none border-light-subtle">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold small">Kode RO</label>
                                    <input type="text" name="kode_ro" id="modal_kode_ro" class="form-control rounded-3 shadow-none border-light-subtle">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold small">Nama RO</label>
                                    <input type="text" name="nama_ro" id="modal_nama_ro" class="form-control rounded-3 shadow-none border-light-subtle">
                                </div>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <h6 class="fw-bold small text-primary border-bottom pb-2 mb-3">1. Alokasi Pagu (Tahunan)
                                </h6>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Pagu Awal (Rp)</label>
                                    <input type="number" name="pagu_awal" id="modal_pagu_awal"
                                        class="form-control rounded-3 shadow-none border-light-subtle" min="0" step="any">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Pagu Revisi (Rp)</label>
                                    <input type="number" name="pagu_revisi" id="modal_pagu_revisi"
                                        class="form-control rounded-3 shadow-none border-light-subtle" min="0" step="any">
                                    <div class="form-text small">Kosongkan jika tidak ada revisi.</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h6 class="fw-bold small text-primary border-bottom pb-2 mb-3">2. Realisasi (Per Triwulan)
                                </h6>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Realisasi Triwulan I (Rp)</label>
                                    <input type="number" name="realisasi_tw1" id="modal_tw1"
                                        class="form-control rounded-3 shadow-none border-light-subtle" min="0" step="any">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Realisasi Triwulan II (Rp)</label>
                                    <input type="number" name="realisasi_tw2" id="modal_tw2"
                                        class="form-control rounded-3 shadow-none border-light-subtle" min="0" step="any">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Realisasi Triwulan III (Rp)</label>
                                    <input type="number" name="realisasi_tw3" id="modal_tw3"
                                        class="form-control rounded-3 shadow-none border-light-subtle" min="0" step="any">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold small">Realisasi Triwulan IV (Rp)</label>
                                    <input type="number" name="realisasi_tw4" id="modal_tw4"
                                        class="form-control rounded-3 shadow-none border-light-subtle" min="0" step="any">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4 shadow-sm"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm"><i
                                class="fas fa-save me-1"></i> Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            const modal = new bootstrap.Modal(document.getElementById('modalAnggaran'));

            $('.btn-edit').on('click', function () {
                const id = $(this).data('id');
                const kode = $(this).data('kode');
                const nama = $(this).data('nama');
                const awal = $(this).data('awal');
                const revisi = $(this).data('revisi');
                const tw1 = $(this).data('tw1');
                const tw2 = $(this).data('tw2');
                const tw3 = $(this).data('tw3');
                const tw4 = $(this).data('tw4');
                const kode_kegiatan = $(this).data('kode_kegiatan');
                const nama_kegiatan = $(this).data('nama_kegiatan');
                const kode_ro = $(this).data('kode_ro');
                const nama_ro = $(this).data('nama_ro');

                $('#formAnggaran').attr('action', "{{ route('anggaran.store') }}");
                $('#modal_indikator_id').val(id);
                $('#modal_kode_input').val(kode);
                $('#modal_kode').text(kode);
                $('#modal_nama').text(nama);

                $('#modal_pagu_awal').val(awal > 0 ? awal : '');
                $('#modal_pagu_revisi').val(revisi > 0 ? revisi : '');
                $('#modal_tw1').val(tw1 > 0 ? tw1 : '');
                $('#modal_tw2').val(tw2 > 0 ? tw2 : '');
                $('#modal_tw3').val(tw3 > 0 ? tw3 : '');
                $('#modal_tw4').val(tw4 > 0 ? tw4 : '');
                $('#modal_kode_kegiatan').val(kode_kegiatan);
                $('#modal_nama_kegiatan').val(nama_kegiatan);
                $('#modal_kode_ro').val(kode_ro);
                $('#modal_nama_ro').val(nama_ro);

                modal.show();
            });

            $('.btn-edit-sasaran').on('click', function () {
                const kode = $(this).data('kode');
                const nama = $(this).data('nama');
                const awal = $(this).data('awal');
                const revisi = $(this).data('revisi');
                const tw1 = $(this).data('tw1');
                const tw2 = $(this).data('tw2');
                const tw3 = $(this).data('tw3');
                const tw4 = $(this).data('tw4');
                const kode_kegiatan = $(this).data('kode_kegiatan');
                const nama_kegiatan = $(this).data('nama_kegiatan');
                const kode_ro = $(this).data('kode_ro');
                const nama_ro = $(this).data('nama_ro');

                $('#formAnggaran').attr('action', "{{ route('anggaran.storeSasaran') }}");
                $('#modal_indikator_id').val('');
                $('#modal_kode_input').val(kode);
                $('#modal_kode').text(kode);
                $('#modal_nama').text(nama);

                $('#modal_pagu_awal').val(awal > 0 ? awal : '');
                $('#modal_pagu_revisi').val(revisi > 0 ? revisi : '');
                $('#modal_tw1').val(tw1 > 0 ? tw1 : '');
                $('#modal_tw2').val(tw2 > 0 ? tw2 : '');
                $('#modal_tw3').val(tw3 > 0 ? tw3 : '');
                $('#modal_tw4').val(tw4 > 0 ? tw4 : '');
                $('#modal_kode_kegiatan').val(kode_kegiatan);
                $('#modal_nama_kegiatan').val(nama_kegiatan);
                $('#modal_kode_ro').val(kode_ro);
                $('#modal_nama_ro').val(nama_ro);

                modal.show();
            });
        });
    </script>
    <!-- Modal Import Anggaran -->
    <div class="modal fade" id="modalImportAnggaran" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Import Data Anggaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('anggaran.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="alert alert-info border-0 rounded-4 shadow-sm mb-4">
                            <div class="small fw-bold"><i class="fas fa-info-circle me-1"></i> Silakan unduh template Excel terlebih dahulu, lalu isi data Master Anggaran & Realisasi.</div>
                        </div>
                        <div class="mb-4 text-center">
                            <a href="{{ route('anggaran.template', ['tahun' => $tahun]) }}" class="btn btn-outline-success rounded-pill px-4 fw-bold">
                                <i class="fas fa-download me-1"></i> Download Template Anggaran
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
