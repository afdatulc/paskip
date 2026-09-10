<?php

namespace App\Http\Controllers;

use App\Models\Indikator;
use App\Models\IndikatorMedia;
use App\Http\Requests\IndikatorRequest;
use App\Imports\IndikatorImport;
use App\Imports\IndikatorXYImport;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class IndikatorController extends Controller
{
    public function index()
    {
        $query = Indikator::with('pic')
            ->withCount([
                'outputMasters'
            ]);
        
        if (!auth()->user()->isAdminOrPimpinan()) {
            $pegawaiId = auth()->user()->pegawai_id;
            if ($pegawaiId) {
                if (auth()->user()->isPic()) {
                    $query->where('pic_id', $pegawaiId);
                } elseif (auth()->user()->isAnggota()) {
                    $query->whereHas('anggotas', function($q) use ($pegawaiId) {
                        $q->where('pegawai_id', $pegawaiId);
                    });
                } else {
                    $query->whereRaw('1 = 0');
                }
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $indikators = $query->get();
        $pegawais = \App\Models\Pegawai::orderBy('pangkat_golongan', 'desc')->orderBy('nip', 'asc')->get();
        return view('indikator.index', compact('indikators', 'pegawais'));
    }

    public function store(IndikatorRequest $request)
    {
        if (!auth()->user()->isAdminOrPimpinan()) {
            abort(403, 'Hanya Admin yang dapat menambahkan indikator.');
        }

        $data = $request->validated();
        
        $indikatorData = collect($data)->except([
            'target_tw1', 'target_tw2', 'target_tw3', 'target_tw4',
            'target_x_tw1', 'target_x_tw2', 'target_x_tw3', 'target_x_tw4',
            'target_y_tw1', 'target_y_tw2', 'target_y_tw3', 'target_y_tw4'
        ])->toArray();

        $indikator = Indikator::create($indikatorData);

        $targetData = collect($data)->only([
            'target_tw1', 'target_tw2', 'target_tw3', 'target_tw4',
            'target_x_tw1', 'target_x_tw2', 'target_x_tw3', 'target_x_tw4',
            'target_y_tw1', 'target_y_tw2', 'target_y_tw3', 'target_y_tw4'
        ])->toArray();

        $indikator->target()->create($targetData);
        
        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Indikator berhasil ditambahkan',
                'data' => $indikator
            ]);
        }
        
        return redirect()->route('indikator.index')->with('success', 'Indikator berhasil ditambahkan');
    }

    public function show(Indikator $indikator)
    {
        $indikator->load('target');
        return response()->json($indikator);
    }

    public function update(IndikatorRequest $request, Indikator $indikator)
    {
        $data = $request->validated();
        $user = auth()->user();

        if (!$user->isAdminOrPimpinan()) {
            if ($indikator->pic_id != $user->pegawai_id) {
                abort(403, 'Anda bukan PIC untuk indikator ini.');
            }
            // PIC cannot change the PIC field
            unset($data['pic_id']);
        }

        $indikatorData = collect($data)->except([
            'target_tw1', 'target_tw2', 'target_tw3', 'target_tw4',
            'target_x_tw1', 'target_x_tw2', 'target_x_tw3', 'target_x_tw4',
            'target_y_tw1', 'target_y_tw2', 'target_y_tw3', 'target_y_tw4'
        ])->toArray();

        $indikator->update($indikatorData);

        $targetData = collect($data)->only([
            'target_tw1', 'target_tw2', 'target_tw3', 'target_tw4',
            'target_x_tw1', 'target_x_tw2', 'target_x_tw3', 'target_x_tw4',
            'target_y_tw1', 'target_y_tw2', 'target_y_tw3', 'target_y_tw4'
        ])->toArray();

        $indikator->target()->updateOrCreate(
            ['indikator_id' => $indikator->id],
            $targetData
        );

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Indikator berhasil diperbarui',
                'data' => $indikator
            ]);
        }
        return redirect()->route('indikator.index')->with('success', 'Indikator berhasil diperbarui');
    }

    public function updateTautan(Request $request, Indikator $indikator)
    {
        $user = auth()->user();
        if (!$user->isAdminOrPimpinan() && $indikator->pic_id != $user->pegawai_id) {
            abort(403);
        }

        $validated = $request->validate([
            'dasar_hitung' => 'nullable|string',
        ]);

        $indikator->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Dasar Hitung & Tautan berhasil diperbarui',
                'data' => $indikator
            ]);
        }

        return redirect()->route('indikator.index')->with('success', 'Dasar Hitung & Tautan berhasil diperbarui');
    }

    public function destroy(Indikator $indikator)
    {
        $indikator->delete();

        if (request()->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Indikator berhasil dihapus'
            ]);
        }

        return redirect()->route('indikator.index')->with('success', 'Indikator berhasil dihapus');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new IndikatorImport, $request->file('file'));
            return redirect()->route('indikator.index')->with('success', 'Data Indikator berhasil diimport.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Format file tidak sesuai template! (' . $e->getMessage() . ')');
        }
    }

    public function importXY(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new IndikatorXYImport, $request->file('file'));
            return redirect()->route('indikator.index')->with('success', 'Data Target dan Definisi X/Y berhasil diimport.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Format file tidak sesuai template! (' . $e->getMessage() . ')');
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'kode', 'kode_tujuan', 'tujuan', 'kode_sasaran', 'sasaran', 
            'kode_indikator_kinerja', 'indikator_kinerja', 
            'jenis_indikator', 'periode', 'tipe', 'satuan', 
            'target_tahunan', 'tahun'
        ];

        return Excel::download(new class($headers) implements \Maatwebsite\Excel\Concerns\FromArray {
            protected $headers;
            public function __construct($headers) { $this->headers = $headers; }
            public function array(): array { return [$this->headers]; }
        }, 'template_import_indikator.xlsx');
    }

    public function downloadTemplateXY()
    {
        $headers = [
            'Kode Indikator', 'Definisi X', 'Definisi Y', 
            'Target Tahunan X', 'Target Tahunan Y',
            'Target X TW 1', 'Target X TW 2', 'Target X TW 3', 'Target X TW 4', 
            'Target Y TW 1', 'Target Y TW 2', 'Target Y TW 3', 'Target Y TW 4'
        ];

        return Excel::download(new class($headers) implements \Maatwebsite\Excel\Concerns\FromArray {
            protected $headers;
            public function __construct($headers) { $this->headers = $headers; }
            public function array(): array { return [$this->headers]; }
        }, 'template_import_indikator_xy.xlsx');
    }

    /**
     * Simpan konten rich-text: basis_data, dasar_hitung, definisi_x, definisi_y.
     */
    public function updateRichContent(Request $request, Indikator $indikator)
    {
        $user = auth()->user();
        if (!$user->isAdminOrPimpinan() && $indikator->pic_id != $user->pegawai_id) {
            abort(403);
        }

        $validated = $request->validate([
            'basis_data'   => 'nullable|string',
            'dasar_hitung' => 'nullable|string',
            'definisi_x'   => 'nullable|string|max:500',
            'definisi_y'   => 'nullable|string|max:500',
        ]);

        $indikator->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Konten berhasil disimpan',
        ]);
    }

    /**
     * Upload foto/media untuk disematkan ke dalam rich-text editor.
     * Return URL foto yang bisa langsung digunakan oleh Quill.js.
     */
    public function uploadMedia(Request $request, Indikator $indikator)
    {
        $user = auth()->user();
        if (!$user->isAdminOrPimpinan() && $indikator->pic_id != $user->pegawai_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'file'  => 'required|file|mimes:jpg,jpeg,png,gif,webp,pdf|max:10240',
            'field' => 'required|in:basis_data,dasar_hitung',
        ]);

        $file     = $request->file('file');
        $field    = $request->input('field');
        $path     = $file->store("indikator_media/{$indikator->id}", 'public');

        // Simpan metadata media ke tabel indikator_media
        $media = IndikatorMedia::create([
            'indikator_id'  => $indikator->id,
            'field'         => $field,
            'file_path'     => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type'     => $file->getMimeType(),
            'file_size'     => $file->getSize(),
        ]);

        return response()->json([
            'status' => 'success',
            'url'    => asset('storage/' . $path),
            'id'     => $media->id,
        ]);
    }
}

