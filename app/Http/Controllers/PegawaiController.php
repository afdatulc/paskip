<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Imports\PegawaiImport;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class PegawaiController extends Controller
{
    public function index()
    {
        $pegawais = Pegawai::with('user')
            ->orderBy('pangkat_golongan', 'desc')
            ->orderBy('nip', 'asc')
            ->get();
        return view('pegawai.index', compact('pegawais'));
    }

    public function create()
    {
        return view('pegawai.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nip' => 'required|string|unique:pegawais,nip',
            'nama' => 'required|string',
            'email_bps' => 'nullable|email|unique:pegawais,email_bps',
            'jabatan' => 'nullable|string',
            'pangkat_golongan' => 'nullable|string',
            'unit_kerja' => 'nullable|string',
            'status' => 'required|in:PNS,PPPK,Outsourcing,Lainnya',
            'seksi' => 'required|in:Sosial,Produksi,Distribusi,Nerwilis,IPDS,Umum,Lainnya',
            'no_hp' => 'nullable|string',
        ]);

        $pegawai = Pegawai::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data pegawai berhasil ditambahkan',
                'data' => $pegawai
            ]);
        }

        return redirect()->route('pegawai.index')->with('success', 'Data pegawai berhasil ditambahkan.');
    }

    public function show(Pegawai $pegawai)
    {
        return response()->json($pegawai);
    }

    public function update(Request $request, Pegawai $pegawai)
    {
        $validated = $request->validate([
            'nip' => 'required|string|unique:pegawais,nip,' . $pegawai->id,
            'nama' => 'required|string',
            'email_bps' => 'nullable|email|unique:pegawais,email_bps,' . $pegawai->id,
            'jabatan' => 'nullable|string',
            'pangkat_golongan' => 'nullable|string',
            'unit_kerja' => 'nullable|string',
            'status' => 'required|in:PNS,PPPK,Outsourcing,Lainnya',
            'seksi' => 'required|in:Sosial,Produksi,Distribusi,Nerwilis,IPDS,Umum,Lainnya',
            'no_hp' => 'nullable|string',
        ]);

        $pegawai->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data pegawai berhasil diperbarui',
                'data' => $pegawai
            ]);
        }

        return redirect()->route('pegawai.index')->with('success', 'Data pegawai berhasil diperbarui.');
    }

    public function activateAccount($id)
    {
        $pegawai = Pegawai::findOrFail($id);
        
        if (!$pegawai->email_bps) {
            if (request()->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Pegawai tidak memiliki email BPS. Harap update data email terlebih dahulu.'
                ], 422);
            }
            return redirect()->back()->with('error', 'Pegawai tidak memiliki email BPS. Harap update data email terlebih dahulu.');
        }

        // Menentukan role secara otomatis
        $role = 'anggota';
        if (str_contains(strtolower($pegawai->jabatan), 'kepala bps')) {
            $role = 'pimpinan';
        } elseif (\App\Models\Indikator::where('pic_id', $pegawai->id)->exists()) {
            $role = 'pic';
        }

        // Set default password menjadi 'password'
        $plainPassword = 'password';

        $user = \App\Models\User::updateOrCreate(
            ['email' => $pegawai->email_bps],
            [
                'name'       => $pegawai->nama,
                'password'   => \Illuminate\Support\Facades\Hash::make($plainPassword),
                'role'       => $role,
                'pegawai_id' => $pegawai->id
            ]
        );

        $message = "Akun untuk {$pegawai->nama} berhasil diaktifkan. Password default adalah: {$plainPassword}";

        if (request()->ajax()) {
            return response()->json([
                'status'   => 'success',
                'message'  => $message,
                'password' => $plainPassword, 
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    public function destroy(Pegawai $pegawai)
    {
        $pegawai->delete();

        if (request()->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Data pegawai berhasil dihapus'
            ]);
        }

        return redirect()->route('pegawai.index')->with('success', 'Data pegawai berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls']);
        try {
            Excel::import(new PegawaiImport, $request->file('file'));
            return redirect()->route('pegawai.index')->with('success', 'Data Pegawai berhasil diimport.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Format file tidak sesuai template! (' . $e->getMessage() . ')');
        }
    }

    public function syncApi()
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(30)->get('https://ipin.bps-tapin.com/api/employees/full');
            if (!$response->successful()) {
                if (request()->ajax()) {
                    return response()->json(['status' => 'error', 'message' => 'Gagal terhubung ke API Pegawai'], 500);
                }
                return redirect()->back()->with('error', 'Gagal terhubung ke API Pegawai');
            }

            $employees = $response->json('data') ?? [];
            $countUpdated = 0;
            $countCreated = 0;

            foreach ($employees as $emp) {
                $nip = $emp['nip_pns'] ?? null;
                $email = $emp['email_bps'] ?? null;

                if (!$nip && !$email) continue;

                $pegawai = null;
                if ($nip) {
                    $pegawai = Pegawai::where('nip', $nip)->first();
                }
                if (!$pegawai && $email) {
                    $pegawai = Pegawai::where('email_bps', $email)->first();
                }

                $namaLengkap = trim($emp['nama_lengkap'] ?? '');
                $gelarBelakang = trim($emp['gelar_belakang'] ?? '');
                $nama = $namaLengkap . ($gelarBelakang ? ', ' . $gelarBelakang : '');
                
                $jabFungsional = trim($emp['jabatan_fungsional_nama'] ?? '');
                $jabatanRaw = trim($emp['jabatan'] ?? '');
                $jabatan = trim($jabFungsional . ' ' . $jabatanRaw);
                
                $unit_kerja = $emp['satker'] ?? null;
                $no_hp = $emp['no_hp'] ?? null;
                $pangkat_golongan = $emp['pangkat_golongan'] ?? null;

                if ($pegawai) {
                    $pegawai->update([
                        'nama' => $nama,
                        'nip' => $nip ?: $pegawai->nip,
                        'email_bps' => $email ?: $pegawai->email_bps,
                        'jabatan' => $jabatan,
                        'pangkat_golongan' => $pangkat_golongan,
                        'unit_kerja' => $unit_kerja,
                        'no_hp' => $no_hp,
                    ]);
                    $countUpdated++;
                } else {
                    Pegawai::create([
                        'nip' => $nip ?? '',
                        'nama' => $nama,
                        'email_bps' => $email,
                        'jabatan' => $jabatan,
                        'pangkat_golongan' => $pangkat_golongan,
                        'unit_kerja' => $unit_kerja,
                        'no_hp' => $no_hp,
                        'status' => 'PNS',
                        'seksi' => 'Lainnya',
                    ]);
                    $countCreated++;
                }
            }

            if (request()->ajax()) {
                return response()->json(['status' => 'success', 'message' => "Sync berhasil. $countCreated data ditambahkan, $countUpdated data diperbarui."]);
            }
            return redirect()->back()->with('success', "Sync berhasil. $countCreated data ditambahkan, $countUpdated data diperbarui.");
        } catch (\Throwable $e) {
            if (request()->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $headers = ['nip', 'nama', 'email_bps', 'no_hp', 'jabatan', 'unit_kerja', 'status', 'seksi'];
        return Excel::download(new class($headers) implements \Maatwebsite\Excel\Concerns\FromArray {
            protected $headers;
            public function __construct($headers) { $this->headers = $headers; }
            public function array(): array { return [$this->headers]; }
        }, 'template_import_pegawai.xlsx');
    }
}
