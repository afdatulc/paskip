<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\Indikator;
use App\Models\KendalaRtl;
use App\Models\Pegawai;

class AnalisisKendalaBaruImport implements ToCollection, WithHeadingRow
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
        foreach ($rows as $row) {
            $kode = trim($row['kode_indikator'] ?? '');
            if (empty($kode)) continue;

            $indikator = Indikator::where('kode', $kode)->first();
            if (!$indikator) continue;

            $kendalaRaw = trim($row['kendala_yg_dihadapi'] ?? '');
            $solusiRaw = trim($row['solusi_yg_telah_dilakukan'] ?? '');
            $rtlRaw = trim($row['rencana_tindak_lanjut'] ?? '');
            $picRaw = trim($row['pic_tindak_lanjut'] ?? '');
            
            // Parse Date
            $parsedBatasWaktu = null;
            if (!empty($row['batas_waktu_tl'])) {
                $rawBatas = $row['batas_waktu_tl'];
                if (is_numeric($rawBatas)) {
                    try {
                        $parsedBatasWaktu = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rawBatas)->format('Y-m-d');
                    } catch (\Exception $e) {}
                } else {
                    try {
                        $parsedBatasWaktu = \Carbon\Carbon::parse($rawBatas)->format('Y-m-d');
                    } catch (\Exception $e) {}
                }
            }

            if (empty($kendalaRaw) && empty($solusiRaw) && empty($rtlRaw)) {
                continue; // Skip empty rows
            }

            // Find PIC NIP by name (since the template usually contains names)
            $picNip = null;
            if (!empty($picRaw)) {
                $pegawai = Pegawai::where('nama', 'LIKE', '%' . $picRaw . '%')->orWhere('nip', $picRaw)->first();
                if ($pegawai) {
                    $picNip = $pegawai->nip;
                }
            }

            KendalaRtl::create([
                'indikator_id' => $indikator->id,
                'tahun' => $this->tahun,
                'triwulan' => $this->triwulan,
                'kendala' => $kendalaRaw,
                'solusi' => $solusiRaw,
                'rtl' => $rtlRaw,
                'pic_nip' => $picNip,
                'batas_waktu' => $parsedBatasWaktu,
                'status' => 'Belum Ditindak Lanjut',
            ]);
        }
    }
}
