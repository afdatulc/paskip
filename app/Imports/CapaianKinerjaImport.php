<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\Indikator;
use App\Models\Realisasi;
use App\Models\CapaianKinerja;

class CapaianKinerjaImport implements ToCollection, WithHeadingRow
{
    protected $tahun;
    protected $triwulan;

    public function __construct($tahun, $triwulan)
    {
        $this->tahun = $tahun;
        $this->triwulan = $triwulan;
    }

    public function collection(Collection $rows)
    {
        $batasWaktu = $this->tahun . '-12-31';

        foreach ($rows as $row) {
            // Asumsi header excel: kode_indikator, realisasi_tw, realisasi_x, realisasi_y,
            // link_bukti_dukung_kinerja, link_bukti_dukung_rencana_tindak_lanjut_triwulan_sebelumnya
            
            $kode = trim($row['kode_indikator']);
            if (empty($kode)) continue;

            $indikator = Indikator::where('kode', $kode)->first();
            if (!$indikator) continue;

            // Validasi PIC: jika bukan admin/pimpinan, hanya bisa import IKU miliknya
            if (!auth()->user()->isAdmin() && !auth()->user()->isPimpinan()) {
                if ($indikator->pic_id != auth()->user()->pegawai_id) {
                    continue;
                }
            }

            // Proses Realisasi untuk Triwulan yang dipilih
            if (isset($row['realisasi_tw']) && is_numeric($row['realisasi_tw'])) {
                $dataRealisasi = ['realisasi_kumulatif' => $row['realisasi_tw']];
                
                if (isset($row['realisasi_x']) && is_numeric($row['realisasi_x'])) {
                    $dataRealisasi['realisasi_x'] = $row['realisasi_x'];
                }
                
                if (isset($row['realisasi_y']) && is_numeric($row['realisasi_y'])) {
                    $dataRealisasi['realisasi_y'] = $row['realisasi_y'];
                }

                Realisasi::updateOrCreate(
                    ['indikator_id' => $indikator->id, 'triwulan' => $this->triwulan],
                    $dataRealisasi
                );
            }

            // Proses Capaian (Kualitatif) untuk Triwulan yang dipilih saat upload
            // Tabel: capaian_kinerjas
            CapaianKinerja::updateOrCreate(
                [
                    'indikator_id' => $indikator->id,
                    'tahun' => $this->tahun,
                    'triwulan' => $this->triwulan,
                ],
                [
                    'link_bukti_kinerja' => $row['link_bukti_dukung_kinerja'] ?? null,
                    'link_bukti_tindak_lanjut' => $row['link_bukti_dukung_rencana_tindak_lanjut_triwulan_sebelumnya'] ?? null,
                ]
            );
            



            // Parse text to arrays
            $kendalaRaw = $row['kendala_yg_dihadapi'] ?? '';
            $solusiRaw = $row['solusi_yg_telah_dilakukan'] ?? '';
            $rtlRaw = $row['rencana_tindak_lanjut'] ?? '';
            $picRaw = $row['pic_tindak_lanjut'] ?? '';
            
            // Batas waktu 1 nilai untuk semua RTL di baris ini
            $parsedBatasWaktu = $batasWaktu; // fallback global
            if (!empty($row['batas_waktu_tindak_lanjut'])) {
                $rawBatas = $row['batas_waktu_tindak_lanjut'];
                if (is_numeric($rawBatas)) {
                    try {
                        $parsedBatasWaktu = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rawBatas)->format('Y-m-d');
                    } catch (\Exception $e) {}
                } else {
                    try {
                        // Jika berupa teks string biasa
                        $parsedBatasWaktu = \Carbon\Carbon::parse($rawBatas)->format('Y-m-d');
                    } catch (\Exception $e) {}
                }
            }

            if (!empty(trim($kendalaRaw)) || !empty(trim($solusiRaw)) || !empty(trim($rtlRaw))) {
                \App\Models\KendalaRtl::updateOrCreate(
                    [
                        'indikator_id' => $indikator->id,
                        'triwulan' => $this->triwulan,
                        'tahun' => $this->tahun,
                    ],
                    [
                        'kendala' => !empty(trim($kendalaRaw)) ? trim($kendalaRaw) : null,
                        'solusi' => !empty(trim($solusiRaw)) ? trim($solusiRaw) : null,
                        'rtl' => !empty(trim($rtlRaw)) ? trim($rtlRaw) : null,
                        'batas_waktu' => $parsedBatasWaktu,
                        'pic_nip' => !empty(trim($picRaw)) ? trim($picRaw) : null,
                        'status' => 'Belum Ditindak Lanjut',
                    ]
                );
            }

        }
    }

    private function parseBulletPoints($text)
    {
        if (empty($text)) return [];
        
        $lines = explode("\n", $text);
        $points = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Hapus karakter bullet atau penomoran di awal string
            $line = preg_replace('/^[-•*\s]+/', '', $line);
            $line = preg_replace('/^\d+[\.)]\s*/', '', $line);
            
            if (!empty($line)) {
                $points[] = $line;
            }
        }
        
        // Kalau setelah dipecah baris tidak ada hasilnya
        if (count($points) === 0) {
            $points[] = trim($text);
        }
        
        return $points;
    }
}
