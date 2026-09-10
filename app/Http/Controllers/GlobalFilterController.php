<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GlobalFilterController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'global_tahun' => 'nullable|integer',
            'global_triwulan' => 'nullable|string',
        ]);

        if ($request->has('global_tahun')) {
            session(['global_tahun' => $request->global_tahun]);
        }
        if ($request->has('global_triwulan')) {
            session(['global_triwulan' => $request->global_triwulan]);
        }

        return redirect()->back()->with('success', 'Filter global berhasil diperbarui.');
    }
}
