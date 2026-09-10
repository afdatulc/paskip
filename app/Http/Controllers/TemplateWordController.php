<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Indikator;
use App\Models\Pegawai;
use PhpOffice\PhpWord\TemplateProcessor;
use App\Services\RichTemplateProcessor;
use App\Models\SasaranAnggaran;

class TemplateWordController extends Controller
{
    public function index()
    {
        $pegawais = Pegawai::orderBy('pangkat_golongan', 'desc')->orderBy('nip', 'asc')->get();
        return view('template_word.index', compact('pegawais'));
    }

    public function exportNotulenCapaian(Request $request)
    {
        if ($request->input('format') === 'word') {
            return $this->exportNotulenCapaianWord($request);
        }
        return $this->exportNotulenCapaianHtml($request);
    }

public function exportNotulenCapaianWord(Request $request)
    {
        $validated = $request->validate([
            'tahun' => 'required|integer',
            'triwulan' => 'required|integer|between:1,4',
            'tanggal' => 'required|date',
            'waktu' => 'required|string',
            'tempat' => 'required|string',
            'pimpinan_id' => 'required|exists:pegawais,id',
            'notulis_id' => 'required|exists:pegawais,id'
        ]);

        $pimpinan = Pegawai::find($validated['pimpinan_id']);
        $notulis = Pegawai::find($validated['notulis_id']);

        // Format tanggal (dddd, dd mmmm yyyy)
        \Carbon\Carbon::setLocale('id');
        $date = \Carbon\Carbon::parse($validated['tanggal']);
        $formattedDate = $date->translatedFormat('l, d F Y');
        $tanggalNotula = $date->translatedFormat('d F Y');

        $templatePath = storage_path('app/templates/notulen_capkin.docx');
        if (!file_exists($templatePath)) {
            return redirect()->back()->with('error', 'File template Notulen Capaian (notulen_capkin.docx) tidak ditemukan di folder storage/app/templates.');
        }

        $templateProcessor = new RichTemplateProcessor($templatePath);

        // Replace tag umum
        $triwulans = ['', 'TRIWULAN I', 'TRIWULAN II', 'TRIWULAN III', 'TRIWULAN IV'];
        $triwulans1 = ['', 'Triwulan I', 'Triwulan II', 'Triwulan III', 'Triwulan IV'];
        $templateProcessor->setValue('triwulan_upper', $triwulans[$validated['triwulan']]);
        $templateProcessor->setValue('triwulan_proper', $triwulans1[$validated['triwulan']]);
        $templateProcessor->setValue('tahun', $validated['tahun']);
        $templateProcessor->setValue('triwulan', $validated['triwulan']);
        $templateProcessor->setValue('hari_tanggal', $formattedDate);
        $templateProcessor->setValue('tanggal_notula', $tanggalNotula);
        $templateProcessor->setValue('waktu', $validated['waktu']);
        $templateProcessor->setValue('tempat', $validated['tempat']);
        $templateProcessor->setValue('pimpinan_rapat', $pimpinan->nama);
        $templateProcessor->setValue('jabatan_pimpinan', $pimpinan->jabatan ?? '-');
        $templateProcessor->setValue('kepala', $pimpinan->nama);
        $templateProcessor->setValue('notulis', $notulis->nama);
        $indikators = Indikator::with([
            'target',
            'realisasis' => function ($q) use ($validated) {
                $q->where('triwulan', $validated['triwulan']);
            },
            'capaianKinerjas' => function ($q) use ($validated) {
                $q->where('tahun', $validated['tahun'])->where('triwulan', $validated['triwulan']);
            },
            'anggarans' => function ($q) use ($validated) {
                $q->where('tahun', $validated['tahun']);
            },
            'outputMasters'
        ])->get();

        $sasaranAnggarans = SasaranAnggaran::where('tahun', $validated['tahun'])->get();
        $globalPaguAwal = $sasaranAnggarans->sum('pagu_awal');
        $globalPaguRevisi = $sasaranAnggarans->sum('pagu_revisi');
        $globalPaguRealisasi = 0;

        $tw = $validated['triwulan'];
        foreach ($sasaranAnggarans as $sa) {
            for ($i = 1; $i <= $tw; $i++) {
                $field = "realisasi_tw{$i}";
                $globalPaguRealisasi += (float) $sa->$field;
            }
        }

        $templateProcessor->setValue('pagu_awal', number_format($globalPaguAwal, 0, ',', '.'));
        $templateProcessor->setValue('pagu_revisi', number_format($globalPaguRevisi, 0, ',', '.'));
        $templateProcessor->setValue('pagu_realisasi', number_format($globalPaguRealisasi, 0, ',', '.'));

        $templateProcessor->cloneBlock('block_indikator', count($indikators), true, true);

        $sumCapaianTriwulan = 0;
        $sumCapaianTahunan = 0;
        $countIndikator = count($indikators);
        $countIndikatorTriwulan = 0;

        foreach ($indikators as $index => $indikator) {
            $blockIdx = $index + 1;

            $realisasi = $indikator->realisasis->first();
            $capaianData = $indikator->capaianKinerjas->first();

            $targetField = 'target_tw' . $validated['triwulan'];
            $target = $indikator->target ? $indikator->target->$targetField : '-';

            $templateProcessor->setValue("no#{$blockIdx}", $blockIdx);
            $templateProcessor->setValue("kode_indikator#{$blockIdx}", $indikator->kode);
            $templateProcessor->setValue("kode#{$blockIdx}", $indikator->kode);
            $templateProcessor->setValue("nama_indikator#{$blockIdx}", $indikator->indikator_kinerja);
            $templateProcessor->setValue("indikator_kinerja#{$blockIdx}", $indikator->indikator_kinerja);
            $templateProcessor->setValue("indikator_kinerja_tanpa_satuan#{$blockIdx}", $indikator->indikator_kinerja);
            $templateProcessor->setValue("tujuan#{$blockIdx}", $indikator->tujuan ?? '-');
            $templateProcessor->setValue("sasaran#{$blockIdx}", $indikator->sasaran ?? '-');
            $templateProcessor->setValue("satuan#{$blockIdx}", $indikator->satuan ?? '-');
            $templateProcessor->setValue("target#{$blockIdx}", $target);
            $templateProcessor->setValue("target_tw#{$blockIdx}", $target);
            $templateProcessor->setValue("target_tahunan#{$blockIdx}", $indikator->target_tahunan ?? '-');

            // Format Output Masters
            if ($indikator->outputMasters && $indikator->outputMasters->count() > 0) {
                $htmlList = '<ol style="font-family: \'Aptos\'; font-size: 11pt;">';
                foreach ($indikator->outputMasters as $om) {
                    $htmlList .= '<li>' . htmlspecialchars($om->nama_output) . '</li>';
                }
                $htmlList .= '</ol>';
                try {
                    $templateProcessor->setHtmlValue("daftar_output_master#{$blockIdx}", $htmlList);
                } catch (\Throwable $e) {
                    $templateProcessor->setValue("daftar_output_master#{$blockIdx}", '-');
                }
            } else {
                $templateProcessor->setValue("daftar_output_master#{$blockIdx}", '-');
            }

            $templateProcessor->setValue("definisi_x#{$blockIdx}", $indikator->definisi_x ?? '-');
            $templateProcessor->setValue("definisi_y#{$blockIdx}", $indikator->definisi_y ?? '-');

            $real_x = $realisasi ? $realisasi->realisasi_x : '-';
            $real_y = $realisasi ? $realisasi->realisasi_y : '-';
            $capaian = $realisasi ? $realisasi->realisasi_kumulatif : 0;

            $targetTahunan = $indikator->target_tahunan ?? 0;
            $capaian_triwulan = (is_numeric($target) && $target > 0) ? round(($capaian / $target) * 100, 2) : 0;
            if ($capaian_triwulan > 120) $capaian_triwulan = 120;
            
            $capaian_tahunan = (is_numeric($targetTahunan) && $targetTahunan > 0) ? round(($capaian / $targetTahunan) * 100, 2) : 0;
            if ($capaian_tahunan > 120) $capaian_tahunan = 120;
            
            $sumCapaianTriwulan += $capaian_triwulan;
            $sumCapaianTahunan += $capaian_tahunan;
            
            if ($capaian_triwulan != 0) $countIndikatorTriwulan++;

            $templateProcessor->setValue("real_x#{$blockIdx}", $real_x);
            $templateProcessor->setValue("x#{$blockIdx}", $real_x);
            $templateProcessor->setValue("real_y#{$blockIdx}", $real_y);
            $templateProcessor->setValue("y#{$blockIdx}", $real_y);
            $templateProcessor->setValue("capaian#{$blockIdx}", $capaian);
            $templateProcessor->setValue("realisasi_kumulatif#{$blockIdx}", $capaian);
            $templateProcessor->setValue("capaian_triwulan#{$blockIdx}", $capaian_triwulan);
            $templateProcessor->setValue("capaian_tahunan#{$blockIdx}", $capaian_tahunan);

            $issues = \App\Models\KendalaRtl::where('indikator_id', $indikator->id)
                        ->where('tahun', $validated['tahun'])
                        ->where('triwulan', $validated['triwulan'])
                        ->with('pic')
                        ->orderBy('created_at', 'asc')
                        ->get();
            $kendalas = [];
            $solusis = [];
            $rtlsDesc = [];
            $rtlsPic = [];
            $rtlsBatas = [];

            $parseLines = function ($text) {
                if (!$text) return [];
                $lines = explode("\n", html_entity_decode(strip_tags($text)));
                $result = [];
                foreach ($lines as $line) {
                    $line = preg_replace('/^[-•*\s]+/', '', trim($line));
                    if ($line !== '') {
                        $result[] = $line;
                    }
                }
                return empty($result) ? ['-'] : $result;
            };

            foreach ($issues as $issue) {
                if ($issue->kendala) {
                    $kendalas = array_merge($kendalas, $parseLines($issue->kendala));
                }
                if ($issue->solusi) {
                    $solusis = array_merge($solusis, $parseLines($issue->solusi));
                }
                if ($issue->rtl) {
                    $rtlsDesc = array_merge($rtlsDesc, $parseLines($issue->rtl));
                }
                if ($issue->pic_nip) {
                    $picName = $issue->pic ? $issue->pic->nama : $issue->pic_nip;
                    $rtlsPic[] = $picName;
                }
                if ($issue->batas_waktu) {
                    $rtlsBatas[] = \Carbon\Carbon::parse($issue->batas_waktu)->locale('id')->translatedFormat('d F Y');
                }
            }



            $templateProcessor->setMultilineValue("kendala#{$blockIdx}", $kendalas);
            $templateProcessor->setMultilineValue("solusi#{$blockIdx}", $solusis);
            $templateProcessor->setMultilineValue("tindak_lanjut#{$blockIdx}", $rtlsDesc);
            $templateProcessor->setMultilineValue("rencana_tindak_lanjut#{$blockIdx}", $rtlsDesc);
            $templateProcessor->setMultilineValue("pic_tindak_lanjut#{$blockIdx}", $rtlsPic);
            $templateProcessor->setMultilineValue("batas_waktu#{$blockIdx}", $rtlsBatas);

            $basisData = $indikator->basis_data ? html_entity_decode(strip_tags($indikator->basis_data)) : '-';

            $dasarHitung = $capaianData && $capaianData->dasar_hitung ? $capaianData->dasar_hitung : ($indikator->dasar_hitung ? $indikator->dasar_hitung : '-');
            $argumenLogis = $capaianData && $capaianData->argumen_logis ? $capaianData->argumen_logis : '-';
            $penjelasanLainnya = $capaianData && $capaianData->penjelasan_lainnya ? $capaianData->penjelasan_lainnya : ($indikator->penjelasan_lainnya ?? '-');
            $targetRealisasi = $capaianData && $capaianData->target_realisasi ? $capaianData->target_realisasi : '-';

            $templateProcessor->setValue("basis_data#{$blockIdx}", $basisData);
            $templateProcessor->setValue("basis_data_baseline#{$blockIdx}", $basisData);

            try {
                $templateProcessor->setHtmlValue("dasar_hitung#{$blockIdx}", $dasarHitung);
            } catch (\Throwable $e) {
            }
            try {
                $templateProcessor->setHtmlValue("argumen_logis#{$blockIdx}", $argumenLogis);
            } catch (\Throwable $e) {
            }
            try {
                $templateProcessor->setHtmlValue("penjelasan_lainnya#{$blockIdx}", $penjelasanLainnya);
            } catch (\Throwable $e) {
            }
            try {
                $templateProcessor->setHtmlValue("target_realisasi#{$blockIdx}", $targetRealisasi);
            } catch (\Throwable $e) {
            }

            $templateProcessor->setValue("link_bukti_kinerja#{$blockIdx}", $capaianData->link_bukti_kinerja ?? ($indikator->link_bukti_kinerja ?? '-'));
            $templateProcessor->setValue("link_bukti_tindak_lanjut#{$blockIdx}", $capaianData->link_bukti_tindak_lanjut ?? ($indikator->link_bukti_tindak_lanjut ?? '-'));

            // Target X/Y
            $targetObj = $indikator->target;
            $totalTargetX = 0;
            $totalTargetY = 0;
            for ($i = 1; $i <= 4; $i++) {
                $txField = "target_x_tw{$i}";
                $tyField = "target_y_tw{$i}";
                $txVal = $targetObj ? $targetObj->$txField : 0;
                $tyVal = $targetObj ? $targetObj->$tyField : 0;

                $totalTargetX += (float) $txVal;
                $totalTargetY += (float) $tyVal;

                $templateProcessor->setValue("target_x_tw{$i}#{$blockIdx}", $txVal ?: '-');

                // Calculate percentage: (target_x_tw / target_tahunan_y) * 100
                $targetTahunanY = (float) $indikator->target_tahunan_y;
                if ($targetTahunanY > 0) {
                    $percentage = ((float) $txVal / $targetTahunanY) * 100;
                    // Format with 2 decimal places if not whole number
                    $percentageFormatted = (floor($percentage) == $percentage) ? number_format($percentage, 0) : number_format($percentage, 2);
                    $templateProcessor->setValue("target_y_tw{$i}#{$blockIdx}", $percentageFormatted . '%');
                } else {
                    $templateProcessor->setValue("target_y_tw{$i}#{$blockIdx}", '-');
                }
            }
            $templateProcessor->setValue("total_target_x#{$blockIdx}", $totalTargetX ?: '-');
            $templateProcessor->setValue("total_target_y#{$blockIdx}", $totalTargetY ?: '-');

            // Rincian Output (Tabel RO via cloneRow)
            $anggarans = $indikator->anggarans;
            $roCloned = false;
            try {
                $templateProcessor->cloneRow("ro#{$blockIdx}", count($anggarans) > 0 ? count($anggarans) : 1);
                $roCloned = true;
            } catch (\PhpOffice\PhpWord\Exception\Exception $e) {
                // Ignore cloneRow failure, we will replace the raw block tags with '-'
            }

            if ($roCloned) {
                if (count($anggarans) > 0) {
                    foreach ($anggarans as $outIndex => $tRo) {
                        $rowIdx = $outIndex + 1;

                        $realisasiTW = 0;
                        for ($i = 1; $i <= $tw; $i++) {
                            $field = "realisasi_tw{$i}";
                            $realisasiTW += (float) $tRo->$field;
                        }

                        $paguSisa = $tRo->pagu_revisi > 0 ? ($tRo->pagu_revisi - $realisasiTW) : ($tRo->pagu_awal - $realisasiTW);

                        $templateProcessor->setValue("no_ro#{$blockIdx}#{$rowIdx}", $rowIdx . '.');
                        $templateProcessor->setValue("ro#{$blockIdx}#{$rowIdx}", $tRo->nama_ro ?? '-');
                        $templateProcessor->setValue("realisasi_volume_ro#{$blockIdx}#{$rowIdx}", '-');
                        $templateProcessor->setValue("progres_ro#{$blockIdx}#{$rowIdx}", '-');
                        $templateProcessor->setValue("pagu_awal#{$blockIdx}#{$rowIdx}", number_format($tRo->pagu_awal, 0, ',', '.'));
                        $templateProcessor->setValue("pagu_revisi#{$blockIdx}#{$rowIdx}", number_format($tRo->pagu_revisi, 0, ',', '.'));
                        $templateProcessor->setValue("pagu_sisa#{$blockIdx}#{$rowIdx}", number_format($paguSisa, 0, ',', '.'));
                        $templateProcessor->setValue("pagu_realisasi#{$blockIdx}#{$rowIdx}", number_format($realisasiTW, 0, ',', '.'));
                    }
                } else {
                    $templateProcessor->setValue("no_ro#{$blockIdx}#1", '-');
                    $templateProcessor->setValue("ro#{$blockIdx}#1", '-');
                    $templateProcessor->setValue("realisasi_volume_ro#{$blockIdx}#1", '-');
                    $templateProcessor->setValue("progres_ro#{$blockIdx}#1", '-');
                    $templateProcessor->setValue("pagu_awal#{$blockIdx}#1", '-');
                    $templateProcessor->setValue("pagu_revisi#{$blockIdx}#1", '-');
                    $templateProcessor->setValue("pagu_sisa#{$blockIdx}#1", '-');
                    $templateProcessor->setValue("pagu_realisasi#{$blockIdx}#1", '-');
                }
            } else {
                // If cloneRow failed, the tags still exist without the row suffix
                $templateProcessor->setValue("no_ro#{$blockIdx}", '-');
                $templateProcessor->setValue("ro#{$blockIdx}", '-');
                $templateProcessor->setValue("realisasi_volume_ro#{$blockIdx}", '-');
                $templateProcessor->setValue("progres_ro#{$blockIdx}", '-');
                $templateProcessor->setValue("pagu_awal#{$blockIdx}", '-');
                $templateProcessor->setValue("pagu_revisi#{$blockIdx}", '-');
                $templateProcessor->setValue("pagu_sisa#{$blockIdx}", '-');
                $templateProcessor->setValue("pagu_realisasi#{$blockIdx}", '-');
            }
        }

        $rata_rata_capaian_triwulan = $countIndikatorTriwulan > 0 ? round($sumCapaianTriwulan / $countIndikatorTriwulan, 2) : 0;
        $rata_rata_capaian_tahunan = $countIndikator > 0 ? round($sumCapaianTahunan / $countIndikator, 2) : 0;
        
        try {
            $templateProcessor->setValue('rata_rata_capaian_triwulan', $rata_rata_capaian_triwulan);
        } catch (\Throwable $e) {}
        try {
            $templateProcessor->setValue('rata_rata_capaian_tahunan', $rata_rata_capaian_tahunan);
        } catch (\Throwable $e) {}

        // Efisiensi Table (Clone Row for sasaran outside the block_indikator)
        $sasarans = $indikators->groupBy('sasaran')->filter(function ($value, $key) {
            return !empty($key);
        });

        if ($sasarans->count() > 0) {
            $sasaranCloned = false;
            try {
                $templateProcessor->cloneRow('sasaran', $sasarans->count());
                $sasaranCloned = true;
            } catch (\PhpOffice\PhpWord\Exception\Exception $e) {
                // Ignore cloneRow failure
            }

            if ($sasaranCloned) {
                $idx = 1;
                foreach ($sasarans as $sasaranName => $inds) {
                    $firstInd = $inds->first();
                    $kodeSasaran = $firstInd->kode_sasaran;

                    $sa = $sasaranAnggarans->where('kode', $kodeSasaran)->first();
                    $sumAwal = $sa ? $sa->pagu_awal : 0;
                    $sumRevisi = $sa ? $sa->pagu_revisi : 0;
                    $sumRealisasi = 0;
                    if ($sa) {
                        for ($i = 1; $i <= $tw; $i++) {
                            $field = "realisasi_tw{$i}";
                            $sumRealisasi += (float) $sa->$field;
                        }
                    }

                    $templateProcessor->setValue("sasaran#{$idx}", $sasaranName);
                    $templateProcessor->setValue("pagu_awal_sasaran#{$idx}", number_format($sumAwal, 0, ',', '.'));
                    $templateProcessor->setValue("pagu_revisi_sasaran#{$idx}", number_format($sumRevisi, 0, ',', '.'));
                    $templateProcessor->setValue("pagu_realisasi_sasaran#{$idx}", number_format($sumRealisasi, 0, ',', '.'));
                    $idx++;
                }
            } else {
                $templateProcessor->setValue('sasaran', '-');
                $templateProcessor->setValue('pagu_awal_sasaran', '-');
                $templateProcessor->setValue('pagu_revisi_sasaran', '-');
                $templateProcessor->setValue('pagu_realisasi_sasaran', '-');
            }
        } else {
            $templateProcessor->setValue('sasaran', '-');
            $templateProcessor->setValue('pagu_awal_sasaran', '-');
            $templateProcessor->setValue('pagu_revisi_sasaran', '-');
            $templateProcessor->setValue('pagu_realisasi_sasaran', '-');
        }

        $fileName = 'Notulen_Capaian_TW' . $validated['triwulan'] . '_' . $validated['tahun'] . '.docx';
        $tempPath = storage_path('app/public/' . $fileName);
        $templateProcessor->saveAs($tempPath);

        return response()->download($tempPath)->deleteFileAfterSend(true);
    }

    
public function exportNotulenCapaianHtml(Request $request)
    {
        $validated = $request->validate([
            'tahun' => 'required|integer',
            'triwulan' => 'required|integer|between:1,4',
            'tanggal' => 'required|date',
            'waktu' => 'required|string',
            'tempat' => 'required|string',
            'pimpinan_id' => 'required|exists:pegawais,id',
            'notulis_id' => 'required|exists:pegawais,id',
        ]);

        $pimpinan = \App\Models\Pegawai::find($validated['pimpinan_id']);
        $notulis = \App\Models\Pegawai::find($validated['notulis_id']);

        \Carbon\Carbon::setLocale('id');
        $date = \Carbon\Carbon::parse($validated['tanggal']);
        $formattedDate = $date->translatedFormat('l, d F Y');
        $tanggalNotula = $date->translatedFormat('d F Y');

        $triwulans = ['', 'TRIWULAN I', 'TRIWULAN II', 'TRIWULAN III', 'TRIWULAN IV'];
        $triwulans1 = ['', 'Triwulan I', 'Triwulan II', 'Triwulan III', 'Triwulan IV'];

        $indikators = \App\Models\Indikator::with([
            'target',
            'realisasis' => function ($q) use ($validated) {
                $q->where('triwulan', $validated['triwulan']);
            },
            'capaianKinerjas' => function ($q) use ($validated) {
                $q->where('tahun', $validated['tahun'])->where('triwulan', $validated['triwulan']);
            },
            'anggarans' => function ($q) use ($validated) {
                $q->where('tahun', $validated['tahun']);
            },
            'outputMasters'
        ])->get();

        $sasaranAnggarans = \App\Models\SasaranAnggaran::where('tahun', $validated['tahun'])->get();

        $sumCapaianTriwulan = 0;
        $sumCapaianTahunan = 0;
        $countIndikator = count($indikators);
        $countIndikatorTriwulan = 0;

        foreach ($indikators as $indikator) {
            $realisasi = $indikator->realisasis->first();
            $targetField = 'target_tw' . $validated['triwulan'];
            $target = $indikator->target ? $indikator->target->$targetField : '-';
            $capaian = $realisasi ? $realisasi->realisasi_kumulatif : 0;
            $targetTahunan = $indikator->target_tahunan ?? 0;

            $capaian_triwulan = 0;
            if (is_numeric($target) && $target > 0) {
                $capaian_triwulan = round(($capaian / $target) * 100, 2);
                if ($capaian_triwulan > 120) $capaian_triwulan = 120;
            }

            $capaian_tahunan = 0;
            if (is_numeric($targetTahunan) && $targetTahunan > 0) {
                $capaian_tahunan = round(($capaian / $targetTahunan) * 100, 2);
                if ($capaian_tahunan > 120) $capaian_tahunan = 120;
            }

            $sumCapaianTriwulan += $capaian_triwulan;
            $sumCapaianTahunan += $capaian_tahunan;

            if ($capaian_triwulan != 0)
                $countIndikatorTriwulan++;
        }

        $rata_rata_capaian_triwulan = $countIndikatorTriwulan > 0 ? round($sumCapaianTriwulan / $countIndikatorTriwulan, 2) : 0;
        $rata_rata_capaian_tahunan = $countIndikator > 0 ? round($sumCapaianTahunan / $countIndikator, 2) : 0;

        return view('template_word.notulen_html', compact(
            'indikators',
            'sasaranAnggarans',
            'pimpinan',
            'notulis',
            'validated',
            'formattedDate',
            'tanggalNotula',
            'triwulans',
            'triwulans1',
            'rata_rata_capaian_triwulan',
            'rata_rata_capaian_tahunan'
        ));
    }
public function exportSuratUndangan(Request $request)
    {
        $validated = $request->validate([
            'nomor_surat' => 'required|string',
            'sifat_surat' => 'required|string',
            'lampiran' => 'required|string',
            'perihal' => 'required|string',
            'tgl_surat' => 'required|date',
            'undangan' => 'required|string',
            'isi_undangan' => 'required|string',
            'hari_tanggal_kegiatan' => 'required|date',
            'waktu_kegiatan' => 'required|string',
            'tempat_kegiatan' => 'required|string',
            'agend_kegiatan' => 'required|string', // typo in user template
        ]);

        \Carbon\Carbon::setLocale('id');
        $tgl_surat = \Carbon\Carbon::parse($validated['tgl_surat'])->translatedFormat('d F Y');
        $hari_tanggal_kegiatan = \Carbon\Carbon::parse($validated['hari_tanggal_kegiatan'])->translatedFormat('l, d F Y');

        $templatePath = storage_path('app/templates/surat_undangan_rapat.docx');
        if (!file_exists($templatePath)) {
            return redirect()->back()->with('error', 'File template Surat Undangan (surat_undangan_rapat.docx) tidak ditemukan di folder storage/app/templates.');
        }

        $templateProcessor = new TemplateProcessor($templatePath);

        // Standard string replaces
        $templateProcessor->setValue('nomor_surat', htmlspecialchars($validated['nomor_surat']));
        $templateProcessor->setValue('sifat_surat', htmlspecialchars($validated['sifat_surat']));
        $templateProcessor->setValue('lampiran', htmlspecialchars($validated['lampiran']));
        $templateProcessor->setValue('perihal', htmlspecialchars($validated['perihal']));
        $templateProcessor->setValue('tgl_surat', $tgl_surat);
        $templateProcessor->setValue('hari_tanggal_kegiatan', $hari_tanggal_kegiatan);
        $templateProcessor->setValue('waktu_kegiatan', htmlspecialchars($validated['waktu_kegiatan']));
        $templateProcessor->setValue('tempat_kegiatan', htmlspecialchars($validated['tempat_kegiatan']));

        // Multi-line values
        // Convert HTML from TinyMCE to Word line breaks for undangan
        $undanganHtml = $validated['undangan'];
        $undanganHtml = str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $undanganHtml);
        $undanganHtml = strip_tags($undanganHtml);
        $undanganHtml = preg_replace("/\n\n+/", "\n\n", trim($undanganHtml));
        $undanganFormatted = str_replace("\n", '</w:t><w:br/><w:t>', htmlspecialchars($undanganHtml));
        $templateProcessor->setValue('undangan', $undanganFormatted);

        // Convert HTML from TinyMCE to Word line breaks for isi_undangan
        $isiUndanganHtml = $validated['isi_undangan'];
        $isiUndanganHtml = str_replace(['<br>', '<br/>', '<br />', '</p>'], "\n", $isiUndanganHtml);
        $isiUndanganHtml = strip_tags($isiUndanganHtml);
        // Clean up excessive newlines
        $isiUndanganHtml = preg_replace("/\n\n+/", "\n\n", trim($isiUndanganHtml));
        $isiUndanganFormatted = str_replace("\n", '</w:t><w:br/><w:t>', htmlspecialchars($isiUndanganHtml));
        $templateProcessor->setValue('isi_undangan', $isiUndanganFormatted);

        $agendaFormatted = str_replace("\n", '</w:t><w:br/><w:t>', htmlspecialchars($validated['agend_kegiatan']));
        $templateProcessor->setValue('agend_kegiatan', $agendaFormatted);

        $fileName = 'Surat_Undangan_' . time() . '.docx';
        $tempPath = storage_path('app/public/' . $fileName);
        $templateProcessor->saveAs($tempPath);

        return response()->download($tempPath)->deleteFileAfterSend(true);
    }

    public function exportDaftarHadir(Request $request)
    {
        $validated = $request->validate([
            'judul_kegiatan' => 'required|string',
            'tanggal_kegiatan' => 'required|date',
            'pimpinan_id' => 'required|exists:pegawais,id',
            'pembuat_id' => 'required|exists:pegawais,id',
            'jumlah_baris' => 'nullable|integer|min:1|max:100',
            'tampilkan_nama' => 'nullable'
        ]);

        $pimpinan = Pegawai::find($validated['pimpinan_id']);
        $pembuat = Pegawai::find($validated['pembuat_id']);

        $tampilkan_nama = isset($validated['tampilkan_nama']) && $validated['tampilkan_nama'] === 'on';

        $pegawais = [];
        $jumlah_baris = 20;

        if ($tampilkan_nama) {
            $pegawais = Pegawai::orderBy('pangkat_golongan', 'desc')->orderBy('nip', 'asc')->get();
            $jumlah_baris = count($pegawais);
        } else {
            $jumlah_baris = $validated['jumlah_baris'] ?? 20;
        }

        \Carbon\Carbon::setLocale('id');
        $tanggal = \Carbon\Carbon::parse($validated['tanggal_kegiatan'])->translatedFormat('d F Y');

        return view('template_word.daftar_hadir', compact('validated', 'tanggal', 'pimpinan', 'pembuat', 'jumlah_baris', 'tampilkan_nama', 'pegawais'));
    }

    public function downloadTemplateRtl($id)
    {
        $rtl = \App\Models\KendalaRtl::with('indikator')->findOrFail($id);

        $path = storage_path('app/templates/template_bukti_tindak_lanjut.docx');
        if (!file_exists($path)) {
            return back()->with('error', 'File template tidak ditemukan.');
        }

        try {
            $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($path);
            
            // Replace variables if they exist in the template
            $templateProcessor->setValue('sasaran', htmlspecialchars($rtl->indikator->sasaran ?? '-'));
            $templateProcessor->setValue('indikator', htmlspecialchars($rtl->indikator->indikator_kinerja ?? '-'));
            
            $rtlText = $rtl->rtl ?? '-';
            $lines = explode("\n", $rtlText);
            $cleanedLines = [];
            $replacements = [];
            
            foreach ($lines as $line) {
                // Hapus awalan strip, bullet, bintang, dan spasi kosong di awal kalimat
                $cleanedLine = preg_replace('/^[-•*\s]+/', '', $line);
                if (trim($cleanedLine) !== '') {
                    $cleaned = htmlspecialchars(trim($cleanedLine));
                    $cleanedLines[] = $cleaned;
                    $replacements[] = ['rtl' => $cleaned];
                }
            }

            if (empty($replacements)) {
                $replacements[] = ['rtl' => '-'];
                $cleanedLines[] = '-';
            }

            // Cara 1: Menggunakan block untuk dukungan bullet Word (disarankan)
            try {
                $templateProcessor->cloneBlock('block_rtl', 0, true, false, $replacements);
            } catch (\Throwable $e) {
                // Abaikan jika block_rtl tidak ada di template
            }

            // Cara 2: Fallback jika user masih memakai ${rtl} biasa tanpa block
            $rtlFormatted = implode('</w:t><w:br/><w:t>', $cleanedLines);
            $templateProcessor->setValue('rtl', $rtlFormatted);

            $fileName = 'Template_Bukti_Tindak_Lanjut_RTL_' . time() . '.docx';
            $tempPath = storage_path('app/public/' . $fileName);
            $templateProcessor->saveAs($tempPath);

            return response()->download($tempPath, 'Template_RTL_' . $rtl->indikator->kode . '.docx')->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            // Fallback to static download if processing fails (e.g., corrupted file)
            return response()->download($path, 'Template_Bukti_Tindak_Lanjut_RTL.docx');
        }
    }
}
