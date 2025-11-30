{{-- resources/views/tagihan/print.blade.php --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Nota - {{ $tagihan->no_invoice }}</title>
 <style>
    /* Ukuran kertas: lebar 12 cm, tinggi 14 cm */
    @page {
        size: 140mm 120mm;   /* width height */
        margin: 5mm 5mm;     /* margin kecil, biar area nota maksimal */
    }

    body {
        margin: 0;
        padding: 0;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 13px;  /* default semua teks 12 */
        color: #000;
    }

    /* Bungkus isi nota supaya ngepas lebar kertas */
    .wrapper {
        width: 100%;
        box-sizing: border-box;
    }

    /* Header */
    .header {
        display: flex;
        align-items: center;
        margin-bottom: 6px;
		margin-top: 7px;
    }

    .logo {
        width: 65px;
    }

    .header-text {
        flex: 1;
        text-align: center;
    }

    .header-text .title {
        font-weight: bold;
        font-size: 17px;
    }

    .header-text .subtitle {
        font-size: 13px;
    }

    .top-info {
        margin-top: 6px;
        font-size: 13px;
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
        font-size: 13px;
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

    @media print {
        .no-print { display: none; }
    }
</style>

</head>
<body>
<div class="no-print" style="margin-bottom:10px;">
    <button onclick="window.print()">Print</button>
</div>

{{-- panggil partial --}}
@include('tagihan.print_partial', ['tagihan' => $tagihan])

</body>
</html>
