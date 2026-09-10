<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KegiatanMaster;
use App\Models\Indikator;
use App\Imports\KegiatanMasterImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class KegiatanMasterController extends Controller
{
    public function index()
    {
        $query = KegiatanMaster::with(['indikator', 'ketuaTim', 'anggotas']);
        
        $indikators = Indikator::all();
        $pegawais = \App\Models\Pegawai::orderBy('pangkat_golongan', 'desc')->orderBy('nip', 'asc')->get();

        // Filter: Admin sees all, PIC Indikator sees activities under their indicators or where they are ketua tim
        if (!auth()->user()->isAdminOrPimpinan()) {
            $pegawaiId = auth()->user()->pegawai_id;
            if ($pegawaiId) {
                // Show activities where user is either: 
                // 1. Ketua Tim
                // 2. Member (Anggota)
                // 3. PIC of the parent indicator
                $query->where(function($q) use ($pegawaiId) {
                    $q->where('ketua_tim_id', $pegawaiId)
                      ->orWhereHas('anggotas', function($sq) use ($pegawaiId) {
                          $sq->where('pegawai_id', $pegawaiId);
                      })
                      ->orWhereHas('indikator', function($sq) use ($pegawaiId) {
                          $sq->where('pic_id', $pegawaiId);
                      });
                });
                
                // Also filter the indicators dropdown for adding/filtering kegiatans
                $indikators = Indikator::where('pic_id', $pegawaiId)
                    ->orWhereHas('kegiatanMasters', function($q) use ($pegawaiId) {
                        $q->where('ketua_tim_id', $pegawaiId)
                          ->orWhereHas('anggotas', function($sq) use ($pegawaiId) {
                              $sq->where('pegawai_id', $pegawaiId);
                          });
                    })->get();
            } else {
                // If user doesn't have a linked pegawai profile, show nothing
                $query->whereRaw('1 = 0');
                $indikators = collect();
            }
        }

        $kegiatans = $query->get();
        
        return view('admin.kegiatan.index', compact('kegiatans', 'indikators', 'pegawais'));
    }

    public function create()
    {
        $indikators = Indikator::all();
        return view('admin.kegiatan.create', compact('indikators'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->isAnggota()) abort(403, 'Akses ditolak.');
        
        $pegawaiId = auth()->user()->pegawai_id;
        $isAdmin = auth()->user()->isAdminOrPimpinan();

        $validated = $request->validate([
            'indikator_id' => [
                'required',
                'exists:indikators,id',
                function ($attribute, $value, $fail) use ($isAdmin, $pegawaiId) {
                    if (!$isAdmin) {
                        $indikator = Indikator::find($value);
                        if (!$indikator || $indikator->pic_id != $pegawaiId) {
                            $fail('Anda hanya diperbolehkan menambah kegiatan pada indikator di mana Anda adalah PIC.');
                        }
                    }
                },
            ],
            'nama_kegiatan' => 'required|string',
            'tahapan' => 'required|array',
        ]);

        $kegiatan = KegiatanMaster::create([
            'indikator_id' => $validated['indikator_id'],
            'nama_kegiatan' => $validated['nama_kegiatan'],
            'tahapan_json' => $validated['tahapan'],
            'ketua_tim_id' => $request->ketua_tim_id,
        ]);

        if ($request->has('anggotas')) {
            $kegiatan->anggotas()->sync($request->anggotas);
        }

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Kegiatan Master berhasil ditambahkan',
                'data' => $kegiatan
            ]);
        }

        return redirect()->route('kegiatan-master.index')->with('success', 'Kegiatan Master berhasil ditambahkan.');
    }

    public function show(KegiatanMaster $kegiatanMaster)
    {
        return response()->json($kegiatanMaster->load(['indikator', 'anggotas']));
    }

    public function update(Request $request, KegiatanMaster $kegiatanMaster)
    {
        if (auth()->user()->isAnggota()) abort(403, 'Akses ditolak.');
        
        $pegawaiId = auth()->user()->pegawai_id;
        $isAdmin = auth()->user()->isAdminOrPimpinan();

        $validated = $request->validate([
            'indikator_id' => [
                'required',
                'exists:indikators,id',
                function ($attribute, $value, $fail) use ($isAdmin, $pegawaiId) {
                    if (!$isAdmin) {
                        $indikator = Indikator::find($value);
                        if (!$indikator || $indikator->pic_id != $pegawaiId) {
                            $fail('Anda hanya diperbolehkan memperbarui kegiatan pada indikator di mana Anda adalah PIC.');
                        }
                    }
                },
            ],
            'nama_kegiatan' => 'required|string',
            'tahapan' => 'required|array',
        ]);

        $kegiatanMaster->update([
            'indikator_id' => $validated['indikator_id'],
            'nama_kegiatan' => $validated['nama_kegiatan'],
            'tahapan_json' => $validated['tahapan'],
            'ketua_tim_id' => $request->ketua_tim_id,
        ]);

        if ($request->has('anggotas')) {
            $kegiatanMaster->anggotas()->sync($request->anggotas);
        }

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Kegiatan Master berhasil diperbarui',
                'data' => $kegiatanMaster
            ]);
        }

        return redirect()->route('kegiatan-master.index')->with('success', 'Kegiatan Master berhasil diperbarui.');
    }

    public function destroy(KegiatanMaster $kegiatanMaster)
    {
        if (auth()->user()->isAnggota()) abort(403, 'Akses ditolak.');
        
        $kegiatanMaster->delete();
        
        if (request()->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Kegiatan Master berhasil dihapus'
            ]);
        }

        return redirect()->route('kegiatan-master.index')->with('success', 'Kegiatan Master berhasil dihapus.');
    }

    public function import(Request $request)
    {
        if (auth()->user()->isAnggota()) abort(403, 'Akses ditolak.');
        
        $request->validate(['file' => 'required|mimes:xlsx,xls']);
        try {
            Excel::import(new KegiatanMasterImport, $request->file('file'));
            return redirect()->route('kegiatan-master.index')->with('success', 'Data Kegiatan berhasil diimport.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Format file tidak sesuai template! (' . $e->getMessage() . ')');
        }
    }

    public function downloadTemplate()
    {
        $headers = ['kode_indikator', 'nama_kegiatan', 'tahapan'];
        $example = ['1.1.1.1', 'Survei Angkatan Kerja Nasional', 'Persiapan, Pengumpulan Data, Pengolahan, Analisis'];
        
        return Excel::download(new class($headers, $example) implements \Maatwebsite\Excel\Concerns\FromArray {
            protected $headers;
            protected $example;
            public function __construct($headers, $example) { 
                $this->headers = $headers; 
                $this->example = $example; 
            }
            public function array(): array { 
                return [$this->headers, $this->example]; 
            }
        }, 'template_import_kegiatan.xlsx');
    }
    public function syncAnggota(Request $request, KegiatanMaster $kegiatanMaster)
    {
        if (auth()->user()->isAnggota()) abort(403, 'Akses ditolak.');
        
        $request->validate([
            'anggotas' => 'array',
            'anggotas.*' => 'exists:pegawais,id'
        ]);

        $kegiatanMaster->anggotas()->sync($request->anggotas);

        return response()->json([
            'status' => 'success',
            'message' => 'Anggota tim berhasil diperbarui',
            'data' => $kegiatanMaster->load('anggotas')
        ]);
    }
}

