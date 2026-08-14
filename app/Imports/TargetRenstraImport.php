<?php

namespace App\Imports;

use App\Models\Indikator;
use App\Models\Renstra;
use App\Models\TargetRenstra;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class TargetRenstraImport implements ToCollection, WithHeadingRow, WithCalculatedFormulas
{
    protected Renstra $renstra;

    public function __construct(Renstra $renstra)
    {
        $this->renstra = $renstra;
    }

    public function collection(Collection $rows)
    {
        $tahunList = $this->renstra->daftar_tahun;

        foreach ($rows as $row) {
            $kodeIku = trim($row['kode_iku'] ?? $row['kode'] ?? '');
            
            $indikator = null;
            if (!empty($kodeIku)) {
                $indikator = Indikator::where('kode', $kodeIku)->first();
            }

            if (!$indikator) {
                $namaIku = trim($row['indikator_kinerja'] ?? '');
                if (!empty($namaIku)) {
                    $indikator = Indikator::where('indikator_kinerja', $namaIku)->first();
                }
            }

            if (!$indikator) {
                continue;
            }

            foreach ($tahunList as $th) {
                $key1 = 'target_' . $th;
                $key2 = (string)$th;

                $targetVal = null;
                if (isset($row[$key1]) && $row[$key1] !== '') {
                    $targetVal = $row[$key1];
                } elseif (isset($row[$key2]) && $row[$key2] !== '') {
                    $targetVal = $row[$key2];
                }

                if (!is_null($targetVal) && is_numeric($targetVal)) {
                    TargetRenstra::updateOrCreate(
                        [
                            'renstra_id' => $this->renstra->id,
                            'indikator_id' => $indikator->id,
                            'tahun' => $th,
                        ],
                        [
                            'target' => (float)$targetVal,
                        ]
                    );
                }
            }
        }
    }
}
