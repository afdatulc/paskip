<?php

namespace App\Http\Controllers;

use App\Models\KendalaRtl;
use App\Models\KendalaRtlExecution;
use App\Helpers\DocxMerger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RtlCompilerController extends Controller
{
    /**
     * Compile all RTL evidence files into a single master document.
     */
    public function compileAll(Request $request)
    {
        // Must be admin
        if (!auth()->user()->isAdminOrPimpinan()) {
            abort(403, 'Unauthorized access.');
        }

        // Fetch all executions that have foto_bukti (which are Word documents)
        $executions = KendalaRtlExecution::whereNotNull('foto_bukti')
            ->orderBy('created_at', 'asc')
            ->get();

        if ($executions->isEmpty()) {
            return back()->with('error', 'Tidak ada bukti RTL yang dapat dikompilasi.');
        }

        $filesToMerge = [];
        foreach ($executions as $exec) {
            $path = storage_path('app/public/' . $exec->foto_bukti);
            // Verify if file exists and is a doc/docx
            if (file_exists($path) && in_array(pathinfo($path, PATHINFO_EXTENSION), ['doc', 'docx'])) {
                $filesToMerge[] = $path;
            }
        }

        if (empty($filesToMerge)) {
            return back()->with('error', 'File dokumen bukti tidak ditemukan atau format tidak didukung.');
        }

        // Define output path
        $timestamp = date('Ymd_His');
        $outputFilename = 'Kompilasi_Bukti_RTL_' . $timestamp . '.docx';
        $outputPath = storage_path('app/public/rtl_executions/' . $outputFilename);

        // Merge files
        $success = DocxMerger::merge($filesToMerge, $outputPath);

        if ($success && file_exists($outputPath)) {
            return response()->download($outputPath)->deleteFileAfterSend(true);
        }

        return back()->with('error', 'Terjadi kesalahan saat menggabungkan dokumen Word.');
    }
}

