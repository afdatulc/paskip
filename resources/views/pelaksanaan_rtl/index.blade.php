@extends('layouts.dashboard')

@section('title', 'Pelaksanaan Rencana Tindak Lanjut')

@section('content')
<div class="card mb-4 shadow-sm border-0 rounded-4">
    <div class="card-body">
        <form action="{{ route('pelaksanaan-rtl.index') }}" method="GET" class="d-flex gap-3 align-items-end flex-wrap">
            <div>
                <label class="form-label text-muted small fw-bold mb-1">Tahun</label>
                <select name="tahun" class="form-select" onchange="this.form.submit()">
                    @for($i = date('Y') - 2; $i <= date('Y') + 2; $i++)
                        <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="form-label text-muted small fw-bold mb-1">Triwulan</label>
                <select name="triwulan" class="form-select" onchange="this.form.submit()">
                    @for($i = 1; $i <= 4; $i++)
                        <option value="{{ $i }}" {{ $triwulan == $i ? 'selected' : '' }}>Triwulan {{ $i }}</option>
                    @endfor
                </select>
            </div>
            
            @if(auth()->user()->isAdmin())
            <div class="ms-auto">
                <a href="{{ route('pelaksanaan-rtl.compile-all') }}" class="btn btn-success">
                    <i class="fas fa-file-word me-1"></i> Compile Seluruh Bukti
                </a>
            </div>
            @endif
        </form>
    </div>
</div>

<div class="alert alert-info border-0 shadow-sm rounded-4 small mb-4">
    <i class="fas fa-info-circle me-1"></i> Menampilkan daftar RTL yang disusun pada <strong>Triwulan {{ $target_triwulan }} Tahun {{ $target_tahun }}</strong> untuk dieksekusi pada <strong>Triwulan {{ $triwulan }} Tahun {{ $tahun }}</strong>.
</div>

<div class="card border-0 shadow-sm rounded-4 text-dark">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="40" class="text-center py-3">No</th>
                        <th width="100" class="py-3">Kode</th>
                        <th style="min-width: 250px;" class="py-3">Indikator Kinerja</th>
                        <th class="py-3 text-center text-nowrap">Batas Waktu</th>
                        <th width="180" class="text-center pe-3 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($groupedIKUs as $indikatorId => $rtlGroup)
                        @php $indikator = $rtlGroup->first()->indikator; @endphp
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-2">{{ $indikator->kode }}</span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark small">{{ $indikator->indikator_kinerja }}</div>
                                <div class="text-muted small">{{ $indikator->sasaran }}</div>
                            </td>
                            <td class="text-center text-nowrap">
                                @if($rtlGroup->first()->batas_waktu)
                                    {{ $rtlGroup->first()->batas_waktu->format('d M Y') }}
                                @else
                                    <span class="text-muted fst-italic">Tidak ditentukan</span>
                                @endif
                            </td>
                            <td class="text-center pe-3">
                                <a href="{{ route('pelaksanaan-rtl.show', ['id' => $indikator->id, 'tahun' => $tahun, 'triwulan' => $triwulan]) }}" class="btn btn-sm btn-dark">
                                    Kelola RTL <span class="badge bg-light text-dark ms-1">{{ $rtlGroup->count() }}</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-clipboard-check fs-2 mb-3"></i>
                                    <p class="mb-0">Tidak ada Rencana Tindak Lanjut (RTL) yang sesuai dengan filter.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>



@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('.form-ajax');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const errorContainer = this.querySelector('.error-message-container');
            const submitBtn = this.querySelector('button[type="submit"]');
            
            errorContainer.classList.add('d-none');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...';

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(async response => {
                if (!response.ok) {
                    if (response.status === 422) {
                        const data = await response.json();
                        let errorMsg = data.message || 'Data tidak valid.';
                        if (data.errors) {
                            errorMsg = Object.values(data.errors).flat().join('<br>');
                        }
                        throw new Error(errorMsg);
                    }
                    throw new Error('Terjadi kesalahan pada server. Silakan coba lagi.');
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    window.location.reload();
                } else if (data.status === 'error') {
                    throw new Error(data.message);
                }
            })
            .catch(error => {
                errorContainer.innerHTML = error.message;
                errorContainer.classList.remove('d-none');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save me-1"></i> Simpan Bukti';
            });
        });
    });
});
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.4.21/mammoth.browser.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle DOCX Preview
    document.querySelectorAll('.btn-preview-doc').forEach(btn => {
        btn.addEventListener('click', function() {
            const url = this.getAttribute('data-url');
            const container = this.closest('.border.rounded').querySelector('.doc-preview-container');
            const contentDiv = container.querySelector('.preview-content');
            const loader = container.querySelector('.loading-indicator');
            
            // Toggle visibility
            if (!container.classList.contains('d-none') && contentDiv.innerHTML !== '') {
                container.classList.add('d-none');
                return;
            }
            
            container.classList.remove('d-none');
            contentDiv.innerHTML = '';
            loader.classList.remove('d-none');
            
            fetch(url)
                .then(response => response.arrayBuffer())
                .then(arrayBuffer => {
                    return mammoth.convertToHtml({arrayBuffer: arrayBuffer});
                })
                .then(result => {
                    loader.classList.add('d-none');
                    contentDiv.innerHTML = result.value;
                    if(result.messages.length > 0) {
                        console.warn("Mammoth messages:", result.messages);
                    }
                })
                .catch(err => {
                    loader.classList.add('d-none');
                    contentDiv.innerHTML = '<div class="alert alert-danger mb-0">Gagal memuat dokumen preview. File mungkin rusak atau format tidak didukung. <br><small>' + err.message + '</small></div>';
                });
        });
    });
});
</script>
@endsection
