@extends('layouts.dashboard')

@section('title', 'Target Triwulanan')

@section('content')
<div class="card border-0 shadow-sm rounded-4 text-dark">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle border-0" id="targetTable">
                <thead class="table-light border-0">
                    <tr>
                        <th rowspan="2" class="align-middle border-0" width="50">No</th>
                        <th rowspan="2" class="align-middle border-0">Indikator Kinerja</th>
                        <th rowspan="2" class="align-middle text-center border-0" width="100">Satuan</th>
                        <th colspan="4" class="text-center border-0 py-2">Target per Triwulan</th>
                        <th rowspan="2" class="align-middle text-center border-0" width="80">Aksi</th>
                    </tr>
                    <tr class="text-center border-0">
                        <th class="border-0 small fw-bold text-muted">TW 1</th>
                        <th class="border-0 small fw-bold text-muted">TW 2</th>
                        <th class="border-0 small fw-bold text-muted">TW 3</th>
                        <th class="border-0 small fw-bold text-muted">TW 4</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @foreach($indikators as $i)
                    @php $disc = $i->discrepancy_q4; @endphp
                    <tr id="row-{{ $i->id }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            <div class="fw-bold text-dark mb-0">{{ $i->indikator_kinerja }}</div>
                            <small class="text-primary fw-bold extra-small">{{ $i->kode }}</small>
                        </td>
                        <td class="text-center"><span class="badge bg-light text-dark border fw-normal px-2">{{ $i->satuan }}</span></td>
                        <td class="text-center fw-bold text-dark">{{ $i->target->target_tw1 ?? '-' }}</td>
                        <td class="text-center fw-bold text-dark">{{ $i->target->target_tw2 ?? '-' }}</td>
                        <td class="text-center fw-bold text-dark">{{ $i->target->target_tw3 ?? '-' }}</td>
                        <td class="text-center fw-bold text-dark">
                            {{ $i->target->target_tw4 ?? '-' }}
                            @if($disc)
                                <div class="mt-1">
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill extra-small"
                                          title="Target Q4 ({{ number_format($disc['target_tw4'], 2) }}) tidak sama dengan PK Tahunan ({{ number_format($disc['target_pk'], 2) }})">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Beda PK ({{ number_format($disc['target_pk'], 2) }})
                                    </span>
                                </div>
                            @endif
                        </td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-primary rounded-3 edit-target" data-id="{{ $i->id }}" title="Edit Target">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Edit Target -->
<div class="modal fade" id="modalTarget" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Update Target Kinerja</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formTarget">
                @csrf
                @method('PUT')
                <input type="hidden" id="indikator_id">
                <div class="modal-body p-4">
                    <div class="mb-3 bg-light p-3 rounded-4 border-start border-4 border-primary">
                        <small class="text-muted fw-bold d-block mb-1">INDIKATOR:</small>
                        <div class="fw-bold text-dark" id="display_indikator">...</div>
                        <small class="text-primary fw-bold" id="display_kode">...</small>
                    </div>

                    {{-- Banner Peringatan Perbedaan TW4 & PK --}}
                    <div id="alertDiscrepancy" class="alert alert-warning border-0 rounded-4 small mb-3 d-none">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Target Q4 (<strong id="text_tw4">0</strong>) ≠ PK Tahunan (<strong id="text_pk">0</strong>).
                            </div>
                            <button type="button" class="btn btn-sm btn-warning rounded-pill px-3 shadow-sm fw-bold" id="btnSyncQ4">
                                <i class="fas fa-sync me-1"></i>Samakan
                            </button>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small">Target TW 1</label>
                            <input type="text" name="target_tw1" id="target_tw1" class="form-control rounded-3 shadow-none border-light-subtle" placeholder="0.00">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">Target TW 2</label>
                            <input type="text" name="target_tw2" id="target_tw2" class="form-control rounded-3 shadow-none border-light-subtle" placeholder="0.00">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">Target TW 3</label>
                            <input type="text" name="target_tw3" id="target_tw3" class="form-control rounded-3 shadow-none border-light-subtle" placeholder="0.00">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">Target TW 4</label>
                            <input type="text" name="target_tw4" id="target_tw4" class="form-control rounded-3 shadow-none border-light-subtle" placeholder="0.00">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm" id="btnSimpan">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#targetTable').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json' }
        });

        let currentPkEfektif = 0;

        // Edit Button Click
        $(document).on('click', '.edit-target', function() {
            const id = $(this).data('id');
            $('#indikator_id').val(id);
            $('#alertDiscrepancy').addClass('d-none');
            $('#modalTarget').modal('show');

            $.get(`{{ url('target') }}/${id}`, function(data) {
                $('#display_indikator').text(data.indikator.indikator_kinerja);
                $('#display_kode').text(data.indikator.kode || '-');
                $('#target_tw1').val(data.target_tw1);
                $('#target_tw2').val(data.target_tw2);
                $('#target_tw3').val(data.target_tw3);
                $('#target_tw4').val(data.target_tw4);

                currentPkEfektif = data.target_pk_efektif || 0;

                if (data.discrepancy_q4) {
                    $('#text_tw4').text(data.discrepancy_q4.target_tw4);
                    $('#text_pk').text(data.discrepancy_q4.target_pk);
                    $('#alertDiscrepancy').removeClass('d-none');
                }
            });
        });

        // Tombol Samakan Q4 ke PK
        $('#btnSyncQ4').on('click', function() {
            if (currentPkEfektif !== undefined) {
                $('#target_tw4').val(currentPkEfektif);
                toastr.info(`Target TW 4 diset mengikuti PK Tahunan (${currentPkEfektif})`);
                $('#alertDiscrepancy').addClass('d-none');
            }
        });

        // Form Submit
        $('#formTarget').on('submit', function(e) {
            e.preventDefault();
            const id = $('#indikator_id').val();
            const btn = $('#btnSimpan');

            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

            $.ajax({
                url: `{{ url('target') }}/${id}`,
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    toastr.success(response.message);
                    $('#modalTarget').modal('hide');
                    window.location.reload();
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('Simpan Perubahan');
                    const errors = xhr.responseJSON.errors;
                    if (errors) {
                        Object.values(errors).forEach(err => toastr.error(err[0]));
                    } else {
                        toastr.error('Terjadi kesalahan saat menyimpan data.');
                    }
                }
            });
        });
    });
</script>
<style>
    .extra-small { font-size: 0.7rem; }
</style>
@endsection
