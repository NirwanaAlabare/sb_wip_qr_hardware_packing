<?php

namespace App\Models\SignalBit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rft extends Model
{
    use HasFactory;

    protected $connection = 'mysql_sb';

    protected $table = 'output_rfts_packing';

    protected $fillable = [
        'id',
        'master_plan_id',
        'so_det_id',
        'no_cut_size',
        'kode_numbering',
        'status',
        'rework_id',
        'created_at',
        'updated_at',
        'created_by',
    ];

    public function masterPlan()
    {
        return $this->belongsTo(MasterPlan::class, 'master_plan_id', 'id');
    }

    public function rework()
    {
        return $this->hasOne(Rework::class, 'id', 'rework_id');
    }

    public function undo()
    {
        return $this->hasOne(Undo::class, 'output_rft_id', 'id');
    }
}
