{{-- resources/views/rekap/keuangan.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Rekap Pendapatan & Pengeluaran</h4>
            <div class="text-secondary">Gabungan entri penarikan dan pendapatan/pengeluaran lainnya</div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="border rounded p-3 bg-light h-100">
                <div class="text-secondary">Total Pendapatan</div>
                <div class="fs-4 fw-semibold text-success">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="border rounded p-3 bg-light h-100">
                <div class="text-secondary">Total Pengeluaran</div>
                <div class="fs-4 fw-semibold text-danger">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="border rounded p-3 bg-light h-100">
                <div class="text-secondary">Saldo</div>
                <div class="fs-4 fw-semibold">Rp {{ number_format($saldo, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Rekap Per Bulan</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Bulan</th>
                            <th class="text-end">Pendapatan</th>
                            <th class="text-end">Pengeluaran</th>
                            <th class="text-end">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($monthlyRecap as $row)
                            <tr>
                                <td>{{ $row['label'] }}</td>
                                <td class="text-end text-success">Rp {{ number_format($row['pendapatan'], 0, ',', '.') }}</td>
                                <td class="text-end text-danger">Rp {{ number_format($row['pengeluaran'], 0, ',', '.') }}</td>
                                <td class="text-end fw-semibold">Rp {{ number_format($row['saldo'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-secondary">Belum ada data untuk ditampilkan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Riwayat Transaksi</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Sumber</th>
                            <th>Kategori</th>
                            <th>Jenis</th>
                            <th>Deskripsi</th>
                            <th class="text-end">Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $item)
                            <tr>
                                <td>{{ $item['tanggal']->translatedFormat('d M Y') }}</td>
                                <td>{{ $item['sumber'] }}</td>
                                <td>{{ $item['kategori'] }}</td>
                                <td>
                                    <span class="badge {{ $item['jenis'] === 'pendapatan' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($item['jenis']) }}
                                    </span>
                                </td>
                                <td>{{ $item['deskripsi'] ?? '-' }}</td>
                                <td class="text-end">Rp {{ number_format($item['nominal'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary">Belum ada riwayat transaksi.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
