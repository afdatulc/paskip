@extends('layouts.dashboard')

@section('title', 'Rencana Strategis (Renstra)')

@section('styles')
<style>
    .renstra-card {
        border-left: 4px solid var(--primary-color);
        transition: all 0.3s ease;
    }
    .renstra-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(67, 97, 238, 0.15) !important;
    }
    .renstra-card.aktif {
        border-left-color: #2ec4b6;
        background: linear-gradient(135deg, rgba(46, 196, 182, 0.03), rgba(255,255,255,1));
    }
    .badge-aktif {
        background: linear-gradient(135deg, #2ec4b6, #218380);
        font-size: 0.7rem;
        padding: 0.35rem 0.8rem;
    }
    .badge-nonaktif {
        background: rgba(108, 117, 125, 0.15);
        color: #6c757d;
        font-size: 0.7rem;
        padding: 0.35rem 0.8rem;
    }
    .periode-text {
        font-size: 1.8rem;
        font-weight: 800;
        letter-spacing: -1px;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .empty-state {
        padding: 4rem 2rem;
        text-align: center;
    }
    .empty-state i {
        font-size: 4rem;
        color: rgba(67, 97, 238, 0.15);
        margin-bottom: 1.5rem;
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-muted mb-0">Kelola periode Rencana Strategis dan target 5-tahunan IKU</p>
    </div>
    @if(auth()->user()->isAdmin())
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahRenstra">
        <i class="fas fa-plus me-2"></i>Tambah Renstra
    </button>
    @endif
</div>

@if($renstras->isEmpty())
    <div class="card">
        <div class="empty-state">
            <i class="fas fa-file-contract d-block"></i>
            <h5 class="fw-bold text-dark">Belum Ada Periode Renstra</h5>
            <p class="text-muted mb-4">Mulai dengan menambahkan periode Rencana Strategis pertama.</p>
            @if(auth()->user()->isAdmin())
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahRenstra">
                <i class="fas fa-plus me-2"></i>Tambah Renstra Pertama
            </button>
            @endif
        </div>
    </div>
@else
    <div class="row g-4">
        @foreach($renstras as $renstra)
        <div class="col-md-6 col-lg-4">
            <div class="card renstra-card {{ $renstra->is_aktif ? 'aktif' : '' }} h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="badge rounded-pill {{ $renstra->is_aktif ? 'badge-aktif' : 'badge-nonaktif' }}">
                                <i class="fas {{ $renstra->is_aktif ? 'fa-circle-check' : 'fa-circle-pause' }} me-1"></i>
                                {{ $renstra->is_aktif ? 'AKTIF' : 'NONAKTIF' }}
                            </span>
                        </div>
                        @if(auth()->user()->isAdmin())
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light rounded-pill px-2" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3">
                                @unless($renstra->is_aktif)
                                <li>
                                    <form action="{{ route('renstra.activate', $renstra) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item py-2">
                                            <i class="fas fa-toggle-on me-2 text-success"></i>Aktifkan
                                        </button>
                                    </form>
                                </li>
                                @endunless
                                <li>
                                    <form action="{{ route('renstra.destroy', $renstra) }}" method="POST"
                                        onsubmit="return confirm('Yakin menghapus Renstra {{ $renstra->periode }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="dropdown-item py-2 text-danger">
                                            <i class="fas fa-trash me-2"></i>Hapus
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                        @endif
                    </div>

                    <div class="periode-text mb-2">{{ $renstra->periode }}</div>
                    <p class="text-muted small mb-3">{{ $renstra->deskripsi ?? 'Tidak ada deskripsi' }}</p>

                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-light rounded-3 px-3 py-2 text-center flex-fill">
                            <div class="fw-bold text-dark">{{ $renstra->targetRenstras->count() }}</div>
                            <div class="text-muted" style="font-size: 0.7rem;">Target IKU</div>
                        </div>
                        <div class="bg-light rounded-3 px-3 py-2 text-center flex-fill">
                            <div class="fw-bold text-dark">{{ count($renstra->daftar_tahun) }}</div>
                            <div class="text-muted" style="font-size: 0.7rem;">Tahun</div>
                        </div>
                    </div>

                    <a href="{{ route('renstra.show', $renstra) }}" class="btn btn-sm btn-outline-primary rounded-pill w-100">
                        <i class="fas fa-chart-line me-1"></i>Lihat Detail & Target
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
@endif

<!-- Modal Tambah Renstra -->
<div class="modal fade" id="modalTambahRenstra" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('renstra.store') }}" method="POST" class="modal-content border-0 shadow-lg rounded-4">
            @csrf
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-file-contract text-primary me-2"></i>Tambah Periode Renstra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label small fw-bold">Tahun Awal</label>
                        <input type="number" name="tahun_awal" class="form-control rounded-3" 
                               value="{{ date('Y') }}" min="2020" max="2040" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small fw-bold">Tahun Akhir</label>
                        <input type="number" name="tahun_akhir" class="form-control rounded-3" 
                               value="{{ date('Y') + 4 }}" min="2021" max="2045" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold">Deskripsi <span class="text-muted">(opsional)</span></label>
                        <textarea name="deskripsi" class="form-control rounded-3" rows="3" 
                                  placeholder="Misal: Renstra BPS Kabupaten Tapin Periode 2025-2029"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 pb-4 px-4 justify-content-center">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                    <i class="fas fa-save me-1"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
