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
            <h6 class="card-title">Catat Penarikan Bulan Ini</h6>
            <p class="text-muted small mb-3">Pilih petugas dan tagihan tercetak pada bulan tagihan ini. Data akan langsung disimpan tanpa tombol simpan.</p>

            <form method="POST" action="{{ route('penarikan.store') }}" class="row g-3" id="penarikan-form">
                @csrf
                <div class="col-md-3">
                    <label class="form-label form-label-sm">Petugas Penarik</label>
                    <select name="petugas" class="form-select form-select-sm" id="petugas-select" required>
                        <option value="">Pilih petugas</option>
                        @foreach($petugasList as $petugas)
                            <option value="{{ $petugas }}" @selected(old('petugas', $petugasPreference) === $petugas)>
                                {{ $petugas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @php($selectedTagihan = $printedTagihans->firstWhere('id', (int) old('tagihan_id')))
                <div class="col-md-9">
                    <label class="form-label form-label-sm">Ambil dari Database</label>
                    <input type="hidden" name="tagihan_id" id="selected-tagihan-id" value="{{ old('tagihan_id') }}">
                    <input
                        type="text"
                        id="tagihan-search"
                        list="tagihan-options"
                        class="form-control form-control-sm"
                        placeholder="Ketik nama pelanggan untuk mencari tagihan"
                        value="{{ $selectedTagihan ? $selectedTagihan->nama_instansi.' ('.$selectedTagihan->no_invoice.') - Rp '.number_format($selectedTagihan->total_bayar, 0, ',', '.') : '' }}"
                        autocomplete="off"
                    >
                    <datalist id="tagihan-options">
                        @foreach($printedTagihans as $tagihan)
                            <option
                                value="{{ $tagihan->nama_instansi }} ({{ $tagihan->no_invoice }}) - Rp {{ number_format($tagihan->total_bayar, 0, ',', '.') }}"
                                data-id="{{ $tagihan->id }}"
                                data-total="{{ number_format($tagihan->total_bayar, 0, ',', '.') }}"
                            ></option>
                        @endforeach
                    </datalist>
                    <div class="form-text" id="tagihan-total-hint">
                        @if($selectedTagihan)
                            Jumlah tagihan: Rp {{ number_format($selectedTagihan->total_bayar, 0, ',', '.') }}
                        @else
                            Pilih tagihan tercetak bulan ini untuk langsung direkap ke petugas.
                        @endif
                    </div>
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
                            <p class="text-muted mb-0">Belum ada penarikan.</p>
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
                                            <div class="small text-muted">{{ $item->created_at->translatedFormat('d M Y H:i') }}</div>
                                        </div>
                                        <div class="ms-auto d-flex flex-column align-items-end gap-2">
                                            <form action="{{ route('penarikan.update', $item) }}" method="POST" class="d-flex align-items-center gap-2 penarikan-nominal-form">
                                                @csrf
                                                @method('PATCH')
                                                <label class="small text-muted mb-0">Terkumpul:</label>
                                                <div class="input-group input-group-sm" style="width: 170px;">
                                                    <span class="input-group-text">Rp</span>
                                                    <input
                                                        type="number"
                                                        name="nominal"
                                                        class="form-control form-control-sm penarikan-nominal-input"
                                                        value="{{ $item->nominal }}"
                                                        min="0"
                                                        max="{{ $item->tagihan?->total_bayar }}"
                                                        step="100"
                                                        required
                                                    >
                                                </div>
                                            </form>
                                            <form action="{{ route('penarikan.destroy', $item) }}" method="POST" class="ms-auto">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-link text-danger p-0 small">Hapus</button>
                                            </form>
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
            const hiddenId = document.getElementById('selected-tagihan-id');
            const totalHint = document.getElementById('tagihan-total-hint');
            const options = Array.from(document.getElementById('tagihan-options').options);
            const form = document.getElementById('penarikan-form');
            const petugasSelect = document.getElementById('petugas-select');
            const nominalForms = document.querySelectorAll('.penarikan-nominal-form');

            const defaultHint = 'Pilih tagihan tercetak bulan ini untuk langsung direkap ke petugas.';

            function syncSelection() {
                const matchedOption = options.find((option) => option.value === searchInput.value);

                if (matchedOption) {
                    hiddenId.value = matchedOption.dataset.id || '';
                    totalHint.textContent = `Jumlah tagihan: Rp ${matchedOption.dataset.total}`;
                    attemptSubmit();
                } else {
                    hiddenId.value = '';
                    totalHint.textContent = defaultHint;
                }
            }

            function attemptSubmit() {
                if (petugasSelect.value && hiddenId.value) {
                    form.submit();
                }
            }

            searchInput.addEventListener('change', syncSelection);
            searchInput.addEventListener('input', () => {
                if (searchInput.value === '') {
                    hiddenId.value = '';
                    totalHint.textContent = defaultHint;
                }
            });

            petugasSelect.addEventListener('change', attemptSubmit);

            nominalForms.forEach((nominalForm) => {
                const input = nominalForm.querySelector('.penarikan-nominal-input');

                if (! input) {
                    return;
                }

                input.addEventListener('change', () => {
                    nominalForm.submit();
                });
            });

            syncSelection();
        })();
    </script>
@endpush
@endsection
