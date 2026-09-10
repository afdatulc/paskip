@extends('layouts.dashboard')

@section('title', 'Template Word')

@section('content')
    <div class="container-fluid py-4">
        <div class="row mb-4 align-items-center">
            <div class="col-md-6">
                <h4 class="mb-0 fw-bold text-gray-800">
                    <i class="fas fa-file-word text-primary me-2"></i> Ekspor Template Word
                </h4>
                <p class="text-muted small mb-0 mt-1">Pilih jenis dokumen yang ingin Anda cetak.</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <label class="form-label fw-bold">Pilih Jenis Dokumen</label>
                <select id="documentType" class="form-select form-select-lg mb-3">
                    <option value="">-- Silakan Pilih Dokumen --</option>
                    <option value="notulen_capaian">Notulen Capaian Kinerja</option>
                    <option value="surat_undangan">Surat Undangan Rapat</option>
                    <option value="daftar_hadir">Daftar Hadir Peserta</option>
                    <!-- <option value="notulen_pk">Notulen PK</option>
                    <option value="dokumen_sumber">Dokumen Sumber</option>
                    <option value="bukti_tindak_lanjut">Bukti Tindak Lanjut</option> -->
                </select>
            </div>
        </div>

        <!-- Form Notulen Capaian Kinerja -->
        <div id="form-notulen_capaian" class="card border-0 shadow-sm rounded-4 document-form" style="display: none;">
            <div class="card-header bg-white border-bottom pt-4 pb-3">
                <h5 class="card-title fw-bold text-primary mb-0">
                    <i class="fas fa-file-alt me-2"></i> Form Notulen Capaian Kinerja
                </h5>
            </div>
            <div class="card-body p-4">
                <div class="alert alert-info border-0 small rounded-3 mb-4">
                    <i class="fas fa-info-circle me-2"></i> Dokumen ini menggunakan template
                    <strong>notulen_capkin.docx</strong> yang ada di storage/app/templates.
                </div>

                <form action="{{ route('template.word.export.notulen') }}" method="POST" onsubmit="this.target = this.format.value === 'html' ? '_blank' : '';">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Tahun</label>
                            <input type="number" name="tahun" class="form-control" value="{{ date('Y') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Triwulan</label>
                            <select name="triwulan" class="form-select" required>
                                <option value="1">Triwulan I</option>
                                <option value="2">Triwulan II</option>
                                <option value="3">Triwulan III</option>
                                <option value="4">Triwulan IV</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Tanggal Rapat</label>
                            <input type="date" name="tanggal" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold">Format Output</label>
                            <select name="format" class="form-select">
                                <option value="html">HTML (Live Edit & Print PDF)</option>
                                <option value="word">Microsoft Word (.docx)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Waktu Rapat</label>
                            <input type="text" name="waktu" class="form-control" placeholder="Contoh: 09.00 - Selesai"
                                required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold">Tempat</label>
                            <input type="text" name="tempat" class="form-control" placeholder="Contoh: Ruang Rapat Utama"
                                required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Pimpinan Rapat</label>
                            <select name="pimpinan_id" class="form-select select2" required>
                                <option value="">-- Pilih Pimpinan Rapat --</option>
                                @foreach($pegawais as $p)
                                    <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->jabatan }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Notulis</label>
                            <select name="notulis_id" class="form-select select2" required>
                                <option value="">-- Pilih Notulis --</option>
                                @foreach($pegawais as $p)
                                    <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->jabatan }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                            <i class="fas fa-print me-1"></i> Cetak / Export
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Form Surat Undangan Rapat -->
        <div id="form-surat_undangan" class="card border-0 shadow-sm rounded-4 document-form" style="display: none;">
            <div class="card-header bg-white border-bottom pt-4 pb-3">
                <h5 class="card-title fw-bold text-primary mb-0">
                    <i class="fas fa-envelope-open-text me-2"></i> Form Surat Undangan Rapat
                </h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('template.word.export.undangan') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Nomor Surat</label>
                            <input type="text" name="nomor_surat" class="form-control" value="B-91/63050/PR.130/2026"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Sifat Surat</label>
                            <input type="text" name="sifat_surat" class="form-control" placeholder="Biasa/Penting"
                                value="Biasa" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Lampiran</label>
                            <input type="text" name="lampiran" class="form-control" placeholder="-" value="-" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Perihal</label>
                            <input type="text" name="perihal" class="form-control" placeholder="Undangan Rapat..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Tanggal Surat</label>
                            <input type="date" name="tgl_surat" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Kepada Yth. (Undangan)</label>
                            <textarea name="undangan" class="form-control tinymce-editor" rows="3"
                                required>Yth. Seluruh Pegawai BPS Kabupaten Tapin</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Isi Undangan (Pembuka)</label>
                            <textarea name="isi_undangan" id="isi_undangan" class="form-control tinymce-editor"
                                rows="3">Sesuai dengan Peraturan Presiden Nomor 29 Tahun 2014 tentang Sistem Akuntabilitas Kinerja Instansi Pemerintah (SAKIP) serta sebagai upaya meningkatkan efektivitas pelaksanaan program dan memastikan keselarasan capaian kinerja dan anggaran sebagai bagian dari pelaporan kinerja, dengan ini mengundang Bapak/Ibu untuk mengikuti rapat pada:</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Hari, Tanggal Kegiatan</label>
                            <input type="date" name="hari_tanggal_kegiatan" class="form-control" value="{{ date('Y-m-d') }}"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Waktu Kegiatan</label>
                            <input type="text" name="waktu_kegiatan" class="form-control" placeholder="09.00 s.d. selesai"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Tempat Kegiatan</label>
                            <input type="text" name="tempat_kegiatan" class="form-control" value="Aula BPS Kabupaten Tapin"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Agenda Kegiatan</label>
                            <input type="text" name="agend_kegiatan" class="form-control" placeholder="Rapat Evaluasi"
                                required>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                            <i class="fas fa-file-word me-1"></i> Generate Undangan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Form Daftar Hadir Peserta -->
        <div id="form-daftar_hadir" class="card border-0 shadow-sm rounded-4 document-form" style="display: none;">
            <div class="card-header bg-white border-bottom pt-4 pb-3">
                <h5 class="card-title fw-bold text-primary mb-0">
                    <i class="fas fa-clipboard-list me-2"></i> Form Daftar Hadir Peserta
                </h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('template.word.export.daftar-hadir') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-bold">Judul Kegiatan / Rapat</label>
                            <input type="text" name="judul_kegiatan" class="form-control"
                                placeholder="Contoh: Rapat Capaian Kinerja..." required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold">Tanggal Kegiatan</label>
                            <input type="date" name="tanggal_kegiatan" class="form-control" value="{{ date('Y-m-d') }}"
                                required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Mengetahui (Pimpinan)</label>
                            <select name="pimpinan_id" class="form-select select2" required>
                                <option value="">-- Pilih Pimpinan --</option>
                                @foreach($pegawais as $p)
                                    <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->jabatan }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Pembuat Daftar Hadir</label>
                            <select name="pembuat_id" class="form-select select2" required>
                                <option value="">-- Pilih Pembuat Daftar --</option>
                                @foreach($pegawais as $p)
                                    <option value="{{ $p->id }}">{{ $p->nama }} ({{ $p->jabatan }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Opsi Tampilan Peserta</label>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="tampilkan_nama" id="toggleTampilkanNama">
                                <label class="form-check-label small" for="toggleTampilkanNama">
                                    Tampilkan seluruh nama pegawai BPS
                                </label>
                            </div>
                        </div>
                        <div class="col-12" id="containerJumlahBaris">
                            <label class="form-label small fw-bold">Atau Jumlah Baris Kosong (Peserta)</label>
                            <input type="number" name="jumlah_baris" id="inputJumlahBaris" class="form-control" value="20" min="1" max="100">
                            <small class="text-muted">Tentukan berapa banyak baris kosong yang ingin disediakan untuk daftar hadir jika nama pegawai dikosongkan.</small>
                        </div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                            <i class="fas fa-print me-1"></i> Buka Tampilan Cetak
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Placeholder Segera Hadir -->
        <div id="form-coming_soon" class="card border-0 shadow-sm rounded-4 bg-light opacity-75 document-form"
            style="display: none;">
            <div class="card-body p-5 text-center d-flex flex-column justify-content-center">
                <i class="fas fa-tools fs-1 text-muted mb-3"></i>
                <h5 class="fw-bold text-muted">Segera Hadir</h5>
                <p class="text-muted mb-0">
                    Fitur cetak untuk dokumen ini sedang dalam tahap pengembangan.
                </p>
            </div>
        </div>

    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            if ($('.select2').length) {
                $('.select2').select2({
                    theme: 'bootstrap-5',
                    width: '100%'
                });
            }

            $('#documentType').on('change', function () {
                $('.document-form').hide();
                let selected = $(this).val();

                if (selected === 'notulen_capaian') {
                    $('#form-notulen_capaian').fadeIn();
                } else if (selected === 'surat_undangan') {
                    $('#form-surat_undangan').fadeIn(400, function () {
                        if (typeof window.initTinyMCE === 'function') {
                            window.initTinyMCE('.tinymce-editor');
                        }
                    });
                } else if (selected === 'daftar_hadir') {
                    $('#form-daftar_hadir').fadeIn();
                } else if (selected !== '') {
                    $('#form-coming_soon').fadeIn();
                }
            });

            $('#toggleTampilkanNama').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#containerJumlahBaris').slideUp();
                    $('#inputJumlahBaris').prop('required', false);
                } else {
                    $('#containerJumlahBaris').slideDown();
                    $('#inputJumlahBaris').prop('required', true);
                }
            });
            // trigger on load
            $('#toggleTampilkanNama').trigger('change');
        });
    </script>
@endsection
