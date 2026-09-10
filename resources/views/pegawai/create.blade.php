@extends('layouts.dashboard')

@section('title', 'Tambah Pegawai')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body p-4">
                    <form action="{{ route('pegawai.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">NIP</label>
                            <input type="text" name="nip" class="form-control" value="{{ old('nip') }}" required>
                            @error('nip') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email BPS (Opsional)</label>
                            <input type="email" name="email_bps" class="form-control" value="{{ old('email_bps') }}">
                            @error('email_bps') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-control" value="{{ old('nama') }}" required>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Status Pegawai</label>
                                <select name="status" class="form-select" required>
                                    <option value="PNS">PNS</option>
                                    <option value="PPPK">PPPK</option>
                                    <option value="Outsourcing">Outsourcing</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Seksi</label>
                                <select name="seksi" class="form-select" required>
                                    <option value="Kepala">Kepala</option>
                                    <option value="Sosial">Sosial</option>
                                    <option value="Produksi">Produksi</option>
                                    <option value="Distribusi">Distribusi</option>
                                    <option value="Nerwilis">Nerwilis</option>
                                    <option value="IPDS">IPDS</option>
                                    <option value="Umum">Umum</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Jabatan</label>
                            <input type="text" name="jabatan" class="form-control" value="{{ old('jabatan') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Unit Kerja</label>
                            <input type="text" name="unit_kerja" class="form-control" value="{{ old('unit_kerja') }}">
                        </div>

                        <div class="mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-primary">Simpan Pegawai</button>
                            <a href="{{ route('pegawai.index') }}" class="btn btn-light ms-2">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
