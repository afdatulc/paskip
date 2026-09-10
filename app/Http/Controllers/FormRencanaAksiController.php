<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Indikator;
use App\Models\CapaianKinerja;
use App\Models\Realisasi;
use App\Models\KendalaRtl;
use App\Models\Pegawai;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use Illuminate\Support\Str;

class FormRencanaAksiController extends Controller
{
    public function index(Request $request)
    {
        $tahun = $request->get('tahun', session('global_tahun', date('Y')));
        $triwulan = $request->get('triwulan', session('global_triwulan', min(ceil(date('n') / 3), 4)));

        $indicators = Indikator::query()
            ->with(['target', 'realisasis', 'capaianKinerjas', 'kendalaRtls', 'pic'])
            ->where('tahun', $tahun)
            ->get();

        // Grouping logic: Tujuan -> Sasaran -> Indikator
        $grouped = $indicators->groupBy('tujuan')->map(function ($itemsByTujuan) {
            return $itemsByTujuan->groupBy('sasaran');
        });

        return view('fra.index', compact('tahun', 'triwulan', 'grouped'));
    }

    public function downloadTemplate()
    {
        $path = storage_path('app/templates/template_fra.xlsx');
        if (file_exists($path)) {
            return response()->download($path, 'Template_FRA.xlsx');
        }
        return back()->with('error', 'File template tidak ditemukan.');
    }

    public function preview(Request $request)
    {
        if (auth()->user()->isPimpinan() || auth()->user()->isAnggota()) abort(403, 'Akses ditolak.');
        
        $request->validate([
            'tahun' => 'required|integer',
            'triwulan' => 'required|integer|between:1,4',
            'file_excel' => 'required|mimes:xlsx,xls'
        ]);

        $tahun = $request->tahun;
        $triwulan = $request->triwulan;
        $file = $request->file('file_excel');

        try {
            $reader = IOFactory::createReaderForFile($file->getRealPath());
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file->getRealPath());
            $worksheet = $spreadsheet->getActiveSheet();
            $highestRow = $worksheet->getHighestDataRow();
            $highestColumn = $worksheet->getHighestDataColumn();
            
            // Limit to row 200 just in case to prevent infinite loops from messy excel formatting
            if ($highestRow > 500) {
                $highestRow = 500;
            }
            
            // Read only up to highest data row
            $rows = $worksheet->rangeToArray('A1:' . $highestColumn . $highestRow, null, true, true, true);
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membaca file Excel. Pastikan format file sudah benar.');
        }

        $previewData = [];
        $colRealisasi = ['1' => 'Q', '2' => 'R', '3' => 'S', '4' => 'T'][$triwulan];
        $user = auth()->user();

        // Loop data from row 10 (actual data starts here)
        for ($i = 10; $i <= count($rows); $i++) {
            $row = $rows[$i];
            $kode = trim($row['D'] ?? '');
            if (empty($kode)) continue;

            $indikator = Indikator::where('kode', $kode)->where('tahun', $tahun)->first();
            if (!$indikator) continue;

            // Optional auth check, allow admins or PICs
            if (!$user->isAdminOrPimpinan()) {
                if ($indikator->pic_id != $user->pegawai_id) {
                    continue; // Skip indicators that don't belong to the user
                }
            }

            // Extract excel data
            $rawRealisasi = isset($row[$colRealisasi]) ? trim($row[$colRealisasi]) : '';
            $excelRealisasi = ($rawRealisasi !== '' && $rawRealisasi !== '-') ? floatval($rawRealisasi) : null;
            $excelKendala = trim($row['AC'] ?? '');
            $excelSolusi = trim($row['AD'] ?? '');
            $excelRtl = trim($row['AE'] ?? '');
            $excelPicName = trim($row['AF'] ?? '');
            $excelBatasWaktuStr = trim($row['AG'] ?? '');
            $excelLinkKinerja = trim($row['AH'] ?? '');
            $excelLinkRtlSblm = trim($row['AI'] ?? '');

            if (!\Illuminate\Support\Str::startsWith($excelLinkKinerja, 'http')) $excelLinkKinerja = null;
            if (!\Illuminate\Support\Str::startsWith($excelLinkRtlSblm, 'http')) $excelLinkRtlSblm = null;

            // Try to find PIC Pegawai by name
            $picId = null;
            $picNameStr = $excelPicName;
            if ($excelPicName) {
                $pegawai = Pegawai::where('nama', 'LIKE', '%' . $excelPicName . '%')->first();
                if ($pegawai) {
                    $picId = $pegawai->id;
                    $picNameStr = $pegawai->nama; // Use normalized name
                }
            }

            // Parse Date
            $batasWaktu = null;
            if ($excelBatasWaktuStr) {
                if (is_numeric($excelBatasWaktuStr)) {
                    try {
                        $batasWaktu = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($excelBatasWaktuStr)->format('Y-m-d');
                    } catch (\Throwable $e) {}
                } else {
                    try {
                        $batasWaktu = Carbon::parse($excelBatasWaktuStr)->format('Y-m-d');
                    } catch (\Throwable $e) {
                        // ignore if invalid date
                    }
                }
            }

            // Get existing DB data
            $dbRealisasi = Realisasi::where('indikator_id', $indikator->id)->where('triwulan', $triwulan)->first();
            $dbCapaian = CapaianKinerja::where('indikator_id', $indikator->id)->where('tahun', $tahun)->where('triwulan', $triwulan)->first();
            $dbKendala = KendalaRtl::where('indikator_id', $indikator->id)->where('tahun', $tahun)->where('triwulan', $triwulan)->first();

            // Compare Realisasi & Links
            $diffCapaian = false;
            $oldRealisasiVal = $dbRealisasi && $dbRealisasi->realisasi_kumulatif !== null ? floatval($dbRealisasi->realisasi_kumulatif) : null;
            $oldLinkKinerja = $dbCapaian ? trim($dbCapaian->link_bukti_kinerja) : null;
            $oldLinkRtlSblm = $dbCapaian ? trim($dbCapaian->link_bukti_tindak_lanjut) : null;

            if ($excelRealisasi !== null && $excelRealisasi !== $oldRealisasiVal) $diffCapaian = true;
            if ($excelLinkKinerja && $excelLinkKinerja !== $oldLinkKinerja) $diffCapaian = true;
            if ($excelLinkRtlSblm && $excelLinkRtlSblm !== $oldLinkRtlSblm) $diffCapaian = true;

            // Compare Kendala/RTL
            $diffKendala = false;
            // Compile Kendalas for preview comparison to match what would be exported
            $dbKendalas = KendalaRtl::where('indikator_id', $indikator->id)->where('tahun', $tahun)->where('triwulan', $triwulan)->orderBy('id')->get();
            $dbKendala = $dbKendalas->first(); // For PIC and Batas Waktu comparison
            
            $formatBullet = function($str) {
                if ($str === null || trim($str) === '') return null;
                $str = trim($str);
                return str_starts_with($str, '-') ? $str : '- ' . $str;
            };
            
            $compiledKendalaStr = $dbKendalas->pluck('kendala')->filter()->map($formatBullet)->join("\n");
            $compiledSolusiStr = $dbKendalas->pluck('solusi')->filter()->map($formatBullet)->join("\n");
            $compiledRtlStr = $dbKendalas->pluck('rtl')->filter()->map($formatBullet)->join("\n");

            // Normalize line endings for accurate comparison
            $normalize = function($str) use ($formatBullet) {
                if ($str === null || $str === '') return null;
                $str = str_replace(["\r\n", "\r"], "\n", $str);
                $lines = array_map(function($line) use ($formatBullet) {
                    $line = trim($line);
                    return $line !== '' ? $formatBullet($line) : null;
                }, explode("\n", $str));
                return trim(implode("\n", array_filter($lines)));
            };

            $excelKendala = $normalize($excelKendala);
            $excelSolusi = $normalize($excelSolusi);
            $excelRtl = $normalize($excelRtl);
            $oldKendala = $normalize($compiledKendalaStr);
            $oldSolusi = $normalize($compiledSolusiStr);
            $oldRtl = $normalize($compiledRtlStr);
            $kendalaPicId = $dbKendala && $dbKendala->pic ? $dbKendala->pic->id : null;
            $oldPicId = $kendalaPicId ? $kendalaPicId : ($indikator->pic_id ?? null);
            $oldBatasWaktu = $dbKendala && $dbKendala->batas_waktu ? $dbKendala->batas_waktu->format('Y-m-d') : null;

            if ($excelKendala && $excelKendala !== $oldKendala) $diffKendala = true;
            if ($excelSolusi && $excelSolusi !== $oldSolusi) $diffKendala = true;
            if ($excelRtl && $excelRtl !== $oldRtl) $diffKendala = true;
            if ($picId !== null && $picId !== $oldPicId) $diffKendala = true;
            if ($batasWaktu && $batasWaktu !== $oldBatasWaktu) $diffKendala = true;

            $debugReasons = [];
            if ($excelRealisasi !== null && $excelRealisasi !== $oldRealisasiVal) $debugReasons[] = "Realisasi berbeda: '{$excelRealisasi}' vs '{$oldRealisasiVal}'";
            if ($excelLinkKinerja && $excelLinkKinerja !== $oldLinkKinerja) $debugReasons[] = "Link Kinerja berbeda: '{$excelLinkKinerja}' vs '{$oldLinkKinerja}'";
            if ($excelLinkRtlSblm && $excelLinkRtlSblm !== $oldLinkRtlSblm) $debugReasons[] = "Link RTL berbeda: '{$excelLinkRtlSblm}' vs '{$oldLinkRtlSblm}'";
            
            if ($excelKendala && $excelKendala !== $oldKendala) $debugReasons[] = "Kendala berbeda: '{$excelKendala}' vs '{$oldKendala}'";
            if ($excelSolusi && $excelSolusi !== $oldSolusi) $debugReasons[] = "Solusi berbeda: '{$excelSolusi}' vs '{$oldSolusi}'";
            if ($excelRtl && $excelRtl !== $oldRtl) $debugReasons[] = "RTL berbeda: '{$excelRtl}' vs '{$oldRtl}'";
            if ($picId !== null && $picId !== $oldPicId) $debugReasons[] = "PIC berbeda: '{$picId}' vs '{$oldPicId}'";
            if ($batasWaktu && $batasWaktu !== $oldBatasWaktu) $debugReasons[] = "Batas Waktu berbeda: '{$batasWaktu}' vs '{$oldBatasWaktu}'";

            // Only add to preview if there is Excel data to import
            if (($excelRealisasi !== null || $excelKendala !== '' || $excelRtl !== '') && ($diffCapaian || $diffKendala)) {
                $previewData[] = [
                    'indikator_id' => $indikator->id,
                    'kode' => $indikator->kode,
                    'indikator_kinerja' => $indikator->indikator_kinerja,
                    'excel' => [
                        'realisasi' => $excelRealisasi,
                        'link_kinerja' => $excelLinkKinerja,
                        'link_rtl_sblm' => $excelLinkRtlSblm,
                        'kendala' => $excelKendala,
                        'solusi' => $excelSolusi,
                        'rtl' => $excelRtl,
                        'pic_id' => $picId,
                        'pic_name' => $picNameStr,
                        'batas_waktu' => $batasWaktu,
                    ],
                    'db' => [
                        'realisasi' => $oldRealisasiVal,
                        'link_kinerja' => $oldLinkKinerja,
                        'link_rtl_sblm' => $oldLinkRtlSblm,
                        'kendala' => $oldKendala,
                        'solusi' => $oldSolusi,
                        'rtl' => $oldRtl,
                        'pic_id' => $oldPicId,
                        'pic_name' => $dbKendala && $dbKendala->pic ? $dbKendala->pic->nama : null,
                        'batas_waktu' => $oldBatasWaktu,
                    ],
                    'has_diff_capaian' => $diffCapaian,
                    'has_diff_kendala' => $diffKendala,
                    'debug_reasons' => $debugReasons,
                ];
            }
        }

        if (empty($previewData)) {
            return back()->with('info', 'Tidak ada data baru atau perubahan yang ditemukan dalam file Excel.');
        }

        $previewJson = base64_encode(json_encode($previewData));
        return view('fra.preview', compact('previewData', 'tahun', 'triwulan', 'previewJson', 'rows', 'colRealisasi'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->isPimpinan() || auth()->user()->isAnggota()) abort(403, 'Akses ditolak.');
        
        $request->validate([
            'tahun' => 'required|integer',
            'triwulan' => 'required|integer|between:1,4',
            'preview_data' => 'required|string',
            'selected_items' => 'required|array'
        ]);

        $tahun = $request->tahun;
        $triwulan = $request->triwulan;
        $previewData = json_decode(base64_decode($request->preview_data), true);
        $selectedItems = $request->selected_items;

        $countCapaian = 0;
        $countKendala = 0;

        foreach ($previewData as $index => $data) {
            // Only process if user checked it
            if (!in_array($index, $selectedItems)) {
                continue;
            }

            $indikatorId = $data['indikator_id'];
            $excel = $data['excel'];

            // Update Capaian / Realisasi
            if ($excel['realisasi'] !== null || $excel['link_kinerja'] || $excel['link_rtl_sblm']) {
                if ($excel['realisasi'] !== null) {
                    Realisasi::updateOrCreate(
                        ['indikator_id' => $indikatorId, 'triwulan' => $triwulan],
                        ['realisasi_kumulatif' => $excel['realisasi']]
                    );
                }

                $capaian = CapaianKinerja::firstOrNew([
                    'indikator_id' => $indikatorId,
                    'tahun' => $tahun,
                    'triwulan' => $triwulan
                ]);

                if ($excel['link_kinerja']) $capaian->link_bukti_kinerja = $excel['link_kinerja'];
                if ($excel['link_rtl_sblm']) $capaian->link_bukti_tindak_lanjut = $excel['link_rtl_sblm'];
                
                // If it was rejected, we should probably reset status, but keeping it simple
                if ($capaian->status_approval === 'Ditolak' || !$capaian->exists) {
                    $capaian->status_approval = 'Menunggu';
                }
                $capaian->save();
                $countCapaian++;
            }

            // Update Kendala RTL
            if ($excel['kendala'] || $excel['rtl']) {
                $existingKendalas = KendalaRtl::where('indikator_id', $indikatorId)
                    ->where('tahun', $tahun)
                    ->where('triwulan', $triwulan)
                    ->orderBy('id')
                    ->get();
                    
                $kendala = $existingKendalas->first();
                if (!$kendala) {
                    $kendala = new KendalaRtl([
                        'indikator_id' => $indikatorId,
                        'tahun' => $tahun,
                        'triwulan' => $triwulan
                    ]);
                }

                if ($excel['kendala']) $kendala->kendala = $excel['kendala'];
                if ($excel['solusi']) $kendala->solusi = $excel['solusi'];
                if ($excel['rtl']) $kendala->rtl = $excel['rtl'];
                
                if ($excel['pic_id']) {
                    $pegawai = \App\Models\Pegawai::find($excel['pic_id']);
                    if ($pegawai) {
                        $kendala->pic_nip = $pegawai->nip;
                    }
                }
                
                if ($excel['batas_waktu']) $kendala->batas_waktu = $excel['batas_waktu'];
                
                if (!$kendala->exists) {
                    $kendala->status = 'Belum Ditindak Lanjut';
                }
                
                $kendala->save();
                
                // Remove duplicates if they exist, since we compiled them into the first one
                if ($existingKendalas->count() > 1) {
                    $existingKendalas->slice(1)->each->delete();
                }
                
                $countKendala++;
            }
        }

        return redirect()->route('fra.index')->with('success', "Berhasil menyimpan {$countCapaian} Capaian Kinerja dan {$countKendala} Kendala/RTL.");
    }

    public function export(Request $request)
    {
        $tahun = $request->get('tahun', session('global_tahun', date('Y')));
        $triwulan = $request->get('triwulan', session('global_triwulan', min(ceil(date('n') / 3), 4)));

        $indicators = Indikator::query()
            ->with(['target', 'realisasis', 'capaianKinerjas', 'kendalaRtls', 'pic'])
            ->where('tahun', $tahun)
            ->get();

        $grouped = $indicators->groupBy('tujuan')->map(function ($itemsByTujuan) {
            return $itemsByTujuan->groupBy('sasaran');
        });

        $path = storage_path('app/templates/template_fra.xlsx');
        if (!file_exists($path)) {
            return back()->with('error', 'File template tidak ditemukan.');
        }

        $spreadsheet = IOFactory::load($path);
        $worksheet = $spreadsheet->getActiveSheet();

        $rowIdx = 10; // Start row
        
        // Clear dummy data from row 10 down to 500 to avoid overlapping with old template data
        for ($r = 10; $r <= 500; $r++) {
            for ($c = 'A'; $c !== 'AJ'; $c++) {
                $worksheet->setCellValue($c . $r, null);
                // Remove background color so it doesn't bleed through
                $worksheet->getStyle($c . $r)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_NONE);
            }
        }
        
        foreach ($grouped as $tujuan => $sasarans) {
            $firstInd = $sasarans->first()->first();
            $kodeTujuanStr = $firstInd && $firstInd->kode_tujuan ? $firstInd->kode_tujuan . ': ' : '';
            $worksheet->setCellValue('A' . $rowIdx, $kodeTujuanStr . $tujuan);
            
            // Format tujuan row
            $worksheet->getStyle("A{$rowIdx}:AJ{$rowIdx}")->getFont()->setBold(true);
            $worksheet->getStyle("A{$rowIdx}:AJ{$rowIdx}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE9ECEF');
            $rowIdx++;

            foreach ($sasarans as $sasaran => $inds) {
                $worksheet->setCellValue('B' . $rowIdx, $inds->first()->kode_sasaran ?? '');
                $worksheet->setCellValue('C' . $rowIdx, $sasaran);
                
                // Format sasaran row
                $worksheet->getStyle("A{$rowIdx}:AJ{$rowIdx}")->getFont()->setBold(true);
                $rowIdx++;

                foreach ($inds as $ind) {
                    $target = $ind->target;
                    
                    // Realisasi
                    $r1 = $ind->realisasis->where('triwulan', 1)->first()->realisasi_kumulatif ?? null;
                    $r2 = $ind->realisasis->where('triwulan', 2)->first()->realisasi_kumulatif ?? null;
                    $r3 = $ind->realisasis->where('triwulan', 3)->first()->realisasi_kumulatif ?? null;
                    $r4 = $ind->realisasis->where('triwulan', 4)->first()->realisasi_kumulatif ?? null;

                    // Capaian Kinerja
                    $c1 = $ind->capaianKinerjas->where('triwulan', 1)->first();
                    $c2 = $ind->capaianKinerjas->where('triwulan', 2)->first();
                    $c3 = $ind->capaianKinerjas->where('triwulan', 3)->first();
                    $c4 = $ind->capaianKinerjas->where('triwulan', 4)->first();

                    // Kendala current TW (Get all and compile)
                    $kendalas = $ind->kendalaRtls->where('triwulan', $triwulan);
                    $firstKendala = $kendalas->first();

                    $worksheet->setCellValue('D' . $rowIdx, $ind->kode);
                    $worksheet->setCellValue('E' . $rowIdx, $ind->indikator_kinerja);
                    
                    // Metadata
                    $worksheet->setCellValue('H' . $rowIdx, $ind->jenis_indikator);
                    $worksheet->setCellValue('I' . $rowIdx, $ind->periode);
                    $worksheet->setCellValue('J' . $rowIdx, $ind->tipe);
                    $worksheet->setCellValue('K' . $rowIdx, $ind->target_tahunan);
                    $worksheet->setCellValue('L' . $rowIdx, $ind->satuan);
                    
                    // Target (M-P)
                    if ($target) {
                        $worksheet->setCellValue('M' . $rowIdx, $target->target_tw1 ?? '-');
                        $worksheet->setCellValue('N' . $rowIdx, $target->target_tw2 ?? '-');
                        $worksheet->setCellValue('O' . $rowIdx, $target->target_tw3 ?? '-');
                        $worksheet->setCellValue('P' . $rowIdx, $target->target_tw4 ?? '-');
                    } else {
                        $worksheet->setCellValue('M' . $rowIdx, '-');
                        $worksheet->setCellValue('N' . $rowIdx, '-');
                        $worksheet->setCellValue('O' . $rowIdx, '-');
                        $worksheet->setCellValue('P' . $rowIdx, '-');
                    }
                    
                    // Realisasi (Q-T)
                    $worksheet->setCellValue('Q' . $rowIdx, $r1 ?? '-');
                    $worksheet->setCellValue('R' . $rowIdx, $r2 ?? '-');
                    $worksheet->setCellValue('S' . $rowIdx, $r3 ?? '-');
                    $worksheet->setCellValue('T' . $rowIdx, $r4 ?? '-');
                    
                    // Hitung Capaian Triwulanan (U-X)
                    $polarisasi = $ind->polarisasi ?? 'positif';
                    
                    $capaian_tw1 = ($r1 !== null && $target && $target->target_tw1 !== null) ? \App\Services\CapaianCalculator::hitung($target->target_tw1, $r1, $polarisasi) : null;
                    $capaian_tw2 = ($r2 !== null && $target && $target->target_tw2 !== null) ? \App\Services\CapaianCalculator::hitung($target->target_tw2, $r2, $polarisasi) : null;
                    $capaian_tw3 = ($r3 !== null && $target && $target->target_tw3 !== null) ? \App\Services\CapaianCalculator::hitung($target->target_tw3, $r3, $polarisasi) : null;
                    $capaian_tw4 = ($r4 !== null && $target && $target->target_tw4 !== null) ? \App\Services\CapaianCalculator::hitung($target->target_tw4, $r4, $polarisasi) : null;

                    $worksheet->setCellValue('U' . $rowIdx, $capaian_tw1 ?? '-');
                    $worksheet->setCellValue('V' . $rowIdx, $capaian_tw2 ?? '-');
                    $worksheet->setCellValue('W' . $rowIdx, $capaian_tw3 ?? '-');
                    $worksheet->setCellValue('X' . $rowIdx, $capaian_tw4 ?? '-');
                    
                    // Hitung Capaian Tahunan (Y-AB)
                    $capaian_tahunan_tw1 = ($r1 !== null && $ind->target_tahunan !== null) ? \App\Services\CapaianCalculator::hitung($ind->target_tahunan, $r1, $polarisasi) : null;
                    $capaian_tahunan_tw2 = ($r2 !== null && $ind->target_tahunan !== null) ? \App\Services\CapaianCalculator::hitung($ind->target_tahunan, $r2, $polarisasi) : null;
                    $capaian_tahunan_tw3 = ($r3 !== null && $ind->target_tahunan !== null) ? \App\Services\CapaianCalculator::hitung($ind->target_tahunan, $r3, $polarisasi) : null;
                    $capaian_tahunan_tw4 = ($r4 !== null && $ind->target_tahunan !== null) ? \App\Services\CapaianCalculator::hitung($ind->target_tahunan, $r4, $polarisasi) : null;
                    
                    $worksheet->setCellValue('Y' . $rowIdx, $capaian_tahunan_tw1 ?? '-');
                    $worksheet->setCellValue('Z' . $rowIdx, $capaian_tahunan_tw2 ?? '-');
                    $worksheet->setCellValue('AA' . $rowIdx, $capaian_tahunan_tw3 ?? '-');
                    $worksheet->setCellValue('AB' . $rowIdx, $capaian_tahunan_tw4 ?? '-');
                    
                    // Kendala & RTL for the selected triwulan (AC-AI)
                    if ($kendalas->count() > 0) {
                        $formatBullet = function($str) {
                            if ($str === null || trim($str) === '') return null;
                            $str = trim($str);
                            return str_starts_with($str, '-') ? $str : '- ' . $str;
                        };
                        
                        $worksheet->setCellValue('AC' . $rowIdx, $kendalas->pluck('kendala')->filter()->map($formatBullet)->join("\n"));
                        $worksheet->setCellValue('AD' . $rowIdx, $kendalas->pluck('solusi')->filter()->map($formatBullet)->join("\n"));
                        $worksheet->setCellValue('AE' . $rowIdx, $kendalas->pluck('rtl')->filter()->map($formatBullet)->join("\n"));
                        
                        $picName = ($firstKendala && $firstKendala->pic) ? $firstKendala->pic->nama : ($ind->pic ? $ind->pic->nama : '');
                        $worksheet->setCellValue('AF' . $rowIdx, $picName);
                        
                        if ($firstKendala->batas_waktu) {
                            $worksheet->setCellValue('AG' . $rowIdx, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($firstKendala->batas_waktu));
                            $worksheet->getStyle('AG' . $rowIdx)->getNumberFormat()->setFormatCode('dd mmmm yyyy');
                        }
                    } else if ($ind->pic) {
                        $worksheet->setCellValue('AF' . $rowIdx, $ind->pic->nama);
                    }
                    
                    $currCapaian = $ind->capaianKinerjas->where('triwulan', $triwulan)->first();
                    if ($currCapaian) {
                        $worksheet->setCellValue('AH' . $rowIdx, $currCapaian->link_bukti_kinerja);
                        $worksheet->setCellValue('AI' . $rowIdx, $currCapaian->link_bukti_tindak_lanjut);
                    }

                    $rowIdx++;
                    
                    if ($ind->definisi_x) {
                        $worksheet->setCellValue('E' . $rowIdx, 'X: ' . $ind->definisi_x);
                        if ($target) {
                            $worksheet->setCellValue('M' . $rowIdx, $target->target_x_tw1);
                            $worksheet->setCellValue('N' . $rowIdx, $target->target_x_tw2);
                            $worksheet->setCellValue('O' . $rowIdx, $target->target_x_tw3);
                            $worksheet->setCellValue('P' . $rowIdx, $target->target_x_tw4);
                        }
                        $worksheet->setCellValue('Q' . $rowIdx, $ind->realisasis->where('triwulan', 1)->first()->realisasi_x ?? null);
                        $worksheet->setCellValue('R' . $rowIdx, $ind->realisasis->where('triwulan', 2)->first()->realisasi_x ?? null);
                        $worksheet->setCellValue('S' . $rowIdx, $ind->realisasis->where('triwulan', 3)->first()->realisasi_x ?? null);
                        $worksheet->setCellValue('T' . $rowIdx, $ind->realisasis->where('triwulan', 4)->first()->realisasi_x ?? null);
                        $rowIdx++;
                    }
                    if ($ind->definisi_y) {
                        $worksheet->setCellValue('E' . $rowIdx, 'Y: ' . $ind->definisi_y);
                        if ($target) {
                            $worksheet->setCellValue('M' . $rowIdx, $target->target_y_tw1);
                            $worksheet->setCellValue('N' . $rowIdx, $target->target_y_tw2);
                            $worksheet->setCellValue('O' . $rowIdx, $target->target_y_tw3);
                            $worksheet->setCellValue('P' . $rowIdx, $target->target_y_tw4);
                        }
                        $worksheet->setCellValue('Q' . $rowIdx, $ind->realisasis->where('triwulan', 1)->first()->realisasi_y ?? null);
                        $worksheet->setCellValue('R' . $rowIdx, $ind->realisasis->where('triwulan', 2)->first()->realisasi_y ?? null);
                        $worksheet->setCellValue('S' . $rowIdx, $ind->realisasis->where('triwulan', 3)->first()->realisasi_y ?? null);
                        $worksheet->setCellValue('T' . $rowIdx, $ind->realisasis->where('triwulan', 4)->first()->realisasi_y ?? null);
                        $rowIdx++;
                    }
                    
                    // Jika IKU memiliki X atau Y, beri jarak 1 baris sebelum IKU berikutnya
                    // Tapi jika ini adalah IKU terakhir dalam Sasaran, jangan tambah baris lagi karena di akhir Sasaran sudah ada.
                    if (($ind->definisi_x || $ind->definisi_y) && $ind->id !== $inds->last()->id) {
                        $rowIdx++;
                    }
                }
                
                // Jarak 1 baris setiap selesai satu Sasaran (mencakup semua IKU-nya)
                $rowIdx++;
            }
            
        }

        // Set freeze panes explicitly so only A-E (Tujuan/Sasaran/Indikator) are frozen horizontally, and rows 1-9 are frozen vertically.
        $worksheet->freezePane('F10');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        
        $tempFile = tempnam(sys_get_temp_dir(), 'fra_');
        $writer->save($tempFile);
        
        return response()->download($tempFile, "Form_Rencana_Aksi_{$tahun}_TW{$triwulan}.xlsx")->deleteFileAfterSend(true);
    }

    public function liveUpdate(Request $request)
    {
        if (auth()->user()->isPimpinan() || auth()->user()->isAnggota()) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }
        
        $indikatorId = $request->input('indikator_id');
        $type = $request->input('type'); // 'realisasi' or 'kendala_rtl'
        $field = $request->input('field'); // e.g., 'realisasi_kumulatif', 'realisasi_x', 'kendala', 'solusi'
        $triwulan = $request->input('triwulan');
        $value = $request->input('value');
        
        $tahun = session('tahun', date('Y'));
        $indikatorObj = \App\Models\Indikator::find($indikatorId);

        if (!$indikatorObj) {
            return response()->json(['success' => false, 'message' => 'Indikator tidak ditemukan.'], 404);
        }

        if (!auth()->user()->isAdminOrPimpinan() && auth()->user()->isPic()) {
            if ($indikatorObj->pic_id != auth()->user()->pegawai_id) {
                return response()->json(['success' => false, 'message' => 'Anda hanya dapat mengubah IKU yang menjadi tanggung jawab Anda (PIC).'], 403);
            }
        }

        // Handle numeric fields correctly
        if ($value === '-' || trim($value) === '') {
            if ($type === 'realisasi') {
                $value = 0;
            } else {
                $value = null;
            }
        }

        try {
            if ($type === 'realisasi') {
                $realisasiObj = \App\Models\Realisasi::updateOrCreate(
                    ['indikator_id' => $indikatorId, 'triwulan' => $triwulan],
                    [$field => $value]
                );

                if (in_array($field, ['realisasi_kumulatif', 'realisasi_x', 'realisasi_y'])) {
                    $ind = \App\Models\Indikator::with('target')->find($indikatorId);
                    $targetField = 'target_tw' . $triwulan;
                    $targetVal = $ind->target ? $ind->target->$targetField : null;
                    $polarisasi = $ind->polarisasi ?? 'positif';
                    
                    // Jika yang diedit adalah X atau Y, hitung ulang realisasi_kumulatif
                    if (in_array($field, ['realisasi_x', 'realisasi_y'])) {
                        $rx = $realisasiObj->realisasi_x;
                        $ry = $realisasiObj->realisasi_y;
                        if ($ry && $ry > 0) {
                            $realisasiObj->realisasi_kumulatif = round(($rx / $ry) * 100, 2);
                            $realisasiObj->save();
                        } else if ($ry == 0 && $rx == 0) {
                            // fallback for 0/0
                            $realisasiObj->realisasi_kumulatif = 0;
                            $realisasiObj->save();
                        }
                    }

                    $capaian_tw = \App\Services\CapaianCalculator::hitung($targetVal, $realisasiObj->realisasi_kumulatif, $polarisasi);
                    $capaian_thn = \App\Services\CapaianCalculator::hitung($ind->target_tahunan, $realisasiObj->realisasi_kumulatif, $polarisasi);
                    
                    \App\Models\CapaianKinerja::updateOrCreate(
                        ['indikator_id' => $indikatorId, 'triwulan' => $triwulan, 'tahun' => $tahun],
                        [
                            'capaian_kinerja' => $capaian_tw,
                            'capaian_tahunan' => $capaian_thn
                        ]
                    );

                    return response()->json([
                        'success' => true, 
                        'message' => 'Data berhasil disimpan',
                        'realisasi_kumulatif' => $realisasiObj->realisasi_kumulatif,
                        'capaian_tw' => is_null($capaian_tw) ? '-' : $capaian_tw,
                        'capaian_thn' => is_null($capaian_thn) ? '-' : $capaian_thn,
                    ]);
                }
            } elseif ($type === 'kendala_rtl') {
                $existingKendalas = \App\Models\KendalaRtl::where('indikator_id', $indikatorId)
                    ->where('tahun', $tahun)
                    ->where('triwulan', $triwulan)
                    ->orderBy('id')
                    ->get();
                    
                $kendala = $existingKendalas->first();
                if (!$kendala) {
                    $kendala = new \App\Models\KendalaRtl([
                        'indikator_id' => $indikatorId,
                        'tahun' => $tahun,
                        'triwulan' => $triwulan
                    ]);
                }
                
                $kendala->$field = $value;
                if (!$kendala->exists) {
                    $kendala->status = 'Belum Ditindak Lanjut';
                }
                $kendala->save();
                
                if ($existingKendalas->count() > 1) {
                    $existingKendalas->slice(1)->each->delete();
                }
            }

            return response()->json(['success' => true, 'message' => 'Data berhasil disimpan']);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
        }
    }
}

