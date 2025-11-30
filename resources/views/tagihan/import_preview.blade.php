@extends('layouts.app')

@section('content')
<div class="container">
    <h4>Preview Data Import</h4>
    <p class="text-muted">
        Bulan Tagihan:
        <strong>{{ \Carbon\Carbon::create(null,$bulan,1)->translatedFormat('F') }} {{ $tahun }}</strong><br>
        Secara default semua data akan di-import. Hilangkan centang pada baris yang TIDAK ingin di-import
        atau perbaiki data langsung di tabel ini.
    </p>

    <form action="{{ route('tagihan.import.store') }}" method="POST">
        @csrf

        <div class="table-responsive" style="max-height: 60vh; overflow:auto;">
            <table class="table table-sm table-bordered align-middle">
                <thead class="table-light">
                <tr>
                    <th style="width:30px; text-align:center;">
                        <input type="checkbox" id="check-all" checked>
                    </th>
                    <th style="width:40px;">#</th>
                    <th>Invoice</th>
                    <th>ID Pelanggan</th>
                    <th>Nama</th>
                    <th>Alamat</th>
                    <th>Paket Langganan</th>
                    <th>Tipe Service</th>
                    <th style="width:120px;">Total</th>
                </tr>
                </thead>
                <tbody>
                @foreach($rows as $i => $row)
                    <tr>
                        {{-- checkbox per baris, index sama dengan index array --}}
                        <td class="text-center">
                            <input type="checkbox"
                                   class="row-check"
                                   name="selected_rows[]"
                                   value="{{ $i }}"
                                   checked>
                        </td>

                        <td>{{ $i+1 }}</td>

                        {{-- INVOICE --}}
                        <td>
                            <input type="text"
                                   name="rows[{{ $i }}][invoice]"
                                   class="form-control form-control-sm"
                                   value="{{ $row['invoice'] ?? '' }}">
                        </td>

                        {{-- ID PELANGGAN --}}
                        <td>
                            <input type="text"
                                   name="rows[{{ $i }}][id_pelanggan]"
                                   class="form-control form-control-sm"
                                   value="{{ $row['id_pelanggan'] ?? '' }}">
                        </td>

                        {{-- NAMA --}}
                        <td>
                            <input type="text"
                                   name="rows[{{ $i }}][nama]"
                                   class="form-control form-control-sm"
                                   value="{{ $row['nama'] ?? '' }}">
                        </td>

                        {{-- ALAMAT --}}
                        <td>
                            <input type="text"
                                   name="rows[{{ $i }}][alamat]"
                                   class="form-control form-control-sm"
                                   value="{{ $row['alamat'] ?? '' }}">
                        </td>

                        {{-- PAKET LANGGANAN --}}
                        <td>
                            <input type="text"
                                   name="rows[{{ $i }}][paket_langganan]"
                                   class="form-control form-control-sm"
                                   value="{{ $row['paket_langganan'] ?? '' }}">
                        </td>

                        {{-- TIPE SERVICE --}}
                        <td>
                            <input type="text"
                                   name="rows[{{ $i }}][tipe_service]"
                                   class="form-control form-control-sm"
                                   value="{{ $row['tipe_service'] ?? '' }}">
                        </td>

                        {{-- TOTAL --}}
                        <td>
                            <input type="number"
                                   name="rows[{{ $i }}][total]"
                                   class="form-control form-control-sm text-end"
                                   value="{{ $row['total'] ?? $row['biaya_internet'] ?? 0 }}">
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3 d-flex gap-2">
            <a href="{{ route('tagihan.import.form') }}" class="btn btn-outline-secondary">
                Kembali
            </a>
            <button class="btn btn-success">
                Simpan Data Terpilih ke Database
            </button>
        </div>
    </form>
</div>

{{-- JS sederhana untuk "centang semua" --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const checkAll = document.getElementById('check-all');
        const rowChecks = document.querySelectorAll('.row-check');

        checkAll.addEventListener('change', function () {
            rowChecks.forEach(cb => cb.checked = checkAll.checked);
        });

        rowChecks.forEach(cb => {
            cb.addEventListener('change', function () {
                if (!this.checked) {
                    checkAll.checked = false;
                } else {
                    const allChecked = Array.from(rowChecks).every(c => c.checked);
                    checkAll.checked = allChecked;
                }
            });
        });
    });
</script>
@endsection
