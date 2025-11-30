{{-- resources/views/tagihan/print_partial.blade.php --}}
<div class="wrapper">
    <div class="header">
        <img src="{{ asset('images/logo-tmd.png') }}" class="logo" alt="Logo">
        <div class="header-text">
            <div class="title">PT TUNAS MEDIA DATA</div>
            <div class="subtitle">
                Jl. Kertek KM.3 Desa Semayu, Kecamatan Kertek<br>
                Kabupaten Wonosobo, 56351 | HP. 082220243698
            </div>
        </div>
    </div>

    <div class="top-info">
        <table>
            <tr>
                <td style="width:50%;">
                    <div>Tgl. Jatuh Tempo</div>
                    <div class="bold">
                        {{ $tagihan->tanggal_jatuh_tempo->translatedFormat('d F Y') }}
                    </div>
                </td>
                <td style="width:50%;">
                    <table style="width:100%;">
                        <tr>
                            <td>No. Invoice</td>
                            <td>: {{ $tagihan->no_invoice }}</td>
                        </tr>
                        <tr>
                            <td>Tagihan Bulan</td>
                            <td>: {{ $tagihan->nama_bulan_tagihan }}</td>
                        </tr>
                        <tr>
                            <td>Nomor Pelanggan</td>
                            <td>: {{ $tagihan->no_pelanggan }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
<br><br>
    <div class="mt-3">
        <div class="bold">{{ $tagihan->nama_instansi }}</div>
        <div>
            {{ $tagihan->alamat_instansi }}
        </div>
    </div>
	<br><br>
    <table class="amount-table mt-3">
        <tr>
            <td class="label bold">Biaya Langganan</td>
            <td class="value text-right">
                {{ number_format($tagihan->biaya_langganan,0,',','.') }},00
            </td>
        </tr>
        <tr>
            <td class="label">
                <em>{{ $tagihan->deskripsi_paket ?? 'High Speed Internet Package Service' }}</em>
            </td>
            <td></td>
        </tr>
        <tr>
            <td class="label">Biaya Admin Bank/Loket</td>
            <td class="value text-right">
                <span class="nominal-underline">
                    {{ number_format($tagihan->biaya_admin,0,',','.') }}
                </span>
            </td>
        </tr>
        <tr class="total-row">
            <td class="label">Total Dibayar</td>
            <td class="value text-right">
                {{ number_format($tagihan->total_bayar,0,',','.') }},00
            </td>
        </tr>
    </table>

    <div class="mt-3 bold">
        {{ ucfirst(terbilang($tagihan->total_bayar)) }} rupiah
    </div>
</div>
