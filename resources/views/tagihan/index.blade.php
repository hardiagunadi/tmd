{{-- resources/views/tagihan/index.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Daftar Tagihan Internet</h4>

    @if(session('success'))
        <div class="alert alert-success mt-2">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mt-2">{{ session('error') }}</div>
    @endif

    <div class="d-flex justify-content-between align-items-end mt-3 mb-3">
        {{-- FILTER BULAN & TAHUN --}}
        <form method="GET" class="d-flex gap-2">
            <div>
                <label class="form-label form-label-sm">Bulan Tagihan</label>
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

            <div>
                <label class="form-label form-label-sm">Tahun</label>
                <input type="number" name="tahun" class="form-control form-control-sm"
                       value="{{ $tahun }}" style="width: 90px;">
            </div>

            <div>
                <label class="form-label form-label-sm">Cari data</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Instansi / invoice / pelanggan"
                       value="{{ $search }}" style="width: 220px;">
            </div>

            <div class="align-self-end">
                <button class="btn btn-sm btn-primary">Filter</button>
            </div>
        </form>

        {{-- IMPORT --}}
        <a href="{{ route('tagihan.import.form') }}" class="btn btn-sm btn-success">
            Import dari Excel
        </a>
    </div>

    {{-- OPSI CETAK MASSAL 10 / 15 / 20 DATA --}}
    <div class="mb-3">
        <form action="{{ route('tagihan.print.batch') }}" method="POST" target="_blank" class="d-flex align-items-end gap-2">
            @csrf
            {{-- ikutkan filter bulan/tahun yang sedang dipilih --}}
            <input type="hidden" name="bulan" value="{{ $bulan }}">
            <input type="hidden" name="tahun" value="{{ $tahun }}">

            <div>
                <label class="form-label form-label-sm">Cetak massal</label>
                <select name="jumlah" class="form-select form-select-sm" required>
                    <option value="10">10 data</option>
                    <option value="15">15 data</option>
                    <option value="20">20 data</option>
                </select>
            </div>

            <div class="form-check ms-2">
                <input class="form-check-input" type="checkbox" value="1" id="hanya_belum" name="hanya_belum" checked>
                <label class="form-check-label" for="hanya_belum" style="font-size: 0.8rem;">
                    Hanya data yang belum cetak
                </label>
            </div>

            <button class="btn btn-sm btn-outline-primary ms-2">
                Cetak Data
            </button>
        </form>
    </div>

    <form action="{{ route('tagihan.print.batch') }}" method="POST" target="_blank" class="card card-body">
        @csrf
        <input type="hidden" name="bulan" value="{{ $bulan }}">
        <input type="hidden" name="tahun" value="{{ $tahun }}">

        <div class="d-flex justify-content-between align-items-center mb-2">
            <p class="mb-0 small text-secondary">Centang data yang ingin dicetak secara manual.</p>
            <button class="btn btn-sm btn-outline-primary">Cetak Manual</button>
        </div>

        <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th width="40" class="text-center">Pilih</th>
                    <th>#</th>
                    <th>Instansi</th>
                    <th>No Invoice</th>
                    <th>No Pelanggan</th>
                    <th>Bulan Tagihan</th>
                    <th>Total</th>
                    <th>Status Cetak</th>
                    <th width="80">Cetak</th>
                </tr>
            </thead>
            <tbody>
            @foreach($tagihans as $row)
                <tr>
                    <td class="text-center">
                        <input type="checkbox" class="form-check-input" name="selected[]" value="{{ $row->id }}">
                    </td>
                    <td>{{ $loop->iteration + ($tagihans->currentPage()-1)*$tagihans->perPage() }}</td>
                    <td>{{ $row->nama_instansi }}</td>
                    <td>{{ $row->no_invoice }}</td>
                    <td>{{ $row->no_pelanggan }}</td>
                    <td>{{ $row->nama_bulan_tagihan }} {{ $row->tahun_tagihan }}</td>
                    <td class="text-end">{{ number_format($row->total_bayar,0,',','.') }}</td>
                    <td>
                        @if($row->printed_at)
                            <span class="badge bg-success">Sudah cetak</span>
                        @else
                            <span class="badge bg-secondary">Belum cetak</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <a href="{{ route('tagihan.print', $row) }}" target="_blank"
                           class="btn btn-sm btn-outline-dark">
                            Cetak
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </form>

    {{ $tagihans->withQueryString()->links() }}
</div>
@endsection
