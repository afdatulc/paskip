<?php

namespace App\Imports;

use App\Models\OutputMaster;
use App\Models\Indikator;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Str;

class OutputMasterImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        if (!isset($row['kode_indikator']) || !isset($row['nama_output'])) {
            return null;
        }

        // Find Indikator by kode
        $indikator = Indikator::where('kode', $row['kode_indikator'])->first();
        
        if (!$indikator) {
            return null; // Skip if indicator not found
        }

        // Normalize Jenis Output
        $jenis_output = $row['jenis_output'] ?? 'Laporan';
        if (!in_array($jenis_output, ['Laporan', 'Publikasi'])) {
            $jenis_output = 'Laporan';
        }

        // Normalize Periode
        $periode = $row['periode'] ?? 'Triwulanan';
        if (!in_array($periode, ['Tahunan', 'Triwulanan', 'Bulanan'])) {
            $periode = 'Triwulanan';
        }

        // Format nama_output to Title Case based on KBBI equivalent (Str::title)
        $nama_output = Str::title(trim($row['nama_output']));

        return new OutputMaster([
            'indikator_id' => $indikator->id,
            'nama_output'  => $nama_output,
            'jenis_output' => $jenis_output,
            'periode'      => $periode,
        ]);
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
