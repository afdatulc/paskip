<table>
    <thead>
        <tr>
            <th rowspan="2" style="background-color: #d1e7dd; border: 1px solid #000; font-weight: bold; text-align: center;">No</th>
            <th rowspan="2" style="background-color: #d1e7dd; border: 1px solid #000; font-weight: bold; text-align: center; width: 60px;">Tujuan / Sasaran / Indikator Kinerja</th>
            <th rowspan="2" style="background-color: #d1e7dd; border: 1px solid #000; font-weight: bold; text-align: center;">Jenis (IKU/Proksi)</th>
            <th rowspan="2" style="background-color: #d1e7dd; border: 1px solid #000; font-weight: bold; text-align: center;">Jenis (Periode)</th>
            <th rowspan="2" style="background-color: #d1e7dd; border: 1px solid #000; font-weight: bold; text-align: center;">Jenis (%/Non %)</th>
            <th rowspan="2" style="background-color: #d1e7dd; border: 1px solid #000; font-weight: bold; text-align: center;">Target</th>
            <th rowspan="2" style="background-color: #d1e7dd; border: 1px solid #000; font-weight: bold; text-align: center;">Satuan</th>
            <th colspan="4" style="background-color: #d1e7dd; border: 1px solid #000; font-weight: bold; text-align: center;">Alokasi Target (Kumulatif)</th>
            <th colspan="4" style="background-color: #d1e7dd; border: 1px solid #000; font-weight: bold; text-align: center;">Realisasi (Kumulatif)</th>
            <th rowspan="2" style="background-color: #d1e7dd; border: 1px solid #000; font-weight: bold; text-align: center; width: 50px;">Kendala / Hambatan</th>
        </tr>
        <tr>
            <th style="background-color: #d1e7dd; border: 1px solid #000; font-weight: bold; text-align: center;">TW I</th>
            <th style="background-color: #d1e7dd; border: 1px solid #000; font-weight: bold; text-align: center;">TW II</th>
            <th style="background-color: #d1e7dd; border: 1px solid #000; font-weight: bold; text-align: center;">TW III</th>
            <th style="background-color: #d1e7dd; border: 1px solid #000; font-weight: bold; text-align: center;">TW IV</th>
            <th style="background-color: #d1e7dd; border: 1px solid #000; font-weight: bold; text-align: center;">TW I</th>
            <th style="background-color: #d1e7dd; border: 1px solid #000; font-weight: bold; text-align: center;">TW II</th>
            <th style="background-color: #d1e7dd; border: 1px solid #000; font-weight: bold; text-align: center;">TW III</th>
            <th style="background-color: #d1e7dd; border: 1px solid #000; font-weight: bold; text-align: center;">TW IV</th>
        </tr>
    </thead>
    <tbody>
        @php $globalNo = 1; @endphp
        @foreach($grouped as $tujuan => $sasaranGroups)
            <tr>
                <td style="border: 1px solid #000;"></td>
                <td colspan="16" style="background-color: #e9ecef; border: 1px solid #000; font-weight: bold;">
                    @php
                        $firstIndInTujuan = null;
                        foreach($sasaranGroups as $sasGroup) {
                            $firstIndInTujuan = $sasGroup->first();
                            if($firstIndInTujuan) break;
                        }
                        $kodeTujuan = $firstIndInTujuan ? $firstIndInTujuan->kode_tujuan : null;
                    @endphp
                    {{ ($kodeTujuan ? $kodeTujuan . ' - ' : '') . ($tujuan ?: 'Tanpa Tujuan') }}
                </td>
            </tr>
            @foreach($sasaranGroups as $sasaran => $indicators)
                <tr>
                    <td style="border: 1px solid #000;"></td>
                    <td colspan="16" style="border: 1px solid #000; font-weight: bold; padding-left: 20px;">
                        @php
                            $kodeSasaran = $indicators->first() ? $indicators->first()->kode_sasaran : null;
                        @endphp
                        Sasaran: {{ ($kodeSasaran ? $kodeSasaran . ' - ' : '') . ($sasaran ?: 'Tanpa Sasaran') }}
                    </td>
                </tr>
                @foreach($indicators as $i)
                    @php
                        $target = $i->target;
                        $realisasis = $i->realisasis;
                        $kCount = count($i->kegiatanMasters);
                    @endphp
                    <tr>
                        <td style="border: 1px solid #000; text-align: center;">{{ $globalNo++ }}</td>
                        <td style="border: 1px solid #000;">
                            {{ $i->kode_indikator_kinerja ?: $i->kode }} - {{ $i->indikator_kinerja }}
                        </td>
                        <td style="border: 1px solid #000; text-align: center;">{{ $i->jenis_indikator }}</td>
                        <td style="border: 1px solid #000; text-align: center;">{{ $i->periode }}</td>
                        <td style="border: 1px solid #000; text-align: center;">{{ $i->tipe }}</td>
                        <td style="border: 1px solid #000; text-align: center; background-color: #e9ecef;">{{ $i->target_tahunan }}</td>
                        <td style="border: 1px solid #000; text-align: center;">{{ $i->satuan }}</td>
                        
                        {{-- Targets --}}
                        <td style="border: 1px solid #000; text-align: center;">{{ $target->target_tw1 ?? 0 }}</td>
                        <td style="border: 1px solid #000; text-align: center;">{{ $target->target_tw2 ?? 0 }}</td>
                        <td style="border: 1px solid #000; text-align: center;">{{ $target->target_tw3 ?? 0 }}</td>
                        <td style="border: 1px solid #000; text-align: center;">{{ $target->target_tw4 ?? 0 }}</td>
                        
                        {{-- Realizations --}}
                        <td style="border: 1px solid #000; text-align: center; font-weight: bold;">{{ $realisasis->where('triwulan', 1)->first()->realisasi_kumulatif ?? 0 }}</td>
                        <td style="border: 1px solid #000; text-align: center; font-weight: bold;">{{ $realisasis->where('triwulan', 2)->first()->realisasi_kumulatif ?? 0 }}</td>
                        <td style="border: 1px solid #000; text-align: center; font-weight: bold;">{{ $realisasis->where('triwulan', 3)->first()->realisasi_kumulatif ?? 0 }}</td>
                        <td style="border: 1px solid #000; text-align: center; font-weight: bold;">{{ $realisasis->where('triwulan', 4)->first()->realisasi_kumulatif ?? 0 }}</td>

                        <td style="border: 1px solid #000; vertical-align: top;" rowspan="{{ $kCount + 1 }}">
                            @foreach($kendalas as $index => $k)
                                {{ $index + 1 }}. {{ $k }}&#10;
                            @endforeach
                        </td>
                    </tr>
                    @foreach($i->kegiatanMasters as $k)
                        <tr>
                            <td style="border: 1px solid #000;"></td>
                            <td style="border: 1px solid #000; color: #6c757d; font-style: italic;"> - {{ $k->nama_kegiatan }}</td>
                            <td colspan="15" style="border: 1px solid #000;"></td>
                        </tr>
                    @endforeach
                @endforeach
            @endforeach
        @endforeach
    </tbody>
</table>
