{{-- resources/views/tagihan/penarikan.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Entri Penarikan Pembayaran Tagihan Internet</h4>

    @if(session('success'))
        <div class="alert alert-success mt-2">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mt-2">{{ session('error') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label form-label-sm">Bulan Tagihan</label>
                    <select name="bulan" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        @foreach(range(1,12) as $i)
                            @php
                                $namaBulan = \Carbon\Carbon::create(null, $i, 1)->translatedFormat('F');
                            @endphp
                            <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>{{ $namaBulan }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label form-label-sm">Tahun</label>
                    <input type="number" name="tahun" class="form-control form-control-sm" value="{{ $tahun }}">
                </div>
                <div class="col-md-3 align-self-end">
                    <button class="btn btn-sm btn-primary">Terapkan Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Tambah Entri Penarikan</div>
        <div class="card-body">
            <p class="text-secondary small">
                Pilih petugas penarik dan pelanggan. Data pelanggan serta jumlah tagihan diambil otomatis dari database; Anda dapat menyesuaikan nominal jika pembayaran berbeda.
            </p>
            <form action="{{ route('tagihan.penarikan.store') }}" method="POST" class="row g-3">
                @csrf
                <div class="col-md-3">
                    <label class="form-label">Petugas Penarik</label>
                    <select name="petugas" class="form-select" required>
                        <option value="">-- Pilih Petugas --</option>
                        @foreach($petugasList as $petugas)
                            <option value="{{ $petugas }}" {{ old('petugas') === $petugas ? 'selected' : '' }}>{{ $petugas }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Pelanggan (berdasarkan bulan/tahun tagihan)</label>
                    <select name="tagihan_id" id="tagihan_id" class="form-select" required data-selected-nominal="{{ old('nominal') }}">
                        <option value="">-- Pilih Pelanggan --</option>
                        @foreach($tagihans as $tagihan)
                            <option value="{{ $tagihan->id }}" data-nominal="{{ $tagihan->total_bayar }}" {{ old('tagihan_id') == $tagihan->id ? 'selected' : '' }}>{{ $tagihan->nama_instansi }} | Invoice {{ $tagihan->no_invoice }} | {{ $tagihan->nama_bulan_tagihan }} {{ $tagihan->tahun_tagihan }} | Rp {{ number_format($tagihan->total_bayar,0,',','.') }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nominal (opsional)</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="nominal" id="nominal_input" class="form-control" min="0" placeholder="Otomatis dari tagihan" value="{{ old('nominal') }}">
                    </div>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary">Simpan ke Petugas</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            Daftar Penarikan per Petugas ({{ $bulan ? \Carbon\Carbon::create(null, $bulan, 1)->translatedFormat('F') : 'Semua Bulan' }} {{ $tahun }})
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Petugas</th>
                            <th>Pelanggan</th>
                            <th>Invoice</th>
                            <th>Bulan Tagihan</th>
                            <th class="text-end">Nominal</th>
                            <th width="260">Ubah Penarikan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($penarikans as $penarikan)
                            <tr>
                                <td>{{ $penarikan->petugas }}</td>
                                <td>{{ $penarikan->nama_pelanggan }}</td>
                                <td>{{ $penarikan->tagihan?->no_invoice ?? '-' }}</td>
                                <td>{{ $penarikan->tagihan ? $penarikan->tagihan->nama_bulan_tagihan.' '.$penarikan->tagihan->tahun_tagihan : '-' }}</td>
                                <td class="text-end">Rp {{ number_format($penarikan->nominal,0,',','.') }}</td>
                                <td>
                                    <form action="{{ route('tagihan.penarikan.update', $penarikan) }}" method="POST" class="row g-2 align-items-center">
                                        @csrf
                                        @method('PUT')
                                        <div class="col-md-4">
                                            <select name="petugas" class="form-select form-select-sm" required>
                                                @foreach($petugasList as $petugas)
                                                    <option value="{{ $petugas }}" {{ $penarikan->petugas === $petugas ? 'selected' : '' }}>{{ $petugas }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text">Rp</span>
                                                <input type="number" name="nominal" class="form-control" min="0" value="{{ $penarikan->nominal }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <button class="btn btn-sm btn-outline-primary w-100">Update</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-3">Belum ada data penarikan untuk periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const selectTagihan = document.getElementById('tagihan_id');
        const nominalInput = document.getElementById('nominal_input');
        const storedNominal = selectTagihan.dataset.selectedNominal;

        const setNominal = () => {
            const option = selectTagihan.options[selectTagihan.selectedIndex];
            const nominal = option?.dataset?.nominal || '';

            if (! storedNominal && nominal) {
                nominalInput.value = nominal;
            }
        };

        selectTagihan.addEventListener('change', setNominal);
        setNominal();
    });
</script>
@endpush
