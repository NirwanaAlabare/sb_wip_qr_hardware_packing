<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\Reject;
use App\Models\SignalBit\Rework;
use App\Models\SignalBit\MasterPlan;
use Livewire\WithPagination;
use DB;

class ProfileContent extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $masterPlan;
    public $dateFrom;
    public $dateTo;

    public function mount()
    {
        $masterPlan = session()->get('orderInfo');
        $this->masterPlan = $masterPlan ? $masterPlan->id : null;
        $this->dateFrom = $this->dateFrom ? $this->dateFrom : date('Y-m-d');
        $this->dateTo = $this->dateTo ? $this->dateTo : date('Y-m-d');
    }

    public function render()
    {
        $masterPlan = session()->get('orderInfo');
        $this->masterPlan = $masterPlan ? $masterPlan->id : null;

        $totalRftSql = Rft::select('output_rfts_packing.*')->
            leftJoin('master_plan', 'master_plan.id', '=', 'output_rfts_packing.master_plan_id');
            if (Auth::user()->Groupp == 'SEWING') {
                $totalRftSql->where('master_plan.sewing_line', Auth::user()->username);
            }
            if ($this->masterPlan) {
                $totalRftSql->where('master_plan.id', $this->masterPlan);
            }
        $totalRft = $totalRftSql->whereRaw("DATE(output_rfts_packing.created_at) >= '".$this->dateFrom."'")->
            where("status", "NORMAL")->
            whereRaw("DATE(output_rfts_packing.created_at) <= '".$this->dateTo."'")->
            count();

        $totalDefectSql = Defect::select('output_defects_packing.*')->
            leftJoin('master_plan', 'master_plan.id', '=', 'output_defects_packing.master_plan_id')->
            where('output_defects_packing.defect_status', 'defect')->
            where('output_defects_packing.defect_status', 'defect');
            if (Auth::user()->Groupp == 'SEWING') {
                $totalDefectSql->where('master_plan.sewing_line', Auth::user()->username);
            }
            if ($this->masterPlan) {
                $totalDefectSql->where('master_plan.id', $this->masterPlan);
            }
        $totalDefect = $totalDefectSql->whereRaw("DATE(output_defects_packing.created_at) >= '".$this->dateFrom."'")->
            whereRaw("DATE(output_defects_packing.created_at) <= '".$this->dateTo."'")->
            count();

        $totalRejectSql = Reject::select('output_rejects_packing.*')->
            leftJoin('master_plan', 'master_plan.id', '=', 'output_rejects_packing.master_plan_id');
            if (Auth::user()->Groupp == 'SEWING') {
                $totalRejectSql->where('master_plan.sewing_line', Auth::user()->username);
            }
            if ($this->masterPlan) {
                $totalRejectSql->where('master_plan.id', $this->masterPlan);
            }
        $totalReject = $totalRejectSql->whereRaw("DATE(output_rejects_packing.created_at) >= '".$this->dateFrom."'")->
            whereRaw("DATE(output_rejects_packing.created_at) <= '".$this->dateTo."'")->
            count();

        $totalReworkSql = Rework::select('output_reworks_packing.*')->
            leftJoin('output_defects_packing', 'output_defects_packing.id', '=', 'output_reworks_packing.defect_id')->
            leftJoin('master_plan', 'master_plan.id', '=', 'output_defects_packing.master_plan_id')->
            where('master_plan.sewing_line', Auth::user()->username);
            if (Auth::user()->Groupp == 'SEWING') {
                $totalReworkSql->where('master_plan.sewing_line', Auth::user()->username);
            }
            if ($this->masterPlan) {
                $totalReworkSql->where('master_plan.id', $this->masterPlan);
            }
        $totalRework = $totalReworkSql->whereRaw("DATE(output_reworks_packing.created_at) >= '".$this->dateFrom."'")->
            whereRaw("DATE(output_reworks_packing.created_at) <= '".$this->dateTo."'")->
            count();

        return view('livewire.profile-content', [
            'totalRft' => $totalRft,
            'totalDefect' => $totalDefect,
            'totalReject' => $totalReject,
            'totalRework' => $totalRework,
        ]);
    }
}
