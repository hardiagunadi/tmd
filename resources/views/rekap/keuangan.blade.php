{{-- resources/views/rekap/keuangan.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Rekap Keuangan Bulan {{ $periodeLabel }}</h4>

    @if(session('success'))
        <div class="alert alert-success mt-2">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mt-2">{{ session('error') }}</div>
    @endif

    <div class="row g-3 mt-2">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Pendapatan Penarikan</span>
                        <span class="fw-semibold">Rp {{ number_format($summary['pendapatan_penarikan'], 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Pendapatan Lain</span>
                        <span class="fw-semibold">Rp {{ number_format($summary['pendapatan_lain'], 0, ',', '.') }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-semibold">Pendapatan Kotor</span>
                        <span class="fw-bold text-success">Rp {{ number_format($summary['pendapatan_kotor'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Pengeluaran Lain</span>
                        <span class="fw-semibold">Rp {{ number_format($summary['pengeluaran_lain'], 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Gaji Petugas</span>
                        <span class="fw-semibold">Rp {{ number_format($summary['gaji'], 0, ',', '.') }}</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-semibold">Total Pengeluaran</span>
                        <span class="fw-bold text-danger">Rp {{ number_format($summary['pengeluaran'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body d-flex flex-column justify-content-center">
                    <div class="text-muted mb-1">Pendapatan Bersih Bulan Ini</div>
                    <div class="display-6 fw-bold">Rp {{ number_format($summary['bersih'], 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <h6 class="card-title">Gaji Petugas</h6>
            <p class="text-muted small">Isi nominal gaji untuk bulan berjalan. Nilai akan disimpan per petugas.</p>
            <form method="POST" action="{{ route('rekap-keuangan.gaji') }}" class="row g-3 align-items-end">
                @csrf
                @foreach($petugasList as $petugas)
                    <div class="col-md-3">
                        <label class="form-label form-label-sm">{{ $petugas }}</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">Rp</span>
                            <input
                                type="number"
                                name="gaji[{{ $petugas }}]"
                                value="{{ old('gaji.'.$petugas, $gajiRecords->get($petugas)?->nominal) }}"
                                class="form-control form-control-sm"
                                min="0"
                                step="1000"
                            >
                        </div>
                    </div>
                @endforeach
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm">Simpan Gaji</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">
            <h6 class="card-title">Rincian Per Petugas</h6>
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Petugas</th>
                            <th>Penarikan</th>
                            <th>Pendapatan Lain (Bersih)</th>
                            <th>Gaji</th>
                            <th>Subtotal Bersih</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($petugasList as $petugas)
                            @php($penarikan = (int) ($penarikanTotals[$petugas] ?? 0))
                            @php($lain = $pendapatanLainPerPetugas[$petugas]['bersih'] ?? 0)
                            @php($gaji = $gajiRecords->get($petugas)?->nominal ?? 0)
                            <tr>
                                <td class="fw-semibold">{{ $petugas }}</td>
                                <td>Rp {{ number_format($penarikan, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($lain, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($gaji, 0, ',', '.') }}</td>
                                <td class="fw-semibold">Rp {{ number_format($penarikan + $lain - $gaji, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-md-6">
                    <div class="alert alert-secondary mb-0">
                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold">Total Pendapatan Lain</span>
                            <span>Rp {{ number_format($pendapatanLainSummary['pendapatan'], 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold">Total Pengeluaran Lain</span>
                            <span>Rp {{ number_format($pendapatanLainSummary['pengeluaran'], 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <span class="fw-semibold">Bersih Lain-Lain</span>
                            <span class="fw-bold">Rp {{ number_format($pendapatanLainSummary['bersih'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="alert alert-info mb-0">
                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold">Total Penarikan</span>
                            <span>Rp {{ number_format($summary['pendapatan_penarikan'], 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold">Total Gaji</span>
                            <span>Rp {{ number_format($summary['gaji'], 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mt-1">
                            <span class="fw-semibold">Pendapatan Bersih</span>
                            <span class="fw-bold">Rp {{ number_format($summary['bersih'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
