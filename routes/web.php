<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IndikatorController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\TargetController;
use App\Http\Controllers\AnalisisController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\PublicInputController;
use App\Http\Controllers\CapaianKinerjaController;
use App\Http\Controllers\RenstraController;
use App\Http\Controllers\PkTahunanController;
use App\Http\Controllers\EvaluasiTahunanController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/', function() {
        return redirect()->route('dashboard');
    })->name('home');
    Route::get('/api/kegiatan/{indikator_id}', [PublicInputController::class, 'getKegiatan'])->name('api.kegiatan');
    Route::post('/aktivitas', [PublicInputController::class, 'storeAktivitas'])->name('public.aktivitas.store');
    Route::post('/kendala', [PublicInputController::class, 'storeKendala'])->name('public.kendala.store');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::post('indikator/import', [IndikatorController::class, 'import'])->name('indikator.import');
    Route::post('indikator/import-xy', [IndikatorController::class, 'importXY'])->name('indikator.import-xy');
    Route::get('indikator/template', [IndikatorController::class, 'downloadTemplate'])->name('indikator.template');
    Route::get('indikator/template-xy', [IndikatorController::class, 'downloadTemplateXY'])->name('indikator.template-xy');
    Route::resource('indikator', IndikatorController::class);
    Route::post('indikator/{indikator}/tautan', [IndikatorController::class, 'updateTautan'])->name('indikator.tautan');
    Route::post('indikator/{indikator}/rich-content', [IndikatorController::class, 'updateRichContent'])->name('indikator.rich-content');
    Route::post('indikator/{indikator}/media', [IndikatorController::class, 'uploadMedia'])->name('indikator.media');
    
    Route::post('pegawai/sync-api', [PegawaiController::class, 'syncApi'])->name('pegawai.sync-api');
    Route::post('pegawai/import', [PegawaiController::class, 'import'])->name('pegawai.import');
    Route::get('pegawai/template', [PegawaiController::class, 'downloadTemplate'])->name('pegawai.template');
    Route::resource('pegawai', PegawaiController::class);
    Route::post('pegawai/{id}/activate', [PegawaiController::class, 'activateAccount'])->name('pegawai.activate');

    Route::post('kegiatan-master/import', [\App\Http\Controllers\Admin\KegiatanMasterController::class, 'import'])->name('kegiatan-master.import');
    Route::get('kegiatan-master/template', [\App\Http\Controllers\Admin\KegiatanMasterController::class, 'downloadTemplate'])->name('kegiatan-master.template');
    Route::resource('kegiatan-master', \App\Http\Controllers\Admin\KegiatanMasterController::class);
    Route::post('kegiatan-master/{kegiatan_master}/sync-anggota', [\App\Http\Controllers\Admin\KegiatanMasterController::class, 'syncAnggota'])->name('kegiatan-master.sync-anggota');
    
    Route::post('output-master/import', [\App\Http\Controllers\Admin\OutputMasterController::class, 'import'])->name('output-master.import');
    Route::get('output-master/template', [\App\Http\Controllers\Admin\OutputMasterController::class, 'downloadTemplate'])->name('output-master.template');
    Route::post('output-master/{output_master}/toggle-status', [\App\Http\Controllers\Admin\OutputMasterController::class, 'toggleStatus'])->name('output-master.toggle-status');
    Route::post('output-master/{output_master}/upload', [\App\Http\Controllers\Admin\OutputMasterController::class, 'uploadFile'])->name('output-master.upload');
    Route::resource('output-master', \App\Http\Controllers\Admin\OutputMasterController::class);

    Route::get('capaian-kinerja', [CapaianKinerjaController::class, 'index'])->name('capaian-kinerja.index');
    Route::get('capaian-kinerja/{indikator}/previous-data', [CapaianKinerjaController::class, 'getDataPrevious'])->name('capaian-kinerja.previous-data');
    Route::post('capaian-kinerja', [CapaianKinerjaController::class, 'store'])->name('capaian-kinerja.store');
    Route::post('capaian-kinerja/import', [CapaianKinerjaController::class, 'import'])->name('capaian-kinerja.import');
    Route::get('capaian-kinerja/template', [CapaianKinerjaController::class, 'template'])->name('capaian-kinerja.template');
    
    Route::get('monitoring-capaian', [App\Http\Controllers\MonitoringCapaianController::class, 'index'])->name('monitoring-capaian.index');
    
    Route::get('riwayat-kendala', [App\Http\Controllers\RiwayatKendalaController::class, 'index'])->name('riwayat-kendala.index');
    Route::put('riwayat-kendala/{id}', [App\Http\Controllers\RiwayatKendalaController::class, 'update'])->name('riwayat-kendala.update');

    // Issues & Kendala Baru
    Route::post('issues/store', [App\Http\Controllers\IssueController::class, 'store'])->name('issues.store');

    // Evaluasi Kinerja (Daftar IKU & Lapor Kendala)
    Route::get('evaluasi-kinerja', [App\Http\Controllers\EvaluasiKinerjaController::class, 'index'])->name('evaluasi-kinerja.index');

    // Anggaran & Realisasi
    Route::get('anggaran', [App\Http\Controllers\IndikatorAnggaranController::class, 'index'])->name('anggaran.index');
    Route::post('anggaran', [App\Http\Controllers\IndikatorAnggaranController::class, 'store'])->name('anggaran.store');
    Route::post('anggaran/sasaran', [App\Http\Controllers\IndikatorAnggaranController::class, 'storeSasaran'])->name('anggaran.storeSasaran');
    Route::get('anggaran/template', [App\Http\Controllers\IndikatorAnggaranController::class, 'downloadTemplate'])->name('anggaran.template');
    Route::post('anggaran/import', [App\Http\Controllers\IndikatorAnggaranController::class, 'import'])->name('anggaran.import');

    // Monitoring RTL (Dashboard PIC)
    Route::get('monitoring-rtl', [App\Http\Controllers\RtlController::class, 'index'])->name('monitoring-rtl.index');
    Route::post('monitoring-rtl/{id}/eksekusi', [App\Http\Controllers\RtlController::class, 'storeExecution'])->name('monitoring-rtl.eksekusi');

    // Monitoring Manajerial
    Route::get('monitoring-manajerial', [App\Http\Controllers\ManajerialController::class, 'index'])->name('monitoring-manajerial.index');
    Route::post('monitoring-manajerial/{id}/verifikasi', [App\Http\Controllers\ManajerialController::class, 'verifikasi'])->name('monitoring-manajerial.verifikasi');
    
    // Pengaturan Sistem
    Route::post('settings', function(Illuminate\Http\Request $request) {
        if (!auth()->user()->isAdmin()) abort(403);
        $request->validate([
            'default_tahun' => 'required|integer',
            'default_triwulan' => 'required|integer|between:1,4',
        ]);
        \App\Models\Setting::set('default_tahun', $request->default_tahun);
        \App\Models\Setting::set('default_triwulan', $request->default_triwulan);
        return back()->with('success', 'Pengaturan periode berhasil disimpan.');
    })->name('settings.store');
    
    Route::get('target', [TargetController::class, 'index'])->name('target.index');
    Route::get('target/{id}', [TargetController::class, 'show'])->name('target.show');
    Route::put('target/{id}', [TargetController::class, 'update'])->name('target.update');
    Route::post('target/{id}/sync-q4', [TargetController::class, 'syncQ4'])->name('target.sync-q4');
    
    // Template Word
    Route::get('/template-word', [App\Http\Controllers\TemplateWordController::class, 'index'])->name('template.word.index');
    Route::post('/template-word/export-notulen', [App\Http\Controllers\TemplateWordController::class, 'exportNotulenCapaian'])->name('template.word.export.notulen');
    Route::post('/template-word/export-undangan', [App\Http\Controllers\TemplateWordController::class, 'exportSuratUndangan'])->name('template.word.export.undangan');
    Route::post('/template-word/export-daftar-hadir', [App\Http\Controllers\TemplateWordController::class, 'exportDaftarHadir'])->name('template.word.export.daftar-hadir');

    // Master RO
    Route::post('tabel-ro/import', [App\Http\Controllers\TabelRoController::class, 'import'])->name('tabel-ro.import');
    Route::get('tabel-ro/template', [App\Http\Controllers\TabelRoController::class, 'downloadTemplate'])->name('tabel-ro.template');
    Route::resource('tabel-ro', App\Http\Controllers\TabelRoController::class)->except(['show']);

    
    Route::resource('analisis', AnalisisController::class);
    
    Route::get('export/realisasi', [ExportController::class, 'realisasi'])->name('export.realisasi');
    Route::get('export/indikator', [ExportController::class, 'indikator'])->name('export.indikator');

    Route::get('rekap-capaian', [App\Http\Controllers\CapaianController::class, 'rekap'])->name('rekap.capaian');
    Route::get('rekap-capaian/export', [App\Http\Controllers\CapaianController::class, 'export'])->name('rekap.capaian.export');
    



    // Renstra (Rencana Strategis)
    Route::resource('renstra', RenstraController::class)->except(['edit']);
    Route::post('renstra/{renstra}/activate', [RenstraController::class, 'activate'])->name('renstra.activate');
    Route::post('renstra/{renstra}/store-target', [RenstraController::class, 'storeTarget'])->name('renstra.store-target');
    Route::get('renstra/{renstra}/download-template', [RenstraController::class, 'downloadTemplate'])->name('renstra.download-template');
    Route::post('renstra/{renstra}/import-target', [RenstraController::class, 'importTarget'])->name('renstra.import-target');

    // PK Tahunan (Perjanjian Kinerja)
    Route::get('pk-tahunan', [PkTahunanController::class, 'index'])->name('pk-tahunan.index');
    Route::post('pk-tahunan/generate', [PkTahunanController::class, 'generate'])->name('pk-tahunan.generate');
    Route::put('pk-tahunan/{pk_tahunan}', [PkTahunanController::class, 'update'])->name('pk-tahunan.update');
    Route::post('pk-tahunan/{pk_tahunan}/cancel-revisi', [PkTahunanController::class, 'cancelRevisi'])->name('pk-tahunan.cancel-revisi');

    // Evaluasi Tahunan
    Route::get('evaluasi-tahunan', [EvaluasiTahunanController::class, 'index'])->name('evaluasi-tahunan.index');
    Route::post('evaluasi-tahunan', [EvaluasiTahunanController::class, 'store'])->name('evaluasi-tahunan.store');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // Analisis Kendala & Pelaksanaan RTL (Baru)
    Route::get('analisis-kendala-baru', [App\Http\Controllers\AnalisisKendalaBaruController::class, 'index'])->name('analisis-kendala-baru.index');
    Route::post('analisis-kendala-baru', [App\Http\Controllers\AnalisisKendalaBaruController::class, 'store'])->name('analisis-kendala-baru.store');

    Route::get('pelaksanaan-rtl-baru', [App\Http\Controllers\PelaksanaanRtlBaruController::class, 'index'])->name('pelaksanaan-rtl-baru.index');
    Route::post('pelaksanaan-rtl-baru/{rtl}', [App\Http\Controllers\PelaksanaanRtlBaruController::class, 'store'])->name('pelaksanaan-rtl-baru.store');
});

require __DIR__.'/auth.php';
