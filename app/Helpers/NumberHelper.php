<?php

if (! function_exists('terbilang')) {
    /**
     * Konversi angka ke teks terbilang bahasa Indonesia.
     *
     * @param int|float|string $angka
     * @return string
     */
    function terbilang($angka): string
    {
        // normalisasi ke integer
        if (is_string($angka)) {
            $angka = preg_replace('/[^\d-]/', '', $angka);
        }
        $angka = (int) $angka;

        if ($angka === 0) {
            return 'nol';
        }

        if ($angka < 0) {
            return 'minus '. terbilang(abs($angka));
        }

        $huruf = [
            0 => 'nol',
            1 => 'satu',
            2 => 'dua',
            3 => 'tiga',
            4 => 'empat',
            5 => 'lima',
            6 => 'enam',
            7 => 'tujuh',
            8 => 'delapan',
            9 => 'sembilan',
            10 => 'sepuluh',
            11 => 'sebelas',
        ];

        // 0 – 11
        if ($angka < 12) {
            return $huruf[$angka];
        }

        // 12 – 19
        if ($angka < 20) {
            return terbilang($angka - 10) . ' belas';
        }

        // 20 – 99
        if ($angka < 100) {
            $puluh = intdiv($angka, 10);
            $sisa  = $angka % 10;

            $result = terbilang($puluh) . ' puluh';
            if ($sisa > 0) {
                $result .= ' ' . terbilang($sisa);
            }

            return $result;
        }

        // 100 – 199
        if ($angka < 200) {
            $sisa = $angka - 100;

            $result = 'seratus';
            if ($sisa > 0) {
                $result .= ' ' . terbilang($sisa);
            }

            return $result;
        }

        // 200 – 999
        if ($angka < 1000) {
            $ratus = intdiv($angka, 100);
            $sisa  = $angka % 100;

            $result = terbilang($ratus) . ' ratus';
            if ($sisa > 0) {
                $result .= ' ' . terbilang($sisa);
            }

            return $result;
        }

        // 1.000 – 1.999
        if ($angka < 2000) {
            $sisa = $angka - 1000;

            $result = 'seribu';
            if ($sisa > 0) {
                $result .= ' ' . terbilang($sisa);
            }

            return $result;
        }

        // 2.000 – 999.999 (ribu)
        if ($angka < 1000000) {
            $ribu = intdiv($angka, 1000);
            $sisa = $angka % 1000;

            $result = terbilang($ribu) . ' ribu';
            if ($sisa > 0) {
                $result .= ' ' . terbilang($sisa);
            }

            return $result;
        }

        // 1.000.000 – 999.999.999 (juta)
        if ($angka < 1000000000) {
            $juta = intdiv($angka, 1000000);
            $sisa = $angka % 1000000;

            $result = terbilang($juta) . ' juta';
            if ($sisa > 0) {
                $result .= ' ' . terbilang($sisa);
            }

            return $result;
        }

        // 1.000.000.000 – 999.999.999.999 (miliar)
        if ($angka < 1000000000000) {
            $miliar = intdiv($angka, 1000000000);
            $sisa   = $angka % 1000000000;

            $result = terbilang($miliar) . ' miliar';
            if ($sisa > 0) {
                $result .= ' ' . terbilang($sisa);
            }

            return $result;
        }

        // 1.000.000.000.000+ (triliun)
        $triliun = intdiv($angka, 1000000000000);
        $sisa    = $angka % 1000000000000;

        $result = terbilang($triliun) . ' triliun';
        if ($sisa > 0) {
            $result .= ' ' . terbilang($sisa);
        }

        return $result;
    }
}
