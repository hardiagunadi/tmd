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
            <p class="text-secondary small mb-3">
                Pilih petugas penarik dan pelanggan. Data pelanggan serta jumlah tagihan diambil otomatis dari database; data akan tersimpan begitu kedua pilihan dipenuhi. Pelanggan yang sudah ditugaskan ke petugas lain tidak muncul untuk menghindari duplikasi.
            </p>
            <form action="{{ route('tagihan.penarikan.store') }}" method="POST" class="row g-3" id="penarikan-form">
                @csrf
                <div class="col-md-3">
                    <label class="form-label">Petugas Penarik</label>
                    <select name="petugas" id="petugas_select" class="form-select" required>
                        <option value="">-- Pilih Petugas --</option>
                        @foreach($petugasList as $petugas)
                            <option value="{{ $petugas }}" {{ $selectedPetugas === $petugas ? 'selected' : '' }}>{{ $petugas }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Pelanggan (berdasarkan bulan/tahun tagihan)</label>
                    <input type="hidden" name="tagihan_id" id="tagihan_id" value="{{ old('tagihan_id') }}">
                    <input type="text" class="form-control" id="tagihan_search" list="tagihan_list" placeholder="Ketik nama pelanggan" autocomplete="off">
                    <datalist id="tagihan_list">
                        @foreach($tagihansUntukInput as $tagihan)
                            <option value="{{ $tagihan->nama_instansi }} | Invoice {{ $tagihan->no_invoice }} | {{ $tagihan->nama_bulan_tagihan }} {{ $tagihan->tahun_tagihan }} | Rp {{ number_format($tagihan->total_bayar,0,',','.') }}" data-id="{{ $tagihan->id }}" data-nominal="{{ $tagihan->total_bayar }}" data-search="{{ \Illuminate\Support\Str::lower($tagihan->nama_instansi) }}"></option>
                        @endforeach
                    </datalist>
                    <div class="form-text">Ketik atau pilih nama pelanggan. Data yang sudah ditugaskan tidak akan tampil.</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Nominal (opsional)</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="number" name="nominal" id="nominal_input" class="form-control" min="0" placeholder="Otomatis dari tagihan" value="{{ old('nominal') }}">
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            Daftar Penarikan per Petugas ({{ $bulan ? \Carbon\Carbon::create(null, $bulan, 1)->translatedFormat('F') : 'Semua Bulan' }} {{ $tahun }})
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-3 align-items-stretch mb-4">
                @foreach($totalPerPetugas as $petugas => $total)
                    <div class="border rounded px-3 py-2 bg-light flex-grow-1" style="min-width: 180px;">
                        <div class="small text-secondary">{{ $petugas }}</div>
                        <div class="fw-semibold fs-6">Rp {{ number_format($total,0,',','.') }}</div>
                    </div>
                @endforeach
                <div class="ms-auto text-end">
                    <div class="small text-secondary">Total Semua Petugas</div>
                    <div class="fw-semibold fs-5 text-primary">Rp {{ number_format($totalKeseluruhan,0,',','.') }}</div>
                </div>
            </div>

            <div class="row g-4 row-cols-1 row-cols-xl-2">
                @foreach($petugasList as $petugas)
                    @php
                        $dataPetugas = $penarikanByPetugas->get($petugas, collect());
                        $totalPetugas = $dataPetugas->sum('nominal');
                    @endphp
                    <div class="col">
                        <div class="border rounded h-100 d-flex flex-column">
                            <div class="bg-light px-3 py-2 d-flex justify-content-between align-items-center">
                                <div class="fw-semibold">{{ $petugas }}</div>
                                <div class="text-muted small">Total: Rp {{ number_format($totalPetugas,0,',','.') }}</div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm mb-0 align-middle">
                                    <thead>
                                        <tr>
                                            <th>Pelanggan</th>
                                            <th>Invoice</th>
                                            <th>Bulan Tagihan</th>
                                            <th class="text-end">Nominal</th>
                                            <th width="260">Ubah Penarikan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($dataPetugas as $penarikan)
                                            <tr>
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
                                                                @foreach($petugasList as $petugasOption)
                                                                    <option value="{{ $petugasOption }}" {{ $penarikan->petugas === $petugasOption ? 'selected' : '' }}>{{ $petugasOption }}</option>
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
                                                <td colspan="5" class="text-center text-secondary py-3">Belum ada data penarikan untuk petugas ini.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('penarikan-form');
        const petugasSelect = document.getElementById('petugas_select');
        const tagihanInput = document.getElementById('tagihan_search');
        const tagihanHidden = document.getElementById('tagihan_id');
        const nominalInput = document.getElementById('nominal_input');
        const tagihanList = document.getElementById('tagihan_list').options;

        let isSubmitting = false;

        const isiNominal = (option) => {
            const nominal = option?.dataset?.nominal;
            if (nominal && ! nominalInput.value) {
                nominalInput.value = nominal;
            }
        };

        const submitJikaLengkap = () => {
            if (! isSubmitting && petugasSelect.value && tagihanHidden.value) {
                isSubmitting = true;
                form.submit();
            }
        };

        tagihanInput.addEventListener('input', () => {
            const value = tagihanInput.value;
            const opsiExact = Array.from(tagihanList).find((option) => option.value === value);

            if (opsiExact) {
                tagihanHidden.value = opsiExact.dataset.id;
                isiNominal(opsiExact);
                submitJikaLengkap();

                return;
            }

            const opsiByName = Array.from(tagihanList).filter((option) => option.dataset.search?.includes(value.toLowerCase()));

            if (opsiByName.length === 1) {
                const opsi = opsiByName[0];
                tagihanInput.value = opsi.value;
                tagihanHidden.value = opsi.dataset.id;
                isiNominal(opsi);
                submitJikaLengkap();

                return;
            }

            tagihanHidden.value = '';
        });

        petugasSelect.addEventListener('change', submitJikaLengkap);
    });
</script>
@endpush
