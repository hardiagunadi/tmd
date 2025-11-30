{{-- resources/views/tagihan/import.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Import Tagihan dari Excel</h4>

    <form action="{{ route('tagihan.import.preview') }}" method="POST" enctype="multipart/form-data" class="mt-3">
        @csrf

        <div class="mb-3">
            <label class="form-label">File Excel</label>
            <input type="file" name="file" class="form-control" required>
            <small class="text-muted">
                File export “Tagihan Belum Bayar” dari sistem TMD.
            </small>
        </div>

        <div class="row g-2 mb-3">
            <div class="col-auto">
                <label class="form-label">Bulan Tagihan</label>
                <select name="bulan_tagihan" class="form-select form-select-sm" required>
                    @foreach(range(1,12) as $i)
                        @php
                            $namaBulan = \Carbon\Carbon::create(null, $i, 1)->translatedFormat('F');
                        @endphp
                        <option value="{{ $i }}" {{ $bulanSekarang == $i ? 'selected' : '' }}>
                            {{ $namaBulan }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-auto">
                <label class="form-label">Tahun Tagihan</label>
                <input type="number" name="tahun_tagihan"
                       class="form-control form-control-sm"
                       value="{{ $tahunSekarang }}" required>
            </div>
        </div>

        <button class="btn btn-primary">Preview Data</button>
    </form>
</div>
@endsection
