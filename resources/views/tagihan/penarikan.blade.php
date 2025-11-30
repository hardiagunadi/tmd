{{-- resources/views/tagihan/penarikan.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Rekap Penarikan Tagihan {{ $currentMonthName }}</h4>

    @if(session('success'))
        <div class="alert alert-success mt-2">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mt-2">{{ session('error') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <form method="POST" action="{{ route('penarikan.store') }}" class="row g-3 align-items-end">
                @csrf
                <div class="col-lg-6">
                    <label class="form-label form-label-sm">Pilih pelanggan (tagihan sudah dicetak)</label>
                    <select name="tagihan_id" id="tagihan_id" class="form-select form-select-sm" required>
                        <option value="">-- Pilih pelanggan bulan ini --</option>
                        @foreach($printedTagihans as $tagihan)
                            <option value="{{ $tagihan->id }}" data-nominal="{{ $tagihan->total_bayar }}"
                                {{ old('tagihan_id') == $tagihan->id ? 'selected' : '' }}>
                                {{ $tagihan->nama_instansi }} — {{ $tagihan->no_invoice }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Daftar otomatis disaring untuk tagihan bulan {{ $currentMonthName }} yang belum ditarik.</div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="form-label form-label-sm">Petugas penarikan</label>
                    <select name="petugas" class="form-select form-select-sm" required>
                        <option value="">Pilih petugas</option>
                        @foreach($petugasList as $petugas)
                            <option value="{{ $petugas }}" {{ old('petugas') === $petugas ? 'selected' : '' }}>
                                {{ $petugas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="form-label form-label-sm">Nominal otomatis</label>
                    <input type="text" id="nominal" class="form-control form-control-sm" value="-" readonly>
                    <div class="form-text">Nilai mengikuti total tagihan pelanggan.</div>
                </div>

                <div class="col-12">
                    <button class="btn btn-sm btn-primary">Simpan penarikan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-3">
        @foreach($petugasSummary as $petugas => $ringkasan)
            <div class="col-md-3 col-sm-6">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="text-secondary mb-1 small">Petugas</p>
                        <h6 class="fw-bold">{{ $petugas }}</h6>
                        <div class="d-flex justify-content-between mt-3">
                            <div>
                                <div class="text-secondary small">{{ $ringkasan['jumlah'] }} Tagihan</div>
                                <div class="fs-5">Rp {{ number_format($ringkasan['total_nominal'],0,',','.') }}</div>
                            </div>
                            <div class="text-end">
                                <div class="text-secondary small">Bulan {{ $currentMonthName }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @foreach($penarikansByPetugas as $petugas => $entries)
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold">{{ $petugas }}</span>
                <span class="badge bg-primary">{{ $entries->count() }} penarikan</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nama pelanggan</th>
                                <th>No Invoice</th>
                                <th width="230">Nominal</th>
                                <th width="160">Diinput</th>
                                <th width="120" class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($entries as $entry)
                            <tr>
                                <td>{{ $entry->nama_pelanggan }}</td>
                                <td>{{ $entry->tagihan?->no_invoice ?? '-' }}</td>
                                <td>
                                    <div class="fw-semibold">Rp {{ number_format($entry->nominal,0,',','.') }}</div>
                                    <form method="POST" action="{{ route('penarikan.update', $entry) }}" class="d-flex gap-2 align-items-center mt-2">
                                        @csrf
                                        @method('PATCH')
                                        <input type="number" name="nominal" class="form-control form-control-sm" min="0" step="1000"
                                               value="{{ $entry->nominal }}" style="width: 120px;">
                                        <button class="btn btn-sm btn-outline-primary">Update</button>
                                    </form>
                                </td>
                                <td>{{ optional($entry->created_at)->format('d/m/Y H:i') }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('penarikan.destroy', $entry) }}" onsubmit="return confirm('Hapus data penarikan ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-secondary py-3">Belum ada penarikan untuk {{ $petugas }}.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endforeach
</div>
@endsection

@push('scripts')
<script>
    const selectTagihan = document.getElementById('tagihan_id');
    const inputNominal = document.getElementById('nominal');

    function syncNominal() {
        const selected = selectTagihan.options[selectTagihan.selectedIndex];
        const nominal = selected ? selected.getAttribute('data-nominal') : '';

        inputNominal.value = nominal ? `Rp ${Number(nominal).toLocaleString('id-ID')}` : '-';
    }

    if (selectTagihan && inputNominal) {
        selectTagihan.addEventListener('change', syncNominal);

        if (selectTagihan.value) {
            syncNominal();
        }
    }
</script>
@endpush
