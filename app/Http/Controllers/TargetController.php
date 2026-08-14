<?php

namespace App\Http\Controllers;

use App\Models\Indikator;
use App\Models\Target;
use Illuminate\Http\Request;

class TargetController extends Controller
{
    public function index()
    {
        $indikators = Indikator::with(['target', 'pkTahunans'])->get();
        return view('target.index', compact('indikators'));
    }

    public function show($id)
    {
        $target = Target::firstOrCreate(['indikator_id' => $id]);
        $target->load(['indikator.pkTahunans']);
        
        $response = $target->toArray();
        $response['discrepancy_q4'] = $target->indikator->discrepancy_q4;
        $response['target_pk_efektif'] = $target->indikator->target_tahunan_efektif;

        return response()->json($response);
    }

    public function update(Request $request, $id)
    {
        $target = Target::firstOrCreate(['indikator_id' => $id]);
        
        // Sanitasi input: ubah koma menjadi titik untuk desimal
        $data = $request->all();
        $fieldsToSanitize = [
            'target_tw1', 'target_tw2', 'target_tw3', 'target_tw4',
            'target_x_tw1', 'target_x_tw2', 'target_x_tw3', 'target_x_tw4',
            'target_y_tw1', 'target_y_tw2', 'target_y_tw3', 'target_y_tw4'
        ];
        foreach($fieldsToSanitize as $field) {
            if (isset($data[$field])) {
                $data[$field] = str_replace(',', '.', $data[$field]);
            }
        }
        $request->merge($data);

        $target->update($request->validate([
            'target_tw1' => 'nullable|numeric',
            'target_tw2' => 'nullable|numeric',
            'target_tw3' => 'nullable|numeric',
            'target_tw4' => 'nullable|numeric',
            'target_x_tw1' => 'nullable|numeric',
            'target_x_tw2' => 'nullable|numeric',
            'target_x_tw3' => 'nullable|numeric',
            'target_x_tw4' => 'nullable|numeric',
            'target_y_tw1' => 'nullable|numeric',
            'target_y_tw2' => 'nullable|numeric',
            'target_y_tw3' => 'nullable|numeric',
            'target_y_tw4' => 'nullable|numeric',
        ]));

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Target berhasil diperbarui',
                'data' => $target
            ]);
        }

        return redirect()->route('target.index')->with('success', 'Target berhasil diperbarui');
    }

    /**
     * Samakan Target TW 4 dengan Target PK Tahunan (target_efektif).
     */
    public function syncQ4($id)
    {
        $indikator = Indikator::with('pkTahunans')->findOrFail($id);
        $target = Target::firstOrCreate(['indikator_id' => $id]);

        $targetPk = $indikator->target_tahunan_efektif;
        $target->update(['target_tw4' => $targetPk]);

        return response()->json([
            'status' => 'success',
            'message' => "Target TW 4 berhasil disamakan dengan Target PK Tahunan ({$targetPk})",
            'target_tw4' => $targetPk
        ]);
    }
}
