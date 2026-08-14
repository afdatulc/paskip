<?php

namespace App\Exports;

use App\Models\Indikator;
use App\Models\Renstra;
use App\Models\TargetRenstra;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TargetRenstraTemplateExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    protected Renstra $renstra;

    public function __construct(Renstra $renstra)
    {
        $this->renstra = $renstra;
    }

    public function collection()
    {
        $tahunList = $this->renstra->daftar_tahun;
        $indikators = Indikator::orderBy('kode')->get();

        $existingTargets = TargetRenstra::where('renstra_id', $this->renstra->id)
            ->get()
            ->groupBy('indikator_id');

        $rows = [];
        foreach ($indikators as $ind) {
            $targetsForInd = $existingTargets->get($ind->id);
            
            $row = [
                'kode_iku' => $ind->kode ?? '',
                'sasaran' => $ind->sasaran ?? '',
                'indikator_kinerja' => $ind->indikator_kinerja,
                'satuan' => $ind->satuan,
            ];

            foreach ($tahunList as $th) {
                $targetRecord = $targetsForInd ? $targetsForInd->firstWhere('tahun', $th) : null;
                $row['target_' . $th] = $targetRecord ? $targetRecord->target : '';
            }

            $rows[] = $row;
        }

        return collect($rows);
    }

    public function headings(): array
    {
        $tahunList = $this->renstra->daftar_tahun;
        
        $headings = [
            'KODE IKU',
            'SASARAN',
            'INDIKATOR KINERJA',
            'SATUAN',
        ];

        foreach ($tahunList as $th) {
            $headings[] = 'TARGET ' . $th;
        }

        return $headings;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
