<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notulensi Rapat & Format Laporan Capaian Kinerja</title>
    <!-- MathJax for rendering LaTeX formulas -->
    <script>
        MathJax = {
            tex: {
                inlineMath: [['$', '$'], ['\\(', '\\)']],
                displayMath: [['$$', '$$'], ['\\[', '\\]']]
            },
            svg: {
                fontCache: 'global'
            }
        };
    </script>
    <script id="MathJax-script" async src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
    <style>
        @page {
            size: A4;
            margin: 20mm;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 14px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            background-color: #525659;
        }

        .page {
            background-color: white;
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            padding: 20mm;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            box-sizing: border-box;
        }

        .doc-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .header-table th,
        .header-table td {
            border: 1px solid black;
            padding: 5px 8px;
            vertical-align: top;
            text-align: justify;
        }

        .header-table td:first-child {
            font-weight: bold;
            width: 30%;
            text-align: left;
        }

        .intro-text {
            margin-bottom: 15px;
            text-align: justify;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        tr {
            page-break-inside: avoid;
        }

        th,
        td {
            border: 1px solid black;
            padding: 6px 8px;
            vertical-align: top;
            text-align: justify;
            /* Default justify */
        }

        .center {
            text-align: center !important;
        }

        .bold {
            font-weight: bold;
        }

        .inner-table {
            width: 100%;
            margin-top: 10px;
            margin-bottom: 10px;
            border-collapse: collapse;
        }

        .inner-table th,
        .inner-table td {
            border: 1px solid black;
            text-align: center !important;
            padding: 4px;
        }

        .inner-table td:first-child {
            text-align: left !important;
        }

        ul.custom-bullet {
            list-style-type: none;
            padding-left: 0;
            margin-top: 5px;
            margin-bottom: 5px;
        }

        ul.custom-bullet li {
            position: relative;
            padding-left: 18px;
            margin-bottom: 3px;
            text-align: justify;
        }

        ul.custom-bullet.check li::before {
            content: "\2713";
            position: absolute;
            left: 0;
            font-weight: bold;
        }

        ul.custom-bullet.circle li::before {
            content: "\2022";
            position: absolute;
            left: 0;
            font-weight: bold;
        }

        .table-anggaran { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table-anggaran th, .table-anggaran td { border: 1px solid black; padding: 8px; text-align: left; }
        .table-anggaran th { text-align: center; }
        .narasi-efisiensi { margin-bottom: 30px; text-align: justify; }
        .narasi-efisiensi ul { list-style-type: none; padding-left: 20px; }
        .signature-section { display: flex; justify-content: space-between; margin-top: 50px; padding: 0 20px; }
        .signature-box { text-align: center; width: 250px; }
        .signature-name { font-weight: bold; text-decoration: underline; margin-top: 80px; }

        @media print {
            body {
                background-color: transparent;
            }

            .page {
                margin: 0;
                padding: 0;
                box-shadow: none;
                width: auto;
                min-height: auto;
            }

            .no-print {
                display: none !important;
            }
        }

        [contenteditable]:focus {
            outline: 2px dashed #ccc;
        }

        .btn-print {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: #0d6efd;
            color: white;
            border: none;
            padding: 15px 25px;
            border-radius: 50px;
            font-size: 16px;
            font-family: Arial, sans-serif;
            font-weight: bold;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background-color 0.2s;
        }

        .btn-print:hover {
            background-color: #0b5ed7;
        }
    </style>
</head>

<body>

    <button onclick="window.print()" class="btn-print no-print" contenteditable="false">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
            <path
                d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2H5zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1z" />
            <path
                d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2V7zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z" />
        </svg>
        Save as PDF / Print
    </button>

    <div class="page" contenteditable="true" spellcheck="false">
        <div class="doc-title">
            NOTULENSI RAPAT<br>
            MONITORING KINERJA {{ strtoupper($triwulans[$validated['triwulan']]) }} TAHUN {{ $validated['tahun'] }}<br>
            BPS KABUPATEN TAPIN
        </div>

        <table class="header-table">
            <tr>
                <td>Agenda Pembahasan</td>
                <td>Monitoring Kinerja {{ $triwulans1[$validated['triwulan']] }} Tahun {{ $validated['tahun'] }}</td>
            </tr>
            <tr>
                <td>Hari/Tanggal</td>
                <td>{{ $formattedDate }}</td>
            </tr>
            <tr>
                <td>Waktu</td>
                <td>{{ $validated['waktu'] }}</td>
            </tr>
            <tr>
                <td>Tempat</td>
                <td>{{ $validated['tempat'] }}</td>
            </tr>
            <tr>
                <td>Pimpinan Rapat</td>
                <td>{{ $pimpinan->nama }} {{ $pimpinan->jabatan ? '(' . $pimpinan->jabatan . ')' : '' }}</td>
            </tr>
        </table>

        <div class="bold" style="margin-bottom: 5px;">I. Capaian Kinerja {{ $triwulans1[$validated['triwulan']] }} Tahun
            {{ $validated['tahun'] }}
        </div>
        <div class="intro-text">
            Capaian Kinerja IKU {{ $triwulans1[$validated['triwulan']] }} tahun {{ $validated['tahun'] }} pada BPS
            Kabupaten Tapin sebesar {{ $rata_rata_capaian_triwulan }} persen (terhadap target
            {{ $triwulans1[$validated['triwulan']] }} {{ $validated['tahun'] }}) atau {{ $rata_rata_capaian_tahunan }}
            persen (terhadap target tahunan). Adapun penjelasan detail capaian untuk setiap indikator kinerja
            disampaikan di bawah ini.
        </div>

        @foreach($indikators as $index => $indikator)
            @php
                $realisasi = $indikator->realisasis->first();
                $capaianData = $indikator->capaianKinerjas->first();

                $targetField = 'target_tw' . $validated['triwulan'];
                $target = $indikator->target ? $indikator->target->$targetField : '-';
                $capaian = $realisasi ? $realisasi->realisasi_kumulatif : 0;

                $targetTahunan = $indikator->target_tahunan ?? 0;
                $capaian_triwulan = (is_numeric($target) && $target > 0) ? round(($capaian / $target) * 100, 2) : 0;
                if ($capaian_triwulan > 120)
                    $capaian_triwulan = 120;

                $capaian_tahunan = (is_numeric($targetTahunan) && $targetTahunan > 0) ? round(($capaian / $targetTahunan) * 100, 2) : 0;
                if ($capaian_tahunan > 120)
                    $capaian_tahunan = 120;

                $anggarans = $indikator->anggarans;

                // Kendala, Solusi, RTL
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
                    return $result;
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

            @endphp

            <div>
                <table>
                    <tbody style="page-break-inside: avoid;">
                    <tr>
                        <td colspan="1" style="width: 10%;">Sasaran</td>
                        <td colspan="6" style="border-left: none;">
                            <div style="display: flex;">
                                <div style="margin-right: 8px;">:</div>
                                <div style="flex: 1;">{{ $indikator->sasaran ?? '-' }}</div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th rowspan="2" class="center" style="vertical-align: middle; width: 5%;">No.</th>
                        <th rowspan="2" class="center" style="vertical-align: middle; width: 25%;">Indikator<br>Kinerja</th>
                        <th rowspan="2" class="center" style="vertical-align: middle; width: 10%;">Target PK</th>
                        <th colspan="3" class="center">{{ $triwulans1[$validated['triwulan']] }}</th>
                        <th rowspan="2" class="center" style="vertical-align: middle; width: 15%;">
                            Capaian<br>Terhadap<br>Target PK</th>
                    </tr>
                    <tr>
                        <th class="center" style="width: 10%;">Target</th>
                        <th class="center" style="width: 10%;">Realisasi</th>
                        <th class="center" style="width: 15%;">Capaian<br>Terhadap<br>Target<br>Triwulanan</th>
                    </tr>
                    <tr>
                        <td class="center">{{ $indikator->kode }}</td>
                        <td>{{ $indikator->indikator_kinerja }}</td>
                        <td class="center">{{ $targetTahunan }}</td>
                        <td class="center">{{ $target }}</td>
                        <td class="center">{{ $capaian }}</td>
                        <td class="center">{{ $capaian_triwulan }}%</td>
                        <td class="center">{{ $capaian_tahunan }}%</td>
                    </tr>
                    </tbody>
                    <tbody style="page-break-inside: avoid;">
                    <tr>
                        <th colspan="7" class="center">Analisis Capaian Kinerja</th>
                    </tr>
                    <tr>
                        <td colspan="7">
                            <div class="bold">Realisasi Volume RO dan Progress Pelaksanaan Kegiatan sampai dengan Triwulan
                                <u>Berjalan</u>
                            </div>

                            <table class="inner-table">
                                <tr>
                                    <th class="center">Rincian Output</th>
                                    <th class="center" style="width: 25%;">Pagu Awal</th>
                                    <th class="center" style="width: 25%;">Realisasi TW {{ $validated['triwulan'] }}</th>
                                </tr>
                                @forelse($anggarans as $tRo)
                                    @php
                                        $realisasiTW = 0;
                                        for ($i = 1; $i <= $validated['triwulan']; $i++) {
                                            $field = "realisasi_tw{$i}";
                                            $realisasiTW += (float) $tRo->$field;
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $tRo->nama_ro }}</td>
                                        <td class="center">{{ number_format($tRo->pagu_awal, 0, ',', '.') }}</td>
                                        <td class="center">{{ number_format($realisasiTW, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="center">-</td>
                                    </tr>
                                @endforelse
                            </table>
                        </td>
                    </tr>
                    </tbody>
                    <tbody style="page-break-inside: avoid;">
                    <tr>
                        <td colspan="7">
                            <div class="bold">Kendala:</div>
                            @if(count($kendalas) > 0)
                                <ul class="custom-bullet check">
                                    @foreach($kendalas as $k)
                                        <li>{{ $k }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <div>-</div>
                            @endif
                        </td>
                    </tr>
                    </tbody>
                    <tbody style="page-break-inside: avoid;">
                    <tr>
                        <td colspan="7">
                            <div class="bold">Solusi:</div>
                            @if(count($solusis) > 0)
                                <ul class="custom-bullet check">
                                    @foreach($solusis as $s)
                                        <li>{{ $s }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <div>-</div>
                            @endif
                        </td>
                    </tr>
                    </tbody>
                    <tbody style="page-break-inside: avoid;">
                    <tr>
                        <td colspan="4" style="width: 60%;">
                            <div class="bold">Rencana Tindak Lanjut:</div>
                            @if(count($rtlsDesc) > 0)
                                <ul class="custom-bullet circle">
                                    @foreach($rtlsDesc as $r)
                                        <li>{{ $r }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <div>-</div>
                            @endif
                        </td>
                        <td colspan="3" style="width: 40%;">
                            <div class="bold">PIC Tindak Lanjut:</div>
                            @if(count($rtlsPic) > 0)
                                <ul class="custom-bullet circle">
                                    @foreach(array_unique($rtlsPic) as $p)
                                        <li>{{ $p }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <div>-</div>
                            @endif

                            <div class="bold" style="margin-top: 15px;">Batas Waktu Tindak Lanjut:</div>
                            @if(count($rtlsBatas) > 0)
                                <ul class="custom-bullet circle">
                                    @foreach(array_unique($rtlsBatas) as $b)
                                        <li>{{ $b }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <div>-</div>
                            @endif
                        </td>
                    </tr>
                    </tbody>
                    <tbody style="page-break-inside: avoid;">
                    <tr>
                        <th colspan="7" class="center">Dasar Hitung/Bukti Dukung/Lainnya</th>
                    </tr>
                    <tr>
                        <td colspan="7">
                            <div><b>Basis Data:</b> <br>{!! $indikator->basis_data ?? '-' !!}</div>
                        </td>
                    </tr>
                    </tbody>
                    <tbody style="page-break-inside: avoid;">
                    <tr>
                        <td colspan="7">
                            <div><b>Dasar Hitung:</b>
                                <br>{!! $capaianData->dasar_hitung ?? ($indikator->dasar_hitung ?? '-') !!}
                            </div>
                        </td>
                    </tr>
                    </tbody>
                    <tbody style="page-break-inside: avoid;">
                    <tr>
                        <td colspan="7">
                            <div><b>Argumen Logis:</b> <br>{!! $capaianData->argumen_logis ?? '-' !!}</div>
                        </td>
                    </tr>
                    </tbody>
                    <tbody style="page-break-inside: avoid;">
                    <tr>
                        <td colspan="7">
                            <div class="bold">Tautan Bukti Dukung Realisasi IKU:</div>
                            <div>{{ $capaianData->link_bukti_kinerja ?? ($indikator->link_bukti_kinerja ?? '-') }}</div>
                        </td>
                    </tr>
                    </tbody>
                    <tbody style="page-break-inside: avoid;">
                    <tr>
                        <td colspan="7">
                            <div class="bold">Tautan Bukti Dukung Rencana Tindak Lanjut Triwulan Sebelumnya:</div>
                            <div>
                                {{ $capaianData->link_bukti_tindak_lanjut ?? ($indikator->link_bukti_tindak_lanjut ?? '-') }}
                            </div>
                        </td>
                    </tr>
                    </tbody>
                    <tbody style="page-break-inside: avoid;">
                    <tr>
                        <td colspan="7">
                            <div class="bold">Penjelasan/pembahasan lainnya:</div>
                            <div>{!! $capaianData->penjelasan_lainnya ?? ($indikator->penjelasan_lainnya ?? '-') !!}</div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>

    <div class="page page-break page-landscape">
        <div class="bold" style="margin-bottom: 15px; font-size: 16px;">II. Realisasi Anggaran dan Upaya Efisiensi sampai dengan Triwulan Berjalan</div>
        
        <table class="table-anggaran">
            <thead>
                <tr>
                    <th style="width: 40%;">Sasaran Kegiatan</th>
                    <th style="width: 20%;">PAGU Awal (Rp)</th>
                    <th style="width: 20%;">PAGU Revisi Sampai<br>dengan triwulan berjalan<br>(Rp)</th>
                    <th style="width: 20%;">Realisasi sampai dengan<br>triwulan Berjalan (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $sasarans = $indikators->groupBy('sasaran')->filter(function ($value, $key) {
                        return !empty($key);
                    });
                    $tw = $validated['triwulan'];
                @endphp
                @if($sasarans->count() > 0)
                    @foreach($sasarans as $sasaranName => $inds)
                        @php
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
                        @endphp
                        <tr>
                            <td>{{ $sasaranName }}</td>
                            <td class="center">{{ number_format($sumAwal, 0, ',', '.') }}</td>
                            <td class="center">{{ number_format($sumRevisi, 0, ',', '.') }}</td>
                            <td class="center">{{ number_format($sumRealisasi, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="4" class="center">Tidak ada data sasaran anggaran</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="page page-break">
        <div class="bold" style="margin-bottom: 15px; font-size: 16px;">III. Upaya Efisiensi dan Penutup</div>
        
        <div class="narasi-efisiensi">
            <p>
                Berdasarkan data realisasi anggaran hingga {{ $triwulans1[$validated['triwulan']] }} Tahun {{ $validated['tahun'] }}, telah dilakukan beberapa upaya efisiensi dalam pelaksanaan kegiatan di BPS Kabupaten Tapin. Upaya tersebut antara lain:
            </p>
            <ul>
                <li>&bull; <strong>Optimalisasi Rapat Daring:</strong> Mengurangi frekuensi rapat tatap muka dan memaksimalkan penggunaan platform <i>video conference</i> untuk koordinasi internal maupun eksternal, sehingga menekan biaya konsumsi dan perjalanan dinas.</li>
                <li>&bull; <strong>Penghematan ATK dan Barang Cetakan:</strong> Menerapkan kebijakan <i>paperless</i> untuk dokumen-dokumen internal dan laporan berkala. Pencetakan hanya dilakukan untuk dokumen yang benar-benar membutuhkan fisik <i>hardcopy</i>.</li>
                <li>&bull; <strong>Sinergi Kegiatan Lapangan:</strong> Menggabungkan beberapa pencacahan survei yang lokasinya berdekatan dalam satu kali perjalanan dinas petugas, sehingga mengefisienkan alokasi anggaran transportasi lokal.</li>
            </ul>
            <p>
                Secara keseluruhan, kinerja BPS Kabupaten Tapin pada {{ $triwulans1[$validated['triwulan']] }} Tahun {{ $validated['tahun'] }} berjalan cukup baik dengan capaian IKU yang melampaui target pada beberapa indikator, meskipun terdapat beberapa kendala di lapangan yang dapat diatasi dengan solusi yang tepat. Upaya efisiensi anggaran akan terus dimonitor dan ditingkatkan pada triwulan berikutnya.
            </p>
            <p>
                Demikian notulensi rapat monitoring kinerja ini dibuat untuk dapat dipergunakan sebagaimana mestinya.
            </p>
        </div>

        <div class="signature-section">
            <div class="signature-box">
                <div>Notulis,</div>
                <div class="signature-name">{{ $notulis->nama }}</div>
                <div>NIP. {{ $notulis->nip }}</div>
            </div>
            <div class="signature-box">
                <div>Mengetahui,<br>Kepala BPS Kabupaten Tapin</div>
                <div class="signature-name">{{ $pimpinan->nama }}</div>
                <div>NIP. {{ $pimpinan->nip }}</div>
            </div>
        </div>
    </div>

</body>
</html>
