{{-- resources/views/tagihan/print_batch.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Nota Massal</title>
    <style>
        /* Sama seperti print.blade, biar tampilan konsisten */
        @page {
            size: 241mm 140mm;
            margin: 5mm 7mm;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #000;
        }

        .wrapper {
            width: 100%;
            box-sizing: border-box;
        }

        .header {
            display: flex;
            align-items: center;
            margin-bottom: 6px;
        }

        .logo {
            width: 55px;
        }

        .header-text {
            flex: 1;
            text-align: center;
        }

        .header-text .title {
            font-weight: bold;
            font-size: 16px;
        }

        .header-text .subtitle {
            font-size: 12px;
        }

        .top-info {
            margin-top: 6px;
            font-size: 12px;
        }
        .top-info table {
            width: 100%;
        }
        .top-info td {
            vertical-align: top;
        }

        .mt-2 { margin-top: 4px; }
        .mt-3 { margin-top: 6px; }
        .mt-4 { margin-top: 8px; }

        .bold { font-weight: bold; }
        .text-right { text-align: right; }
        .text-underline { text-decoration: underline; }

        .amount-table {
            width: 100%;
            margin-top: 6px;
            font-size: 12px;
        }
        .amount-table td {
            padding: 1px 0;
        }
        .amount-table .label {
            width: 60%;
        }
        .amount-table .value {
            width: 40%;
        }
        .total-row {
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            font-weight: bold;
        }

        .nominal-underline {
            display: inline-block;
            min-width: 60px;
            border-bottom: 1px solid #000;
            padding-bottom: 1px;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body onload="window.print()">

@foreach($tagihans as $tagihan)
    @include('tagihan.print_partial', ['tagihan' => $tagihan])

    @if(!$loop->last)
        <div class="page-break"></div>
    @endif
@endforeach

</body>
</html>
