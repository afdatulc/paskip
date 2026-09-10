<?php

namespace App\Services;

/**
 * Service class untuk menghitung persentase capaian kinerja.
 * Menangani polarisasi IKU (positif/negatif), division by zero, dan capping.
 * 
 * Referensi: Pedoman SAKIP KemenPAN-RB & BPS.
 */
class CapaianCalculator
{
    /**
     * Batas maksimal capaian (capping) agar rata-rata tidak terdistorsi.
     */
    public const BATAS_MAKSIMAL = 120;

    /**
     * Hitung persentase capaian berdasarkan polarisasi IKU.
     *
     * @param float|null $target     Angka target
     * @param float|null $realisasi  Angka realisasi aktual
     * @param string     $polarisasi 'positif' atau 'negatif'
     * @param float      $batasMaksimal Batas capping (default 120%)
     * @return float Persentase capaian (0 - batasMaksimal)
     */
    public static function hitung(
        ?float $target,
        ?float $realisasi,
        string $polarisasi = 'positif',
        float $batasMaksimal = self::BATAS_MAKSIMAL
    ): ?float {
        // Handle data belum diinput / kosong
        if (is_null($target) || is_null($realisasi)) {
            return null;
        }

        $capaian = 0;

        if (strtolower($polarisasi) === 'positif') {
            // Makin tinggi makin baik
            // Rumus Excel: IF(AND(Target=0,Realisasi>0),120,IF(OR(Target=0,Realisasi<=0),"-",MIN(Realisasi/Target*100,120)))
            if ($target == 0 && $realisasi > 0) {
                $capaian = $batasMaksimal;
            } elseif ($target == 0 || $realisasi <= 0) {
                return null;
            } else {
                $capaian = ($realisasi / $target) * 100;
            }
        } elseif (strtolower($polarisasi) === 'negatif') {
            // Makin rendah makin baik
            // Jika target 0 dan realisasi 0, tercapai sempurna
            if ($target == 0 && $realisasi == 0) {
                return null; // Atau batas maksimal, tapi biasanya tidak bisa dihitung
            } elseif ($realisasi == 0) {
                // Target tercapai sempurna (0 error/kemiskinan)
                $capaian = $batasMaksimal;
            } else {
                $capaian = ($target / $realisasi) * 100;
            }
        }

        // Capping nilai maksimal
        if ($capaian > $batasMaksimal) {
            $capaian = $batasMaksimal;
        }

        // Floor at 0 (tidak boleh negatif)
        if ($capaian < 0) {
            $capaian = 0;
        }

        return round($capaian, 2);
    }

    /**
     * Hitung rata-rata capaian untuk sekumpulan IKU (Capaian Sasaran Strategis).
     *
     * @param array $capaianList Array berisi nilai persentase capaian masing-masing IKU
     * @return float Rata-rata capaian
     */
    public static function rataRataCapaian(array $capaianList): float
    {
        if (empty($capaianList)) {
            return 0;
        }

        return round(array_sum($capaianList) / count($capaianList), 2);
    }
}
