@extends('layouts.dashboard')

@section('title', 'Edit Pegawai')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body p-4">
                    <form action="{{ route('pegawai.update', $pegawai->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label fw-bold">NIP</label>
                            <input type="text" name="nip" class="form-control" value="{{ old('nip', $pegawai->nip) }}"
                                required>
                            @error('nip') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email BPS (Opsional)</label>
                            <input type="email" name="email_bps" class="form-control"
                                value="{{ old('email_bps', $pegawai->email_bps) }}">
                            @error('email_bps') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" value="{{ old('nama', $pegawai->nama) }}"
                                required>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Status Pegawai</label>
                                <select name="status" class="form-select" required>
                                    <option value="PNS" {{ $pegawai->status == 'PNS' ? 'selected' : '' }}>PNS</option>
                                    <option value="PPPK" {{ $pegawai->status == 'PPPK' ? 'selected' : '' }}>PPPK</option>
                                    <option value="Outsourcing" {{ $pegawai->status == 'Outsourcing' ? 'selected' : '' }}>
                                        Outsourcing</option>
                                    <option value="Lainnya" {{ $pegawai->status == 'Lainnya' ? 'selected' : '' }}>Lainnya
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Seksi</label>
                                <select name="seksi" class="form-select" required>
                                    <option value="Kepala" {{ $pegawai->seksi == 'Kepala' ? 'selected' : '' }}>Kepala</option>
                                    <option value="Sosial" {{ $pegawai->seksi == 'Sosial' ? 'selected' : '' }}>Sosial</option>
                                    <option value="Produksi" {{ $pegawai->seksi == 'Produksi' ? 'selected' : '' }}>Produksi
                                    </option>
                                    <option value="Distribusi" {{ $pegawai->seksi == 'Distribusi' ? 'selected' : '' }}>
                                        Distribusi</option>
                                    <option value="Nerwilis" {{ $pegawai->seksi == 'Nerwilis' ? 'selected' : '' }}>Nerwilis
                                    </option>
                                    <option value="IPDS" {{ $pegawai->seksi == 'IPDS' ? 'selected' : '' }}>IPDS</option>
                                    <option value="Umum" {{ $pegawai->seksi == 'Umum' ? 'selected' : '' }}>Umum</option>
                                    <option value="Lainnya" {{ $pegawai->seksi == 'Lainnya' ? 'selected' : '' }}>Lainnya
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Jabatan</label>
                            <input type="text" name="jabatan" class="form-control"
                                value="{{ old('jabatan', $pegawai->jabatan) }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Unit Kerja</label>
                            <input type="text" name="unit_kerja" class="form-control"
                                value="{{ old('unit_kerja', $pegawai->unit_kerja) }}">
                        </div>

                        <div class="mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary">Update Pegawai</button>
                            <a href="{{ route('pegawai.index') }}" class="btn btn-light ms-2">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
