@extends('layouts.dashboard')

@section('title', 'Capaian Kinerja')

@section('content')
    <div class="card border-0 shadow-sm rounded-4 text-dark">
        <div class="card-header bg-white py-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="fw-bold mb-0"><i class="fas fa-award text-primary me-2"></i>Capaian Kinerja Triwulanan</h5>
                    <p class="text-muted small mb-0 mt-1">Kelola tautan bukti dukung dan penjelasan per triwulan.</p>
                </div>
                <form action="{{ route('capaian-kinerja.index') }}" method="GET" class="d-flex align-items-center gap-2">
                    <select name="tahun" class="form-select form-select-sm rounded-pill shadow-none" style="width: 120px;" onchange="this.form.submit()">
                        @php $currentYear = date('Y'); @endphp
                        @for($i = $currentYear - 2; $i <= $currentYear + 1; $i++)
                            <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                        @endfor
                    </select>
                    <select name="triwulan" class="form-select form-select-sm rounded-pill shadow-none" style="width: 150px;" onchange="this.form.submit()">
                        <option value="1" {{ $triwulan == 1 ? 'selected' : '' }}>Triwulan I</option>
                        <option value="2" {{ $triwulan == 2 ? 'selected' : '' }}>Triwulan II</option>
                        <option value="3" {{ $triwulan == 3 ? 'selected' : '' }}>Triwulan III</option>
                        <option value="4" {{ $triwulan == 4 ? 'selected' : '' }}>Triwulan IV</option>
                    </select>
                </form>
                @if(auth()->user()->isAdmin())
                    <div class="d-flex gap-2 ms-auto ms-md-0 mt-3 mt-md-0">
                        <a href="{{ route('capaian-kinerja.template', ['tahun' => $tahun]) }}" class="btn btn-sm btn-outline-success rounded-pill px-3 shadow-sm d-flex align-items-center">
                            <i class="fas fa-file-excel me-1"></i> Template
                        </a>
                        <button type="button" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#modalImport">
                            <i class="fas fa-upload me-1"></i> Import
                        </button>
                    </div>
                @endif
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="capaianTable">
                    <thead class="table-light">
                        <tr>
                            <th width="40" class="text-center ps-3">No</th>
                            <th width="100">Kode</th>
                            <th style="min-width: 250px;">Indikator Kinerja</th>
                            <th>Status Capaian</th>
                            <th width="120" class="text-center pe-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($indikators as $ind)
                            @php 
                                $capaian = $capaians->get($ind->id);
                                $realisasi = $ind->realisasis->first();
                                $analisis = $ind->analisis->first();
                                $targetField = 'target_tw' . $triwulan;
                                $targetVal = $ind->target ? $ind->target->$targetField : '-';
                                $targetYField = 'target_y_tw' . $triwulan;
                                $targetYVal = $ind->target ? $ind->target->$targetYField : '';
                                $hasRealisasi = $realisasi && $realisasi->realisasi_kumulatif !== null && $realisasi->realisasi_kumulatif !== '';
                            @endphp
                            <tr>
                                <td class="text-center ps-3">{{ $loop->iteration }}</td>
                                <td>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-2">{{ $ind->kode }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark small">{{ $ind->indikator_kinerja }}</div>
                                    <div class="text-muted extra-small">{{ Str::limit($ind->sasaran, 80) }}</div>
                                </td>
                                <td>
                                    @if($hasRealisasi)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill"><i class="fas fa-check-circle me-1"></i> Terisi</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill"><i class="fas fa-clock me-1"></i> Belum Diisi</span>
                                    @endif
                                </td>
                                <td class="text-center pe-3">
                                    <button class="btn btn-sm btn-primary rounded-pill px-3 btn-edit-capaian" 
                                        data-id="{{ $ind->id }}" 
                                        data-kode="{{ $ind->kode }}"
                                        data-nama="{{ $ind->indikator_kinerja }}"
                                        data-satuan="{{ $ind->satuan }}"
                                        data-defx="{{ $ind->definisi_x }}"
                                        data-defy="{{ $ind->definisi_y }}"
                                        data-target="{{ $targetVal }}"
                                        data-rkumulatif="{{ $realisasi->realisasi_kumulatif ?? '' }}"
                                        data-rx="{{ $realisasi->realisasi_x ?? '' }}"
                                        data-ry="{{ $realisasi->realisasi_y ?? '' }}"
                                        data-kinerja="{{ $capaian->link_bukti_kinerja ?? '' }}"
                                        data-rtl="{{ $capaian->link_bukti_tindak_lanjut ?? '' }}"
                                        data-penjelasan="{{ $capaian->penjelasan_lainnya ?? '' }}"
                                        data-dasar="{{ $capaian->dasar_hitung ?? '' }}"
                                        data-argumen="{{ $capaian->argumen_logis ?? '' }}"
                                        data-targetrealisasi="{{ $capaian->target_realisasi ?? '' }}"
                                        data-targety="{{ $targetYVal }}"
                                        title="Kelola Capaian">
                                        <i class="fas fa-edit me-1"></i> Kelola
                                    </button>
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
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-bold">Kelola Capaian Kinerja</h5>
                        <div class="text-muted small">Tahun <span id="modal-tahun" class="fw-bold text-dark">{{ $tahun }}</span>, Triwulan <span id="modal-triwulan" class="fw-bold text-dark">{{ $triwulan }}</span></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formCapaian">
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

                        <div class="row g-3">
                            <div class="col-12">
                                <h6 class="fw-bold small text-primary border-bottom pb-2 mb-3">1. Realisasi (Kuantitatif)</h6>
                            </div>
                            
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

                            <div class="col-12 mt-4 d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                                <h6 class="fw-bold small text-primary mb-0">2. Narasi & Argumen</h6>
                                <div class="d-flex align-items-center gap-2">
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
                                <label class="form-label fw-bold small">Target & Realisasi</label>
                                <textarea name="target_realisasi" id="target_realisasi" class="form-control rounded-3 shadow-none border-light-subtle" rows="3" placeholder="Jelaskan mengenai target dan realisasi..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small">Dasar Hitung</label>
                                <textarea name="dasar_hitung" id="dasar_hitung" class="form-control rounded-3 shadow-none border-light-subtle" rows="3" placeholder="Jelaskan cara menghitung capaian ini..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small">Argumen Logis</label>
                                <textarea name="argumen_logis" id="argumen_logis" class="form-control rounded-3 shadow-none border-light-subtle" rows="3" placeholder="Masukkan argumen logis terkait capaian periode ini..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small">Penjelasan atau Pembahasan Lainnya</label>
                                <textarea name="penjelasan_lainnya" id="penjelasan_lainnya" class="form-control rounded-3 shadow-none border-light-subtle" rows="3" placeholder="Tambahkan penjelasan lainnya..."></textarea>
                            </div>

                            <div class="col-12 mt-4">
                                <h6 class="fw-bold small text-primary border-bottom pb-2 mb-3">3. Tautan Bukti Dukung</h6>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small">Tautan Bukti Dukung Kinerja</label>
                                <input type="text" name="link_bukti_kinerja" id="link_bukti_kinerja" class="form-control rounded-3 shadow-none border-light-subtle" placeholder="https://...">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold small">Tautan Bukti Dukung Rencana Tindak Lanjut (RTL)</label>
                                <input type="text" name="link_bukti_tindak_lanjut" id="link_bukti_tindak_lanjut" class="form-control rounded-3 shadow-none border-light-subtle" placeholder="https://...">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4 shadow-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm" id="btnSimpan"><i class="fas fa-save me-1"></i> Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Import -->
    @if(auth()->user()->isAdmin())
    <div class="modal fade" id="modalImport" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Import Capaian Kinerja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('capaian-kinerja.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
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
    @endif
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
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

                $('#link_bukti_kinerja').val(btn.data('kinerja'));
                $('#link_bukti_tindak_lanjut').val(btn.data('rtl'));

                const modal = new bootstrap.Modal(document.getElementById('modalCapaian'));
                modal.show();
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
    </script>
@endsection
