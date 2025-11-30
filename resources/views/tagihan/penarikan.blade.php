{{-- resources/views/tagihan/penarikan.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Rekap Penarikan Tagihan</h4>

    @if(session('success'))
        <div class="alert alert-success mt-2">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mt-2">{{ session('error') }}</div>
    @endif

    <div class="card mt-3 mb-4">
        <div class="card-body">
            <h6 class="card-title">Catat Penarikan Baru</h6>
            <p class="text-muted small mb-3">Masukkan nama pelanggan secara manual atau pilih langsung dari daftar tagihan yang sudah dicetak.</p>

            <form method="POST" action="{{ route('penarikan.store') }}" class="row g-3">
                @csrf
                <div class="col-md-3">
                    <label class="form-label form-label-sm">Petugas Penarik</label>
                    <select name="petugas" class="form-select form-select-sm" required>
                        <option value="">Pilih petugas</option>
                        @foreach($petugasList as $petugas)
                            <option value="{{ $petugas }}" @selected(old('petugas') === $petugas)>
                                {{ $petugas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label form-label-sm">Nama Pelanggan</label>
                    <input type="text" name="nama_pelanggan" list="pelanggan-list"
                           class="form-control form-control-sm"
                           placeholder="Ketik manual atau pilih dari daftar"
                           value="{{ old('nama_pelanggan') }}">
                    <datalist id="pelanggan-list">
                        @foreach($printedTagihans as $tagihan)
                            <option value="{{ $tagihan->nama_instansi }}"></option>
                        @endforeach
                    </datalist>
                </div>

                <div class="col-md-5">
                    <label class="form-label form-label-sm">Ambil dari Database (opsional)</label>
                    <select name="tagihan_id" class="form-select form-select-sm">
                        <option value="">-- Pilih tagihan yang sudah dicetak --</option>
                        @foreach($printedTagihans as $tagihan)
                            <option value="{{ $tagihan->id }}" @selected((string) old('tagihan_id') === (string) $tagihan->id)>
                                {{ $tagihan->nama_instansi }} ({{ $tagihan->no_invoice }})
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Jika dipilih, nama pelanggan akan mengikuti data tagihan.</div>
                </div>

                <div class="col-12">
                    <button class="btn btn-primary btn-sm">Simpan Penarikan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        @foreach($petugasList as $petugas)
            @php($items = $penarikans->get($petugas) ?? collect())
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong>{{ $petugas }}</strong>
                        <span class="badge bg-primary">{{ $totals->get($petugas) }} Tagihan</span>
                    </div>
                    <div class="card-body">
                        @if($items->isEmpty())
                            <p class="text-muted mb-0">Belum ada penarikan.</p>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach($items as $item)
                                    <div class="list-group-item px-0">
                                        <div class="fw-semibold">{{ $item->nama_pelanggan }}</div>
                                        @if($item->tagihan)
                                            <div class="small text-muted">Invoice: {{ $item->tagihan->no_invoice }}</div>
                                        @endif
                                        <div class="small text-muted">{{ $item->created_at->translatedFormat('d M Y H:i') }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
