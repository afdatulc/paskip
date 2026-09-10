<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\KendalaRtl;

$rtls = KendalaRtl::all();
$grouped = $rtls->groupBy(function($item) {
    return $item->indikator_id . '_' . $item->tahun . '_' . $item->triwulan;
});

foreach($grouped as $group) {
    if($group->count() > 1) {
        $first = $group->first();
        foreach($group->slice(1) as $other) {
            $first->kendala .= "\n- " . $other->kendala;
            $first->solusi .= "\n- " . $other->solusi;
            $first->rtl .= "\n- " . $other->rtl;
            foreach($other->executions as $exec) {
                $exec->kendala_rtl_id = $first->id;
                $exec->save();
            }
            $other->delete();
        }
        $first->save();
        echo "Merged " . $group->count() . " records for Indikator " . $first->indikator_id . "\n";
    }
}
echo "Done.\n";
