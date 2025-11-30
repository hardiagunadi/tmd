{{-- resources/views/lainnya/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-3">Entri Pendapatan & Pengeluaran Lain-lain</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <div class="fw-semibold mb-1">Terjadi kesalahan:</div>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Catat Pendapatan Penjualan</div>
                <div class="card-body">
                    <form action="{{ route('lainnya.store') }}" method="POST" class="row g-3">
                        @csrf
                        <input type="hidden" name="jenis" value="pendapatan">
                        <input type="hidden" name="kategori" value="Penjualan Barang">

                        <div class="col-12">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', now()->toDateString()) }}" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Sumber / Customer</label>
                            <input type="text" name="pihak" class="form-control" placeholder="Contoh: Penjualan router ke PT ABC" value="{{ old('pihak') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="deskripsi" class="form-control" placeholder="Nama barang atau catatan lain" value="{{ old('deskripsi') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Nominal Pendapatan</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="nominal" min="0" class="form-control" value="{{ old('nominal') }}" required>
                            </div>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Simpan Pendapatan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Catat Pengeluaran Lain-lain</div>
                <div class="card-body">
                    <form action="{{ route('lainnya.store') }}" method="POST" class="row g-3">
                        @csrf
                        <input type="hidden" name="jenis" value="pengeluaran">

                        <div class="col-12">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal', now()->toDateString()) }}" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Kategori</label>
                            <select name="kategori" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                <option value="Gaji Petugas/Teknisi" {{ old('kategori') === 'Gaji Petugas/Teknisi' ? 'selected' : '' }}>Gaji Petugas/Teknisi</option>
                                <option value="Pembelian Barang/Jasa Lainnya" {{ old('kategori') === 'Pembelian Barang/Jasa Lainnya' ? 'selected' : '' }}>Pembelian Barang/Jasa Lainnya</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Penerima / Supplier</label>
                            <input type="text" name="pihak" class="form-control" placeholder="Nama petugas/teknisi atau supplier" value="{{ old('pihak') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="deskripsi" class="form-control" placeholder="Detail pengeluaran" value="{{ old('deskripsi') }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Nominal Pengeluaran</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="number" name="nominal" min="0" class="form-control" value="{{ old('nominal') }}" required>
                            </div>
                        </div>

                        <div class="col-12">
                            <button type="submit" class="btn btn-danger">Simpan Pengeluaran</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="border rounded p-3 bg-light h-100">
                <div class="text-secondary">Total Pendapatan</div>
                <div class="fs-4 fw-semibold text-success">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="border rounded p-3 bg-light h-100">
                <div class="text-secondary">Total Pengeluaran</div>
                <div class="fs-4 fw-semibold text-danger">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Riwayat Pendapatan & Pengeluaran</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jenis</th>
                            <th>Kategori</th>
                            <th>Pihak Terkait</th>
                            <th>Deskripsi</th>
                            <th class="text-end">Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $trx)
                            <tr>
                                <td>{{ $trx->tanggal->translatedFormat('d M Y') }}</td>
                                <td>
                                    <span class="badge {{ $trx->jenis === 'pendapatan' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($trx->jenis) }}
                                    </span>
                                </td>
                                <td>{{ $trx->kategori }}</td>
                                <td>{{ $trx->pihak ?? '-' }}</td>
                                <td>{{ $trx->deskripsi ?? '-' }}</td>
                                <td class="text-end">Rp {{ number_format($trx->nominal, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary">Belum ada data pendapatan/pengeluaran lainnya.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
