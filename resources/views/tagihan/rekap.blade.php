{{-- resources/views/tagihan/rekap.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Rekap Penarikan Tagihan</h4>

    <form method="GET" class="card card-body mt-3 mb-3">
        <div class="row g-3 align-items-end">
            <div class="col-auto">
                <label class="form-label form-label-sm mb-1">Bulan Tagihan</label>
                <select name="bulan" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach(range(1,12) as $i)
                        @php
                            $namaBulan = \Carbon\Carbon::create(null, $i, 1)->translatedFormat('F');
                        @endphp
                        <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>
                            {{ $namaBulan }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-auto">
                <label class="form-label form-label-sm mb-1">Tahun</label>
                <input type="number" name="tahun" class="form-control form-control-sm" value="{{ $tahun }}" style="width: 90px;">
            </div>

            <div class="col-auto">
                <label class="form-label form-label-sm mb-1">Cari Pelanggan/Invoice</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Instansi / invoice / pelanggan"
                       value="{{ $search }}" style="width: 240px;">
            </div>

            <div class="col-auto">
                <button class="btn btn-sm btn-primary">Ambil dari Database</button>
            </div>
        </div>
    </form>

    <div class="d-flex gap-3 mb-3">
        <div class="card">
            <div class="card-body p-3">
                <div class="text-secondary small">Total Pelanggan</div>
                <div class="fw-bold fs-4">{{ number_format($totalPelanggan) }}</div>
            </div>
        </div>
        <div class="card">
            <div class="card-body p-3">
                <div class="text-secondary small">Total Tagihan</div>
                <div class="fw-bold fs-5">Rp {{ number_format($totalBayar,0,',','.') }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-sm table-striped mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Instansi</th>
                        <th>No Invoice</th>
                        <th>No Pelanggan</th>
                        <th>Bulan Tagihan</th>
                        <th class="text-end">Total</th>
                        <th>Status Cetak</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($tagihans as $index => $tagihan)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $tagihan->nama_instansi }}</td>
                        <td>{{ $tagihan->no_invoice }}</td>
                        <td>{{ $tagihan->no_pelanggan }}</td>
                        <td>{{ $tagihan->nama_bulan_tagihan }} {{ $tagihan->tahun_tagihan }}</td>
                        <td class="text-end">{{ number_format($tagihan->total_bayar,0,',','.') }}</td>
                        <td>
                            @if($tagihan->printed_at)
                                <span class="badge bg-success">Sudah cetak</span>
                            @else
                                <span class="badge bg-secondary">Belum cetak</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-secondary">Tidak ada data pada rentang ini.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
