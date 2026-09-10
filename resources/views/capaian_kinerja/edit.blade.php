@extends('layouts.dashboard')

@section('title', 'Isi Capaian Kinerja')

@section('content')
<div class="mb-4">
    <a href="{{ route('capaian-kinerja.index', ['tahun' => $tahun, 'triwulan' => $triwulan]) }}" class="btn btn-light shadow-sm">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-bottom p-4">
        <div class="d-flex align-items-start gap-3">
            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill border border-primary-subtle fs-6">
                {{ $indikator->kode }}
            </span>
            <div>
                <h5 class="fw-bold text-dark mb-1">{{ $indikator->indikator_kinerja }}</h5>
                <div class="text-muted small mt-2">
                    <span class="me-3"><i class="fas fa-calendar text-secondary"></i> Tahun: <strong>{{ $tahun }}</strong> (Triwulan {{ $triwulan }})</span>
                    <span class="me-3"><i class="fas fa-tag text-success"></i> Satuan: <strong>{{ $indikator->satuan }}</strong></span>
                    <span><i class="fas fa-bullseye text-danger"></i> Target TW: <strong>{{ $targetVal ?? '-' }}</strong></span>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body p-4">
        <form action="{{ route('capaian-kinerja.store') }}" method="POST">
            @csrf
            <input type="hidden" name="indikator_id" value="{{ $indikator->id }}">
            <input type="hidden" name="tahun" value="{{ $tahun }}">
            <input type="hidden" name="triwulan" value="{{ $triwulan }}">
            
            <div class="row g-4">
                <!-- 1. Realisasi -->
                <div class="col-12 border-bottom pb-4">
                    <h6 class="fw-bold text-primary mb-3"><span class="badge bg-primary rounded-circle me-2">1</span>Realisasi</h6>
                    
                    @if($indikator->tipe === '%')
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">X ({{ $indikator->definisi_x ?? 'Pembilang' }})</label>
                                <input type="number" step="any" name="realisasi_x" id="realisasi_x" class="form-control" value="{{ $realisasi->realisasi_x ?? '' }}" {{ isset($isReadOnly) && $isReadOnly ? 'readonly' : '' }}>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Y ({{ $indikator->definisi_y ?? 'Penyebut' }})</label>
                                <input type="number" step="any" name="realisasi_y" id="realisasi_y" class="form-control" value="{{ $realisasi->realisasi_y ?? '' }}" {{ isset($isReadOnly) && $isReadOnly ? 'readonly' : '' }}>
                            </div>
                            <div class="col-md-12 mt-3">
                                <label class="form-label small fw-bold">
                                    Realisasi ({{ $indikator->satuan }}) 
                                    <span class="text-muted fw-normal ms-1">(Dihitung otomatis dari X/Y, namun dapat diubah manual)</span>
                                </label>
                                <input type="number" step="any" name="realisasi_kumulatif" id="realisasi_kumulatif" class="form-control" value="{{ $realisasi->realisasi_kumulatif ?? '' }}" {{ isset($isReadOnly) && $isReadOnly ? 'readonly' : '' }}>
                            </div>
                        </div>
                    @else
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Realisasi ({{ $indikator->satuan }})</label>
                            <input type="number" step="any" name="realisasi_kumulatif" id="realisasi_kumulatif" class="form-control" value="{{ $realisasi->realisasi_kumulatif ?? '' }}" {{ isset($isReadOnly) && $isReadOnly ? 'readonly' : '' }}>
                        </div>
                    @endif
                </div>

                <!-- 2. Dasar Hitung -->
                <div class="col-12 border-bottom pb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-primary mb-0"><span class="badge bg-primary rounded-circle me-2">2</span>Dasar Hitung</h6>
                        <div class="d-flex align-items-center gap-2">
                            @if(!(isset($isReadOnly) && $isReadOnly))
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
                            @endif
                        </div>
                    </div>
                    
                    <textarea name="dasar_hitung" id="dasar_hitung" class="form-control mb-3" rows="4" placeholder="Masukkan dasar perhitungan atau rumus yang digunakan..." {{ isset($isReadOnly) && $isReadOnly ? 'readonly' : '' }}>{{ $capaian->dasar_hitung ?? '' }}</textarea>
                    
                    <label class="form-label fw-bold small mt-3">Target Triwulanan</label>
                    <textarea name="target_realisasi" id="target_realisasi" class="form-control" rows="4" placeholder="Jelaskan mengenai target dan realisasi..." {{ isset($isReadOnly) && $isReadOnly ? 'readonly' : '' }}>{{ $capaian->target_realisasi ?? '' }}</textarea>
                </div>

                <!-- 3. Argumen Logis -->
                <div class="col-12 border-bottom pb-4">
                    <h6 class="fw-bold text-primary mb-3"><span class="badge bg-primary rounded-circle me-2">3</span>Argumen Logis</h6>
                    <textarea name="argumen_logis" id="argumen_logis" class="form-control" rows="4" placeholder="Masukkan argumen logis terkait capaian periode ini..." {{ isset($isReadOnly) && $isReadOnly ? 'readonly' : '' }}>{{ $capaian->argumen_logis ?? '' }}</textarea>
                </div>

                <!-- 4. Lainnya -->
                <div class="col-12 pb-2">
                    <h6 class="fw-bold text-primary mb-3"><span class="badge bg-primary rounded-circle me-2">4</span>Lainnya</h6>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Penjelasan Lainnya</label>
                        <textarea name="penjelasan_lainnya" id="penjelasan_lainnya" class="form-control" rows="3" placeholder="Tambahan penjelasan jika diperlukan..." {{ isset($isReadOnly) && $isReadOnly ? 'readonly' : '' }}>{{ $capaian->penjelasan_lainnya ?? '' }}</textarea>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Link Bukti Dukung Kinerja</label>
                            <input type="url" name="link_bukti_kinerja" class="form-control" placeholder="https://..." value="{{ $capaian->link_bukti_kinerja ?? '' }}" {{ isset($isReadOnly) && $isReadOnly ? 'readonly' : '' }}>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Link Bukti Tindak Lanjut</label>
                            <input type="url" name="link_bukti_tindak_lanjut" class="form-control" placeholder="https://..." value="{{ $capaian->link_bukti_tindak_lanjut ?? '' }}" {{ isset($isReadOnly) && $isReadOnly ? 'readonly' : '' }}>
                        </div>
                    </div>
                </div>
            </div>

            @if(!(isset($isReadOnly) && $isReadOnly))
            <div class="mt-4 pt-3 border-top text-end">
                <button type="submit" name="action" value="save" class="btn btn-outline-primary px-4 me-2 rounded-pill shadow-sm">
                    <i class="fas fa-save me-1"></i> Simpan Saja
                </button>
                <button type="submit" name="action" value="save_and_next" class="btn btn-primary px-4 rounded-pill shadow-sm fw-bold">
                    <i class="fas fa-arrow-right me-1"></i> Simpan & Lanjut Isi Kendala
                </button>
            </div>
            @else
            <div class="mt-4 pt-3 border-top text-end">
                <p class="text-muted fst-italic mb-0"><i class="fas fa-info-circle me-1"></i> Anda dalam mode Read-Only (Hanya Baca).</p>
            </div>
            @endif
        </form>
    </div>
</div>
<!-- Invisible data used by JS -->
<span id="modal-target" style="display: none;">{{ $targetVal }}</span>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        if (typeof window.initTinyMCE === 'function') {
            @if(isset($isReadOnly) && $isReadOnly)
                tinymce.init({
                    selector: '#dasar_hitung, #argumen_logis, #penjelasan_lainnya, #target_realisasi',
                    readonly: true,
                    menubar: false,
                    toolbar: false,
                    plugins: '',
                    statusbar: false,
                    content_style: 'body { font-family:Outfit,Helvetica,Arial,sans-serif; font-size:14px }'
                });
            @else
                window.initTinyMCE('#dasar_hitung');
                window.initTinyMCE('#argumen_logis');
                window.initTinyMCE('#penjelasan_lainnya');
                window.initTinyMCE('#target_realisasi');
            @endif
        }

        function calculateKumulatif() {
            if ($('#realisasi_x').length && $('#realisasi_y').length) {
                let x = parseFloat($('#realisasi_x').val()) || 0;
                let y = parseFloat($('#realisasi_y').val()) || 0;
                let kum = (y > 0) ? (x / y * 100) : 0;
                $('#realisasi_kumulatif').val(kum.toFixed(2).replace(/\.00$/, ''));
            }
        }
        
        // Calculate when X or Y changes
        $('#realisasi_x, #realisasi_y').on('input', calculateKumulatif);

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

        // Copy Narasi Button
        $(document).on('click', '.btn-copy-narasi', function(e) {
            e.preventDefault();
            const tw = $(this).data('tw');
            const indikatorId = $('input[name="indikator_id"]').val();
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
    });
</script>
@endsection
