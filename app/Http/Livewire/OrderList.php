<?php

namespace App\Http\Livewire;

use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\UserPassword;
use App\Models\SignalBit\TemporaryOutput;
use Illuminate\Support\Facades\Auth;
use Illuminate\Session\SessionManager;
use Livewire\Component;
use DB;

class OrderList extends Component
{
    public $orders;
    public $search = '';
    public $date = '';
    public $baseUrl = '';

    public $orderFilters = '';
    public $filterLine = '';
    public $filterBuyer = '';
    public $filterWs = '';
    public $filterProductType = '';
    public $filterStyle = '';
    public $filterDate = '';

    public $listeners = ['setDate' => 'setDate'];

    public $temporaryOutput;

    public function mount(SessionManager $session)
    {
        $session->forget('orderInfo');
        $session->forget('orderWsDetails');

        $this->date = date('Y-m-d');
        $this->baseUrl = url('/');

        $this->filterLine = str_replace("_", " ", Auth::user()->username);
        // $this->lines = UserPassword::where('Groupp', 'SEWING')->get();
        // $this->buyer = DB::table('mastersupplier')->where('tipe_sup', 'C')->get();
    }

    public function clearFilter()
    {
        $this->filterLine = str_replace("_", " ", Auth::user()->username);
        $this->filterBuyer = '';
        $this->filterWs = '';
        $this->filterProductType = '';
        $this->filterStyle = '';

        $this->emit('clearFilterInput');
    }

    public function preSubmitFilter()
    {
        $this->emit('showFilterModal');
    }

    public function submitFilter() {
        $this->orders = $this->orders->filter(function ($item) {
            return $item['sewing_line'] == $this->filterLine && $item['ws_number'] == $this->filterWs && $item['buyer_name'] == $this->filterBuyer && $item['product_type'] == $this->filterProductType && $item['style_name'] == $this->filterStyle;
        })->values();
    }

    public function setDate($date)
    {
        $this->date = $date;

        $this->orders = $this->orders->filter(function ($item) {
            return $item['plan_date'] == $this->date;
        })->values();
    }

    public function render()
    {
        $masterPlanBefore = MasterPlan::selectRaw("max(id) as id")->leftJoin(DB::raw("(SELECT master_plan_id, COUNT(id) total FROM output_defects_packing WHERE created_by = '".Auth::user()->username."' and defect_status = 'defect' and kode_numbering is not null GROUP BY master_plan_id) defects"), "defects.master_plan_id", "=", "master_plan.id")->where("sewing_line", strtoupper(Auth::user()->username))->where("master_plan.cancel", "N")->where("tgl_plan", "<", $this->date)->groupBy("master_plan.id_ws", "master_plan.color")->orderBy("tgl_plan", "desc")->limit(3)->get();

        $additionalQuery = "";
        if ($masterPlanBefore) {
            $masterPlanBeforeIds = implode("' , '", $masterPlanBefore->pluck("id")->toArray());

            $additionalQuery .= "OR master_plan.id IN ('".$masterPlanBeforeIds."')";
        }

        // With Today Output
        $masterPlanWithOutput = MasterPlan::selectRaw("MAX(master_plan.id) id")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("mastersupplier", "mastersupplier.Id_Supplier", "=", "act_costing.id_buyer")->
            leftJoin(DB::raw("(select master_plan_id, count(output_rfts_packing.id) as total from output_rfts_packing where created_by = '".Auth::user()->username."' and updated_at BETWEEN '".$this->date." 00:00:00' and '".$this->date." 23:59:59' group by master_plan_id) as output"), "output.master_plan_id", "=", "master_plan.id")->
            where("master_plan.cancel", "N")->
            where("sewing_line", strtoupper(Auth::user()->username))->
            where("tgl_plan", "<", $this->date)->
            where("output.total", ">", 0)->
            groupBy("master_plan.sewing_line", "master_plan.id_ws", "master_plan.color", "master_plan.tgl_plan")->
            orderBy("tgl_plan", "desc")->
            orderBy("sewing_line", "asc")->
            get();

        if ($masterPlanWithOutput) {
            $masterPlanWithOutputIds = implode("' , '", $masterPlanWithOutput->pluck("id")->toArray());

            $additionalQuery .= " OR master_plan.id IN ('".$masterPlanWithOutputIds."') ";
        }

        $this->orderFilters = DB::table('master_plan')
            ->selectRaw("
                master_plan.id_ws as id_ws,
                master_plan.tgl_plan as plan_date,
                REPLACE(master_plan.sewing_line, '_', ' ') as sewing_line,
                act_costing.kpno as ws_number,
                mastersupplier.supplier as buyer_name,
                act_costing.styleno as style_name,
                CONCAT(masterproduct.product_group, ' - ', masterproduct.product_item) as product_type
            ")
            ->leftJoin('act_costing', 'act_costing.id', '=', 'master_plan.id_ws')
            ->leftJoin('so', 'so.id_cost', '=', 'act_costing.id')
            ->leftJoin('so_det', 'so_det.id_so', '=', 'so.id')
            ->leftJoin('mastersupplier', 'mastersupplier.id_supplier', '=', 'act_costing.id_buyer')
            ->leftJoin('master_size_new', 'master_size_new.size', '=', 'so_det.size')
            ->leftJoin('masterproduct', 'masterproduct.id', '=', 'act_costing.id_product')
            ->where('so_det.cancel', 'N')
            ->where('master_plan.cancel', 'N')
            ->whereRaw('master_plan.tgl_plan = "'.$this->date.'" '.$additionalQuery)
            ->orderBy('master_plan.tgl_plan', 'desc')
            ->orderBy('master_plan.sewing_line', 'asc')
            ->orderBy('mastersupplier.supplier', 'asc')
            ->orderBy('act_costing.kpno', 'asc')
            ->orderBy('product_type', 'asc')
            ->orderBy('act_costing.styleno', 'asc')
            ->get();

        $this->orders = DB::table('master_plan')
            ->selectRaw("
                MIN(master_plan.id) as id,
                master_plan.id_ws as id_ws,
                master_plan.tgl_plan as plan_date,
                REPLACE(master_plan.sewing_line, '_', ' ') as sewing_line,
                act_costing.kpno as ws_number,
                mastersupplier.supplier as buyer_name,
                act_costing.styleno as style_name,
                COALESCE(output.progress, 0) as progress,
                COALESCE(output_endline.progress, 0) as target,
                COALESCE(defects.total, 0) as total_defect,
                CONCAT(masterproduct.product_group, ' - ', masterproduct.product_item) as product_type
            ")
            ->leftJoin('act_costing', 'act_costing.id', '=', 'master_plan.id_ws')
            ->leftJoin('so', 'so.id_cost', '=', 'act_costing.id')
            ->join('so_det', function ($join) {
                $join->on('so_det.id_so', "=", "so.id");
                $join->on('so_det.color', "=", "master_plan.color");
            })
            ->leftJoin('mastersupplier', 'mastersupplier.id_supplier', '=', 'act_costing.id_buyer')
            ->leftJoin('master_size_new', 'master_size_new.size', '=', 'so_det.size')
            ->leftJoin('masterproduct', 'masterproduct.id', '=', 'act_costing.id_product')
            ->leftJoin(DB::raw("(SELECT master_plan.id_ws, master_plan.tgl_plan, SUM(defects.total) total FROM master_plan left join (select master_plan_id, COUNT(id) total FROM output_defects_packing where created_by = '".Auth::user()->username."' and defect_status = 'defect' GROUP BY master_plan_id) defects on defects.master_plan_id = master_plan.id WHERE master_plan.sewing_line = '".strtoupper(Auth::user()->username)."' AND cancel = 'N' GROUP BY master_plan.id_ws, master_plan.tgl_plan) defects"), function ($join) {
                $join->on("defects.id_ws", "=", "master_plan.id_ws");
                $join->on("defects.tgl_plan", "=", "master_plan.tgl_plan");
            })
            ->leftJoin(
                DB::raw("
                    (
                        select
                            master_plan.id as master_plan_id,
                            master_plan.tgl_plan,
                            master_plan.id_ws,
                            master_plan.sewing_line,
                            count(output_rfts_packing.id) as progress
                        from
                            master_plan
                        left join
                            output_rfts_packing on output_rfts_packing.master_plan_id = master_plan.id
                        where
                            (master_plan.sewing_line = '".strtoupper(Auth::user()->username)."' OR master_plan.sewing_line = '".str_replace(" ", "_", strtoupper($this->filterLine))."') AND
                            DATE(output_rfts_packing.updated_at) = '".$this->date."' AND
                            (master_plan.tgl_plan = '".$this->date."' $additionalQuery) AND
                            master_plan.cancel = 'N'
                        group by
                            master_plan.sewing_line,
                            master_plan.id_ws,
                            master_plan.tgl_plan
                    ) output
                "),
                function ($join) {
                    $join->on("output.sewing_line", "=", "master_plan.sewing_line");
                    $join->on("output.id_ws", "=", "master_plan.id_ws");
                    $join->on("output.tgl_plan", "=", "master_plan.tgl_plan");
                }
            )
            ->leftJoin(
                DB::raw("
                    (
                        select
                            master_plan.id as master_plan_id,
                            master_plan.tgl_plan,
                            master_plan.id_ws,
                            master_plan.sewing_line,
                            count(output_rfts.id) as progress
                        from
                            master_plan
                        left join
                            output_rfts on output_rfts.master_plan_id = master_plan.id
                        where
                            (master_plan.sewing_line = '".strtoupper(Auth::user()->username)."' OR master_plan.sewing_line = '".str_replace(" ", "_", strtoupper($this->filterLine))."') AND
                            (master_plan.tgl_plan = '".$this->date."' $additionalQuery) AND
                            master_plan.cancel = 'N'
                        group by
                            master_plan.sewing_line,
                            master_plan.id_ws,
                            master_plan.tgl_plan
                    ) output_endline
                "),
                function ($join) {
                    $join->on("output_endline.sewing_line", "=", "master_plan.sewing_line");
                    $join->on("output_endline.id_ws", "=", "master_plan.id_ws");
                    $join->on("output_endline.tgl_plan", "=", "master_plan.tgl_plan");
                }
            )
            ->where('so_det.cancel', 'N')
            ->where('master_plan.cancel', 'N')
            ->whereRaw("
                (master_plan.sewing_line = '".strtoupper(Auth::user()->username)."' OR master_plan.sewing_line = '".str_replace(" ", "_", strtoupper($this->filterLine))."') AND
                master_plan.tgl_plan = '".$this->date."'
                ".$additionalQuery."
            ")
            ->whereRaw("
                (
                    act_costing.kpno LIKE '%".$this->search."%'
                    OR
                    mastersupplier.supplier LIKE '%".$this->search."%'
                    OR
                    act_costing.styleno LIKE '%".$this->search."%'
                    OR
                    master_plan.color LIKE '%".$this->search."%'
                )
            ")
            ->groupBy(
                'master_plan.id_ws',
                'master_plan.tgl_plan',
                'act_costing.kpno',
                'mastersupplier.supplier',
                'act_costing.styleno',
                'product_type',
                'output.progress',
                'output_endline.progress',
                'so.id'
            )
            ->orderBy('master_plan.tgl_plan', 'desc')
            ->orderBy('defects.total', 'desc')
            ->orderBy('output.progress', 'desc')
            ->orderBy('master_plan.sewing_line', 'asc')
            ->orderBy('master_plan.id_ws', 'desc')
            ->get();

        $this->temporaryOutput = TemporaryOutput::where("line_id", Auth::user()->line_id)->
            whereRaw('(DATE(temporary_output_packing.created_at) = "'.$this->date.'" OR DATE(temporary_output_packing.updated_at) = "'.$this->date.'")')->
            where('tipe_output', 'rft')->
            orWhere('tipe_output', 'rework')->
            count();

        return view('livewire.order-list');
    }
}
