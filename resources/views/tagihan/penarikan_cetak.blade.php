{{-- resources/views/tagihan/penarikan_cetak.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center">
        <h4>Rekap Penarikan Tagihan Tercetak</h4>
        <a href="{{ route('penarikan.index') }}" class="btn btn-outline-secondary btn-sm">Kembali ke Rekap Cepat</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success mt-2">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mt-2">{{ session('error') }}</div>
    @endif

    <div class="card mt-3 mb-4">
        <div class="card-body">
            <h6 class="card-title">Catat Penarikan Tagihan Tercetak</h6>
            <p class="text-muted small mb-3">Masukkan nama pelanggan secara manual atau pilih tagihan yang sudah dicetak dari database. Nominal otomatis mengikuti total tagihan jika diambil dari database.</p>

            <form method="POST" action="{{ route('penarikan.printed.store') }}" class="row g-3" id="penarikan-printed-form">
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

                <div class="col-md-5">
                    <label class="form-label form-label-sm">Nama Pelanggan</label>
                    <input
                        type="text"
                        name="nama_pelanggan"
                        id="nama-pelanggan"
                        class="form-control form-control-sm"
                        value="{{ old('nama_pelanggan') }}"
                        placeholder="Tulis nama pelanggan atau otomatis dari database"
                        required
                    >
                    <div class="form-text">Isi manual atau biarkan terisi otomatis saat memilih tagihan.</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label form-label-sm">Ambil dari Database (Opsional)</label>
                    <input type="hidden" name="tagihan_id" id="tagihan-id" value="{{ old('tagihan_id') }}">
                    <input
                        type="text"
                        name="tagihan_label"
                        id="tagihan-search"
                        list="printed-tagihan-options"
                        class="form-control form-control-sm"
                        placeholder="Cari nama pelanggan atau invoice"
                        value="{{ old('tagihan_label') }}"
                        autocomplete="off"
                    >
                    <datalist id="printed-tagihan-options">
                        @foreach($printedTagihans as $tagihan)
                            <option
                                value="{{ $tagihan->nama_instansi }} ({{ $tagihan->no_invoice }})"
                                data-id="{{ $tagihan->id }}"
                                data-name="{{ $tagihan->nama_instansi }}"
                                data-total="{{ $tagihan->total_bayar }}"
                            ></option>
                        @endforeach
                    </datalist>
                    <div class="form-text" id="nominal-hint">Pilih tagihan tercetak untuk mengisi data otomatis.</div>
                </div>

                <div class="col-md-3">
                    <label class="form-label form-label-sm">Nominal Penarikan</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">Rp</span>
                        <input
                            type="number"
                            class="form-control form-control-sm"
                            name="nominal"
                            id="nominal-input"
                            value="{{ old('nominal', 0) }}"
                            min="0"
                            step="100"
                            required
                        >
                    </div>
                    <div class="form-text">Nilai akan mengikuti total tagihan terpilih atau isi manual.</div>
                </div>

                <div class="col-md-3 align-self-end">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Simpan Rekap</button>
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
                        <div>
                            <strong>{{ $petugas }}</strong>
                            <span class="badge bg-secondary ms-2">{{ $totals->get($petugas)['count'] ?? 0 }} Tagihan</span>
                        </div>
                        <span class="badge bg-primary">Rp {{ number_format($totals->get($petugas)['amount'] ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="card-body">
                        @if($items->isEmpty())
                            <p class="text-muted mb-0">Belum ada rekap penarikan tercatat.</p>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach($items as $item)
                                    <div class="list-group-item px-0 d-flex justify-content-between align-items-start gap-2">
                                        <div>
                                            <div class="fw-semibold">{{ $item->nama_pelanggan }}</div>
                                            @if($item->tagihan)
                                                <div class="small text-muted">Invoice: {{ $item->tagihan->no_invoice }}</div>
                                                <div class="small text-muted">Tagihan: Rp {{ number_format($item->tagihan->total_bayar, 0, ',', '.') }}</div>
                                            @endif
                                            <div class="small text-muted">Tercatat: {{ $item->created_at->translatedFormat('d M Y H:i') }}</div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-semibold">Rp {{ number_format($item->nominal, 0, ',', '.') }}</div>
                                            <div class="small text-muted">Total ditarik</div>
                                        </div>
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

@push('scripts')
    <script>
        (function () {
            const searchInput = document.getElementById('tagihan-search');
            const tagihanIdInput = document.getElementById('tagihan-id');
            const namaInput = document.getElementById('nama-pelanggan');
            const nominalInput = document.getElementById('nominal-input');
            const nominalHint = document.getElementById('nominal-hint');
            const options = Array.from(document.querySelectorAll('#printed-tagihan-options option'));
            const defaultHint = 'Pilih tagihan tercetak untuk mengisi data otomatis.';

            function syncSelection() {
                const matchedOption = options.find((option) => option.value === searchInput.value);

                if (matchedOption) {
                    tagihanIdInput.value = matchedOption.dataset.id || '';
                    namaInput.value = matchedOption.dataset.name || namaInput.value;
                    nominalInput.value = matchedOption.dataset.total || nominalInput.value;
                    nominalHint.textContent = `Jumlah tagihan: Rp ${Number(matchedOption.dataset.total || 0).toLocaleString('id-ID')}`;
                } else {
                    tagihanIdInput.value = '';
                    nominalHint.textContent = defaultHint;
                }
            }

            searchInput.addEventListener('change', syncSelection);
            searchInput.addEventListener('input', () => {
                if (searchInput.value === '') {
                    tagihanIdInput.value = '';
                    nominalHint.textContent = defaultHint;
                }
            });

            syncSelection();
        })();
    </script>
@endpush
@endsection
