<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IndikatorController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\TargetController;

use App\Http\Controllers\ExportController;
use App\Http\Controllers\PublicInputController;
use App\Http\Controllers\CapaianKinerjaController;
use App\Http\Controllers\RenstraController;
use App\Http\Controllers\PkTahunanController;
use App\Http\Controllers\EvaluasiTahunanController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::post('/set-global-filter', [App\Http\Controllers\GlobalFilterController::class, 'update'])->name('global.filter');

    Route::get('/', function() {
        return redirect()->route('dashboard');
    })->name('home');
    Route::get('/api/kegiatan/{indikator_id}', [PublicInputController::class, 'getKegiatan'])->name('api.kegiatan');
    Route::post('/aktivitas', [PublicInputController::class, 'storeAktivitas'])->name('public.aktivitas.store');
    Route::post('/kendala', [PublicInputController::class, 'storeKendala'])->name('public.kendala.store');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // ==========================================================
    // ALL ROLES (Admin, Pimpinan, PIC, Anggota)
    // ==========================================================
    Route::middleware(['role:admin,pimpinan,pic,anggota'])->group(function () {
        // Monitoring
        Route::get('monitoring-capaian', [App\Http\Controllers\MonitoringCapaianController::class, 'index'])->name('monitoring-capaian.index');
        Route::get('monitoring-kelengkapan', [App\Http\Controllers\MonitoringKelengkapanController::class, 'index'])->name('monitoring-kelengkapan.index');
        Route::get('monitoring-rtl', [App\Http\Controllers\RtlController::class, 'index'])->name('monitoring-rtl.index');
        Route::post('monitoring-rtl/{id}/eksekusi', [App\Http\Controllers\RtlController::class, 'storeExecution'])->name('monitoring-rtl.eksekusi');
        
        // Excel FRA
        Route::get('/fra', [App\Http\Controllers\FormRencanaAksiController::class, 'index'])->name('fra.index');
        Route::get('/fra/export', [App\Http\Controllers\FormRencanaAksiController::class, 'export'])->name('fra.export');
        Route::get('/fra/download-template', [App\Http\Controllers\FormRencanaAksiController::class, 'downloadTemplate'])->name('fra.template');
        Route::post('/fra/preview', [App\Http\Controllers\FormRencanaAksiController::class, 'preview'])->name('fra.preview');
        Route::post('/fra/store', [App\Http\Controllers\FormRencanaAksiController::class, 'store'])->name('fra.store');
        Route::post('/fra/live-update', [App\Http\Controllers\FormRencanaAksiController::class, 'liveUpdate'])->name('fra.live_update');
    });

    // ==========================================================
    // ROLE: ADMIN, PIMPINAN, PIC, ANGGOTA (Master Data IKU & Pengisian)
    // ==========================================================
    Route::middleware(['role:admin,pimpinan,pic,anggota'])->group(function () {
        // Master Indikator
        Route::post('indikator/import', [IndikatorController::class, 'import'])->name('indikator.import');
        Route::post('indikator/import-xy', [IndikatorController::class, 'importXY'])->name('indikator.import-xy');
        Route::get('indikator/template', [IndikatorController::class, 'downloadTemplate'])->name('indikator.template');
        Route::get('indikator/template-xy', [IndikatorController::class, 'downloadTemplateXY'])->name('indikator.template-xy');
        Route::resource('indikator', IndikatorController::class);
        Route::post('indikator/{indikator}/tautan', [IndikatorController::class, 'updateTautan'])->name('indikator.tautan');
        Route::post('indikator/{indikator}/rich-content', [IndikatorController::class, 'updateRichContent'])->name('indikator.rich-content');
        Route::post('indikator/{indikator}/media', [IndikatorController::class, 'uploadMedia'])->name('indikator.media');

        // Master Kegiatan
        Route::post('kegiatan-master/import', [\App\Http\Controllers\Admin\KegiatanMasterController::class, 'import'])->name('kegiatan-master.import');
        Route::get('kegiatan-master/template', [\App\Http\Controllers\Admin\KegiatanMasterController::class, 'downloadTemplate'])->name('kegiatan-master.template');
        Route::resource('kegiatan-master', \App\Http\Controllers\Admin\KegiatanMasterController::class);
        Route::post('kegiatan-master/{kegiatan_master}/sync-anggota', [\App\Http\Controllers\Admin\KegiatanMasterController::class, 'syncAnggota'])->name('kegiatan-master.sync-anggota');

        // Master Output
        Route::get('output-master', [App\Http\Controllers\OutputMasterController::class, 'index'])->name('output-master.index');
        Route::post('output-master', [App\Http\Controllers\OutputMasterController::class, 'store'])->name('output-master.store');
        Route::post('output-master/import', [App\Http\Controllers\OutputMasterController::class, 'import'])->name('output-master.import');
        Route::put('output-master/{outputMaster}', [App\Http\Controllers\OutputMasterController::class, 'update'])->name('output-master.update');
        Route::delete('output-master/{outputMaster}', [App\Http\Controllers\OutputMasterController::class, 'destroy'])->name('output-master.destroy');

        // Pengisian Capaian Kinerja
        Route::get('capaian-kinerja', [CapaianKinerjaController::class, 'index'])->name('capaian-kinerja.index');
        Route::get('capaian-kinerja/{indikator}/{tahun}/{triwulan}/edit', [CapaianKinerjaController::class, 'edit'])->name('capaian-kinerja.edit');
        Route::get('capaian-kinerja/{indikator}/previous-data', [CapaianKinerjaController::class, 'getDataPrevious'])->name('capaian-kinerja.previous-data');
        Route::post('capaian-kinerja', [CapaianKinerjaController::class, 'store'])->name('capaian-kinerja.store');
        Route::post('capaian-kinerja/import', [CapaianKinerjaController::class, 'import'])->name('capaian-kinerja.import');
        Route::post('capaian-kinerja/template', [CapaianKinerjaController::class, 'template'])->name('capaian-kinerja.template');
        Route::post('capaian-kinerja/{id}/approve', [CapaianKinerjaController::class, 'approve'])->name('capaian-kinerja.approve');

        // Analisis Kendala & Pelaksanaan RTL (Baru)
        Route::get('analisis-kendala', [App\Http\Controllers\AnalisisKendalaController::class, 'index'])->name('analisis-kendala.index');
        Route::get('analisis-kendala/{id}', [App\Http\Controllers\AnalisisKendalaController::class, 'show'])->name('analisis-kendala.show');
        Route::post('analisis-kendala/import', [App\Http\Controllers\AnalisisKendalaController::class, 'import'])->name('analisis-kendala.import');
        Route::post('analisis-kendala', [App\Http\Controllers\AnalisisKendalaController::class, 'store'])->name('analisis-kendala.store');
        Route::put('analisis-kendala/{id}', [App\Http\Controllers\AnalisisKendalaController::class, 'update'])->name('analisis-kendala.update');
        Route::delete('analisis-kendala/{id}', [App\Http\Controllers\AnalisisKendalaController::class, 'destroy'])->name('analisis-kendala.destroy');

        Route::get('pelaksanaan-rtl', [App\Http\Controllers\PelaksanaanRtlController::class, 'index'])->name('pelaksanaan-rtl.index');
        Route::get('pelaksanaan-rtl/compile-all', [App\Http\Controllers\RtlCompilerController::class, 'compileAll'])->name('pelaksanaan-rtl.compile-all');
        Route::get('pelaksanaan-rtl/{id}', [App\Http\Controllers\PelaksanaanRtlController::class, 'show'])->name('pelaksanaan-rtl.show');
        Route::post('pelaksanaan-rtl/{rtl}', [App\Http\Controllers\PelaksanaanRtlController::class, 'store'])->name('pelaksanaan-rtl.store');
        Route::delete('pelaksanaan-rtl/bukti/{execution}', [App\Http\Controllers\PelaksanaanRtlController::class, 'destroyBukti'])->name('pelaksanaan-rtl.destroy-bukti');

        Route::get('evaluasi-kinerja', [App\Http\Controllers\EvaluasiKinerjaController::class, 'index'])->name('evaluasi-kinerja.index');
    });

    // ==========================================================
    // ROLE: ADMIN & PIMPINAN (Pengaturan Sistem & Master Inti)
    // ==========================================================
    Route::middleware(['role:admin,pimpinan'])->group(function () {
        // Pegawai
        Route::post('pegawai/sync-api', [PegawaiController::class, 'syncApi'])->name('pegawai.sync-api');
        Route::post('pegawai/import', [PegawaiController::class, 'import'])->name('pegawai.import');
        Route::get('pegawai/template', [PegawaiController::class, 'downloadTemplate'])->name('pegawai.template');
        Route::resource('pegawai', PegawaiController::class);
        Route::post('pegawai/{id}/activate', [PegawaiController::class, 'activateAccount'])->name('pegawai.activate');

        // Master RO
        Route::post('tabel-ro/import', [App\Http\Controllers\TabelRoController::class, 'import'])->name('tabel-ro.import');
        Route::get('tabel-ro/template', [App\Http\Controllers\TabelRoController::class, 'downloadTemplate'])->name('tabel-ro.template');
        Route::resource('tabel-ro', App\Http\Controllers\TabelRoController::class)->except(['show']);

        // Target
        Route::get('target', [TargetController::class, 'index'])->name('target.index');
        Route::get('target/{id}', [TargetController::class, 'show'])->name('target.show');
        Route::put('target/{id}', [TargetController::class, 'update'])->name('target.update');
        Route::post('target/{id}/sync-q4', [TargetController::class, 'syncQ4'])->name('target.sync-q4');
        
        // Anggaran & Realisasi
        Route::get('anggaran', [App\Http\Controllers\IndikatorAnggaranController::class, 'index'])->name('anggaran.index');
        Route::post('anggaran', [App\Http\Controllers\IndikatorAnggaranController::class, 'store'])->name('anggaran.store');
        Route::post('anggaran/sasaran', [App\Http\Controllers\IndikatorAnggaranController::class, 'storeSasaran'])->name('anggaran.storeSasaran');
        Route::get('anggaran/template', [App\Http\Controllers\IndikatorAnggaranController::class, 'downloadTemplate'])->name('anggaran.template');
        Route::post('anggaran/import', [App\Http\Controllers\IndikatorAnggaranController::class, 'import'])->name('anggaran.import');

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

        // Template Word
        Route::get('/template-word', [App\Http\Controllers\TemplateWordController::class, 'index'])->name('template.word.index');
        Route::post('/template-word/export-notulen', [App\Http\Controllers\TemplateWordController::class, 'exportNotulenCapaian'])->name('template.word.export.notulen');
        Route::post('/template-word/export-undangan', [App\Http\Controllers\TemplateWordController::class, 'exportSuratUndangan'])->name('template.word.export.undangan');
        Route::post('/template-word/export-daftar-hadir', [App\Http\Controllers\TemplateWordController::class, 'exportDaftarHadir'])->name('template.word.export.daftar-hadir');
        Route::get('/template-word/download-rtl/{id}', [App\Http\Controllers\TemplateWordController::class, 'downloadTemplateRtl'])->name('template.word.download.rtl');
        
        // Export 
        Route::get('export/realisasi', [ExportController::class, 'realisasi'])->name('export.realisasi');
        Route::get('export/indikator', [ExportController::class, 'indikator'])->name('export.indikator');
    });
});

require __DIR__.'/auth.php';
