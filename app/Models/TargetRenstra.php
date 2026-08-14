<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TargetRenstra extends Model
{
    use HasFactory;

    protected $fillable = [
        'renstra_id',
        'indikator_id',
        'tahun',
        'target',
    ];

    public function renstra()
    {
        return $this->belongsTo(Renstra::class);
    }

    public function indikator()
    {
        return $this->belongsTo(Indikator::class);
    }
}
