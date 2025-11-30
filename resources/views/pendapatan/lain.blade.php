{{-- resources/views/pendapatan/lain.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Pendapatan Lain-Lain</h4>

    @if(session('success'))
        <div class="alert alert-success mt-2">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mt-2">{{ session('error') }}</div>
    @endif

    <div class="card mt-3 mb-4">
        <div class="card-body">
            <h6 class="card-title">Catat Transaksi</h6>
            <p class="text-muted small mb-3">Lengkapi data pendapatan dan pengeluaran lain-lain beserta petugas penariknya.</p>

            @php($selectedPetugas = old('petugas', session('last_pendapatan_petugas')))

            <form method="POST" action="{{ route('pendapatan-lain.store') }}" class="row g-3" id="pendapatan-form">
                @csrf
                <div class="col-md-3">
                    <label class="form-label form-label-sm">Petugas Penarik</label>
                    <select name="petugas" class="form-select form-select-sm" required>
                        <option value="">Pilih petugas</option>
                        @foreach($petugasList as $petugas)
                            <option value="{{ $petugas }}" @selected($selectedPetugas === $petugas)>{{ $petugas }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label form-label-sm">Deskripsi</label>
                    <input type="text" name="keterangan" value="{{ old('keterangan') }}" class="form-control form-control-sm" placeholder="Contoh: Biaya admin" required>
                </div>

                <div class="col-md-2">
                    <label class="form-label form-label-sm">Pendapatan (Rp)</label>
                    <input type="number" name="pendapatan" id="pendapatan-input" value="{{ old('pendapatan') }}" class="form-control form-control-sm" min="0" step="100" required>
                </div>

                <div class="col-md-2">
                    <label class="form-label form-label-sm">Pengeluaran (Rp)</label>
                    <input type="number" name="pengeluaran" id="pengeluaran-input" value="{{ old('pengeluaran', 0) }}" class="form-control form-control-sm" min="0" step="100">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <div>
                        <div class="small text-muted">Jumlah Bersih</div>
                        <div id="netto-display" class="fw-semibold">Rp 0</div>
                    </div>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        @foreach($petugasList as $petugas)
            @php($items = $entries->get($petugas) ?? collect())
            @php($total = $totals->get($petugas))
            <div class="col-md-4 mb-3">
                <div class="card h-100">
                    <div class="card-header pb-2">
                        <div class="fw-semibold">{{ $petugas }}</div>
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <div class="d-flex align-items-center gap-2 px-3 py-1 rounded bg-success text-white small">
                                <span class="fw-semibold">Pendapatan</span>
                                <span class="ms-auto">Rp {{ number_format($total['pendapatan'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 px-3 py-1 rounded bg-warning text-dark small">
                                <span class="fw-semibold">Pengeluaran</span>
                                <span class="ms-auto">Rp {{ number_format($total['pengeluaran'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 px-3 py-1 rounded bg-primary text-white small">
                                <span class="fw-semibold">Bersih</span>
                                <span class="ms-auto">Rp {{ number_format($total['bersih'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($items->isEmpty())
                            <p class="text-muted mb-0">Belum ada transaksi.</p>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach($items as $entry)
                                    <div class="list-group-item px-0 d-flex justify-content-between align-items-start gap-2">
                                        <div class="w-100">
                                            <div class="fw-semibold mb-1">{{ $entry->keterangan }}</div>
                                            <div class="d-flex flex-wrap gap-2 mb-1">
                                                <span class="badge bg-success text-white">Pendapatan: Rp {{ number_format($entry->pendapatan, 0, ',', '.') }}</span>
                                                <span class="badge bg-warning text-dark">Pengeluaran: Rp {{ number_format($entry->pengeluaran, 0, ',', '.') }}</span>
                                                <span class="badge bg-primary">Bersih: Rp {{ number_format($entry->pendapatan - $entry->pengeluaran, 0, ',', '.') }}</span>
                                            </div>
                                            <div class="small text-muted">{{ $entry->created_at->translatedFormat('d M Y H:i') }}</div>
                                        </div>
                                        <form action="{{ route('pendapatan-lain.destroy', $entry) }}" method="POST" class="ms-auto">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-link text-danger p-0 small">Hapus</button>
                                        </form>
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
            const pendapatanInput = document.getElementById('pendapatan-input');
            const pengeluaranInput = document.getElementById('pengeluaran-input');
            const nettoDisplay = document.getElementById('netto-display');

            function formatRupiah(value) {
                return new Intl.NumberFormat('id-ID').format(value);
            }

            function updateNetto() {
                const pendapatan = Number(pendapatanInput.value || 0);
                const pengeluaran = Number(pengeluaranInput.value || 0);
                const netto = pendapatan - pengeluaran;

                nettoDisplay.textContent = `Rp ${formatRupiah(netto)}`;
            }

            pendapatanInput.addEventListener('input', updateNetto);
            pengeluaranInput.addEventListener('input', updateNetto);

            updateNetto();
        })();
    </script>
@endpush
@endsection
