<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Session\SessionManager;
use App\Models\SignalBit\DefectType;
use App\Models\SignalBit\DefectArea;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\Reject as RejectModel;
use App\Models\SignalBit\MasterPlan;
use Carbon\Carbon;
use DB;

class ReturnReject extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $orderInfo;
    public $orderWsDetailSizes;
    public $output;
    public $outputInput;
    public $sizeInput;
    public $sizeInputText;

    public $searchDefect;
    public $searchReject;
    public $defectImage;
    public $defectPositionX;
    public $defectPositionY;
    public $allDefectListFilter;
    public $allDefectImage;
    public $allDefectPosition;
    public $massQty;
    public $massSize;
    public $massDefectType;
    public $massDefectTypeName;
    public $massDefectArea;
    public $massDefectAreaName;
    public $massSelectedDefect;
    public $info;

    public $defectTypes;
    public $defectAreas;
    public $rejectType;
    public $rejectArea;
    public $rejectAreaPositionX;
    public $rejectAreaPositionY;

    public $rftsReturnId;
    public $defectReturnId;
    public $masterPlanId;
    public $soDetId;
    public $kode_qr;
    public $po;
    public $worksheet_style;
    public $color;
    public $size;
    public $packing_line;
    public $date = '';

    protected $rules = [
        'outputInput' => 'required|numeric|min:1',

        'rejectType' => 'required',
        'rejectArea' => 'required',
        'rejectAreaPositionX' => 'required',
        'rejectAreaPositionY' => 'required',
    ];

    protected $messages = [
        'outputInput.required' => 'Harap tentukan kuantitas output.',
        'outputInput.numeric' => 'Harap isi kuantitas output dengan angka.',
        'outputInput.min' => 'Kuantitas output tidak bisa kurang dari 1.',

        'rejectType.required' => 'Harap tentukan jenis reject.',
        'rejectArea.required' => 'Harap tentukan area reject.',
        'rejectAreaPositionX.required' => "Harap tentukan posisi reject area dengan mengklik tombol 'gambar' di samping 'select product type'.",
        'rejectAreaPositionY.required' => "Harap tentukan posisi reject area dengan mengklik tombol 'gambar' di samping 'select product type'.",
    ];

    protected $listeners = [
        'updateWsDetailSizes' => 'updateWsDetailSizes',
        'updateOutputReject' => 'updateOutput',

        'submitReject' => 'submitReject',
        'submitAllReject' => 'submitAllReject',
        'cancelReject' => 'cancelReject',
        'hideDefectAreaImageClear' => 'hideDefectAreaImage',
        'updateWsDetailSizes' => 'updateWsDetailSizes',

        'setRejectAreaPosition' => 'setRejectAreaPosition',
        'clearInput' => 'clearInput',
    ];

    public function openDefectModal($rftsReturnId, $defectReturnId, $masterPlanId, $soDetId)
    {
        $this->reset([
            'rftsReturnId',
            'defectReturnId',
            'masterPlanId',
            'soDetId',
            'rejectType',
            'rejectArea',
            'rejectAreaPositionX',
            'rejectAreaPositionY',
        ]);
        
        $this->rftsReturnId = $rftsReturnId;
        $this->defectReturnId = $defectReturnId;
        $this->masterPlanId = $masterPlanId;
        $this->soDetId = $soDetId;

        $data = DB::table('output_defect_packing_po_return')
            ->where('id', $defectReturnId)
            ->first();

        if ($data) {
            $this->rejectType = $data->defect_type_id;
            $this->rejectArea = $data->defect_area_id;
            $this->rejectAreaPositionX = $data->defect_area_x;
            $this->rejectAreaPositionY = $data->defect_area_y;
        }

        // $this->emit('showModal', 'reject');

        // $this->emit('setRejectData', [
        //     'type' => $this->rejectType,
        //     'area' => $this->rejectArea,
        //     'x'    => $this->rejectAreaPositionX,
        //     'y'    => $this->rejectAreaPositionY,
        // ]);

        $this->submitInput(app(SessionManager::class));
    }

    public function mount(SessionManager $session)
    {
        $this->outputInput = 1;
        $this->sizeInput = null;
        $this->sizeInputText = null;

        $this->rejectType = null;
        $this->rejectArea = null;
        $this->rejectAreaPositionX = null;
        $this->rejectAreaPositionY = null;

        $this->date = date('Y-m-d');
    }

    public function loadRejectPage()
    {
        $this->emit('loadRejectPageJs');
    }

    public function updateWsDetailSizes()
    {
        $this->outputInput = 1;
        $this->sizeInput = null;
        $this->sizeInputText = '';

        $this->orderInfo = session()->get('orderInfo', $this->orderInfo);
        $this->orderWsDetailSizes = session()->get('orderWsDetailSizes', $this->orderWsDetailSizes);
    }

    public function updateOutput()
    {
        $this->output = collect(DB::select("select output_rejects_packing.*, so_det.size, COUNT(output_rejects_packing.id) output from `output_rejects_packing` left join `so_det` on `so_det`.`id` = `output_rejects_packing`.`so_det_id` where `master_plan_id` = '".$this->orderInfo->id."' and `status` = 'NORMAL' group by so_det.id"));
    }

    public function clearInput()
    {
        $this->outputInput = 1;
        $this->sizeInput = null;
        $this->sizeInputText = '';
    }

    public function outputIncrement()
    {
        $this->outputInput++;
    }

    public function outputDecrement()
    {
        if (($this->outputInput-1) < 1) {
            $this->emit('alert', 'warning', "Kuantitas output tidak bisa kurang dari 1.");
        } else {
            $this->outputInput--;
        }
    }

    public function setSizeInput($size, $sizeText)
    {
        $this->sizeInput = $size;
        $this->sizeInputText = $sizeText;
    }

    public function selectRejectAreaPosition()
    {
        $masterPlan = MasterPlan::select('gambar')->find($this->masterPlanId);

        if ($masterPlan) {
            $this->emit(
                'showSelectRejectArea',
                $masterPlan->gambar,
                $this->rejectAreaPositionX,
                $this->rejectAreaPositionY,
            );
        } else {
            $this->emit('alert', 'error', 'Harap pilih tipe produk terlebih dahulu');
        }
    }

    public function setRejectAreaPosition($x, $y)
    {
        $this->rejectAreaPositionX = $x;
        $this->rejectAreaPositionY = $y;
    }

    public function submitInput(SessionManager $session)
    {
        $validatedData = $this->validate();

        $insertData = [];
        for ($i = 0; $i < $this->outputInput; $i++)
        {
            array_push($insertData, [
                'output_rfts_packing_po_return_id' => $this->rftsReturnId,
                'defect_return_id' => $this->defectReturnId,
                'so_det_id' => $this->soDetId,
                'master_plan_id' => $this->masterPlanId,
                'kode_numbering' => $this->kode_qr,
                'status' => 'NORMAL',
                'reject_type_id' => $this->rejectType,
                'reject_area_id' => $this->rejectArea,
                'reject_area_x' => $this->rejectAreaPositionX,
                'reject_area_y' => $this->rejectAreaPositionY,
                'reject_status' => $this->defectReturnId ? 'defect' : 'mati',
                'created_by' => Auth::user()->username,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }

        $insertReject = DB::table('output_reject_packing_po_return')->insert($insertData);

        if ($insertReject) {
            if($this->defectReturnId){
                DB::table('output_defect_packing_po_return')->where('id', $this->defectReturnId)
                ->update([
                    'defect_status'   => 'rejected',
                    'updated_at' => Carbon::now(),
                ]);

                DB::table('output_rfts_packing_po_return')->where('id', $this->rftsReturnId)
                ->update([
                    'status'     => 'reject',
                    'updated_at' => Carbon::now(),
                ]);
            }else{
                DB::table('output_rfts_packing_po_return')->where('id', $this->rftsReturnId)
                ->update([
                    'status'     => 'reject',
                    'updated_at' => Carbon::now(),
                ]);
            }

            $type = DefectType::select('defect_type')->find($this->rejectType);
            $area = DefectArea::select('defect_area')->find($this->rejectArea);
            $getSize = DB::table('so_det')
                ->select('id', 'size')
                ->where('id', $this->soDetId)
                ->first();

            $this->emit('alert', 'success', $this->outputInput." REJECT output berukuran ".$getSize->size." dengan jenis : ".$type->defect_type." dan area : ".$area->defect_area." berhasil terekam.");
            $this->emit('hideModal', 'reject');

            $this->reset([
                'masterPlanId',
                'soDetId',
                'kode_qr',
                'po',
                'worksheet_style',
                'color',
                'size',
                'packing_line',
                'rejectType',
                'rejectArea',
                'rejectAreaPositionX',
                'rejectAreaPositionY',
            ]);
            $this->emit('clearSelectRejectAreaPoint');
            $this->emit('hideModal', 'reject');
            $this->outputInput = 1;
            $this->soDetId = '';
        } else {
            $this->emit('alert', 'error', "Terjadi kesalahan. Output tidak berhasil direkam.");
        }
    }

    public function closeInfo()
    {
        $this->info = false;
    }

    public function setDefectAreaPosition($x, $y)
    {
        $this->defectPositionX = $x;
        $this->defectPositionY = $y;
    }

    public function showDefectAreaImage($defectImage, $x, $y)
    {
        $this->defectImage = $defectImage;
        $this->defectPositionX = $x;
        $this->defectPositionY = $y;

        $this->emit('showDefectAreaImage', $this->defectImage, $this->defectPositionX, $this->defectPositionY);
    }

    public function hideDefectAreaImage()
    {
        $this->defectImage = null;
        $this->defectPositionX = null;
        $this->defectPositionY = null;
    }

    public function updatingSearchDefect()
    {
        $this->resetPage('defectsPage');
    }

    public function updatingSearchReject()
    {
        $this->resetPage('rejectsPage');
    }

    public function preSubmitMassReject($defectType, $defectArea, $defectTypeName, $defectAreaName) {
        $this->massQty = 1;
        $this->massSize = '';
        $this->massDefectType = $defectType;
        $this->massDefectTypeName = $defectTypeName;
        $this->massDefectArea = $defectArea;
        $this->massDefectAreaName = $defectAreaName;

        $this->emit('showModal', 'massReject');
    }

    public function submitMassReject() {
        $availableReject = 0;
        $externalReject = 0;

        $selectedDefect = Defect::selectRaw('output_defects_packing.id id, output_defects_packing.master_plan_id master_plan_id, output_defects_packing.so_det_id so_det_id, output_defects_packing.kode_numbering, output_defects_packing.no_cut_size, output_defects_packing.defect_type_id, output_defects_packing.defect_area_id, output_defects_packing.defect_area_x, output_defects_packing.defect_area_y, output_defect_types.allocation, output_defect_in_out.status in_out_status')->
            leftJoin('so_det', 'so_det.id', '=', 'output_defects_packing.so_det_id')->
            leftJoin('output_defect_in_out', 'output_defect_in_out.defect_id', '=', 'output_defects_packing.id')->
            leftJoin('output_defect_types', 'output_defect_types.id', '=', 'output_defects_packing.defect_type_id')->
            leftJoin('output_defect_areas', 'output_defect_areas.id', '=', 'output_defects_packing.defect_area_id')->
            where('output_defects_packing.defect_status', 'defect')->
            where('output_defects_packing.master_plan_id', $this->orderInfo->id)->
            where('output_defects_packing.defect_type_id', $this->massDefectType)->
            where('output_defects_packing.defect_area_id', $this->massDefectArea)->
            where('output_defects_packing.so_det_id', $this->massSize)->
            whereNull('output_defects_packing.kode_numbering')->
            take($this->massQty)->get();

        if ($selectedDefect->count() > 0) {
            $defectIds = [];
            foreach ($selectedDefect as $defect) {
                if ($defect->in_out_status != "defect") {
                    // create reject
                    $createReject = RejectModel::create([
                        "master_plan_id" => $defect->master_plan_id,
                        "so_det_id" => $defect->so_det_id,
                        "defect_id" => $defect->id,
                        "status" => "NORMAL",
                        "kode_numbering" => $defect->kode_numbering,
                        "no_cut_size" => $defect->no_cut_size,
                        "reject_status" => 'defect',
                        'reject_type_id' => $defect->defect_type_id,
                        'reject_area_id' => $defect->defect_area_id,
                        'reject_area_x' => $defect->defect_area_x,
                        'reject_area_y' => $defect->defect_area_y,
                        'created_by' => Auth::user()->username
                    ]);

                    // add defect id array
                    array_push($defectIds, $defect->id);

                    $availableReject += 1;
                } else {
                    $externalReject += 1;
                }
            }
            // update defect
            $defectSql = Defect::whereIn('id', $defectIds)->update([
                "defect_status" => "rejected"
            ]);

            if ($availableReject > 0) {
                $this->emit('alert', 'success', "DEFECT dengan Ukuran : ".$selectedDefect[0]->size.", Tipe : ".$this->massDefectTypeName." dan Area : ".$this->massDefectAreaName." berhasil di REJECT sebanyak ".$selectedDefect->count()." kali.");

                $this->emit('hideModal', 'massReject');
            } else {
                $this->emit('alert', 'error', "Terjadi kesalahan. DEFECT dengan Ukuran : ".$selectedDefect[0]->size.", Tipe : ".$this->massDefectTypeName." dan Area : ".$this->massDefectAreaName." tidak berhasil di REJECT.");
            }

            if ($externalReject > 0) {
                $this->emit('alert', 'warning', $externalReject." DEFECT masih ada yang di proses MENDING/SPOTCLEANING.");
            }
        } else {
            $this->emit('alert', 'warning', "Data tidak ditemukan.");
        }
    }

    public function submitReject($defectId) {
        $externalReject = 0;

        $thisDefectReject = RejectModel::where('defect_id', $defectId)->count();

        if ($thisDefectReject < 1) {
            // remove from defect
            $defect = Defect::where('id', $defectId);
            $getDefect = Defect::selectRaw('output_defects_packing.*, output_defect_in_out.status')->leftJoin('output_defect_in_out', 'output_defect_in_out.defect_id', '=', 'output_defects_packing.id')->where('output_defects_packing.id', $defectId)->first();

            if ($getDefect->status != 'defect') {
                $updateDefect = $defect->update([
                    "defect_status" => "rejected"
                ]);

                // add to reject
                $createReject = RejectModel::create([
                    "master_plan_id" => $getDefect->master_plan_id,
                    "so_det_id" => $getDefect->so_det_id,
                    "defect_id" => $defectId,
                    "kode_numbering" => $getDefect->kode_numbering,
                    "no_cut_size" => $getDefect->no_cut_size,
                    'created_by' => Auth::user()->username,
                    "status" => "NORMAL",
                    "reject_status" => 'defect',
                    'reject_type_id' => $getDefect->defect_type_id,
                    'reject_area_id' => $getDefect->defect_area_id,
                    'reject_area_x' => $getDefect->defect_area_x,
                    'reject_area_y' => $getDefect->defect_area_y
                ]);

                if ($createReject && $updateDefect) {
                    $this->emit('alert', 'success', "DEFECT dengan ID : ".$defectId." berhasil di REJECT.");

                    // $this->emit('triggerDashboard', Auth::user()->username, Carbon::now()->format('Y-m-d'));
                } else {
                    $this->emit('alert', 'error', "Terjadi kesalahan. DEFECT dengan ID : ".$defectId." tidak berhasil di REJECT.");
                }
            } else {
                $this->emit('alert', 'error', "DEFECT ini masih di proses MENDING/SPOTCLEANING. DEFECT dengan ID : ".$defectId." tidak berhasil di REJECT.");
            }
        } else {
            $this->emit('alert', 'warning', "Pencegahan data redundant. DEFECT dengan ID : ".$defectId." sudah ada di REJECT.");
        }
    }

    public function render(SessionManager $session)
    {
        $this->emit('loadRejectPageJs');

        // Defect types
        $this->defectTypes = DefectType::leftJoin(DB::raw("(select defect_type_id, count(id) total_defect from output_defects where updated_at between '".date("Y-m-d", strtotime(date("Y-m-d").' -10 days'))." 00:00:00' and '".date("Y-m-d")." 23:59:59' group by defect_type_id) as defects"), "defects.defect_type_id", "=", "output_defect_types.id")->whereRaw("(hidden IS NULL OR hidden != 'Y')")->orderBy('defect_type')->get();

        // Defect areas
        $this->defectAreas = DefectArea::leftJoin(DB::raw("(select defect_area_id, count(id) total_defect from output_defects where updated_at between '".date("Y-m-d", strtotime(date("Y-m-d").' -10 days'))." 00:00:00' and '".date("Y-m-d")." 23:59:59' group by defect_area_id) as defects"), "defects.defect_area_id", "=", "output_defect_areas.id")->whereRaw("(hidden IS NULL OR hidden != 'Y')")->orderBy('defect_area')->get();

        $query = DB::table('output_rfts_packing_po_return')
            ->selectRaw("
                id,
                output_rfts_packing_po_id,
                master_plan_id,
                so_det_id,
                DATE_FORMAT(updated_at, '%d-%m-%Y') AS waktu,
                updated_at,
                po,
                line_qc_finishing,
                CONCAT(kpno, ' - ', style, ' - ', color) AS masterplan,
                size
            ")
            ->where('status', 'rft')
            ->where('line_qc_finishing', auth()->user()->username)
            ->orderBy('id', 'DESC');

        $queryDefect = DB::table('output_defect_packing_po_return')
            ->selectRaw("
                output_defect_packing_po_return.id,
                output_rfts_packing_po_return.id as rfts_return_id,
                output_rfts_packing_po_return.master_plan_id,
                output_rfts_packing_po_return.so_det_id,
                master_plan.gambar,
                output_defect_packing_po_return.defect_area_x,
                output_defect_packing_po_return.defect_area_y,
                DATE_FORMAT(output_defect_packing_po_return.updated_at, '%d-%m-%Y') AS waktu,
                output_defect_packing_po_return.updated_at,
                output_defect_packing_po_return.kode_numbering,
                po,
                line_qc_finishing,
                CONCAT(output_rfts_packing_po_return.kpno, ' - ', output_rfts_packing_po_return.style, ' - ', output_rfts_packing_po_return.color) AS masterplan,
                size,
                output_defect_types.defect_type,
                output_defect_areas.defect_area
            ")
            ->leftJoin('output_rfts_packing_po_return', 'output_rfts_packing_po_return.id', '=', 'output_defect_packing_po_return.output_rfts_packing_po_return_id')
            ->leftJoin('master_plan', 'master_plan.id', '=', 'output_rfts_packing_po_return.master_plan_id')
            ->leftJoin('output_defect_areas', 'output_defect_areas.id', '=', 'output_defect_packing_po_return.defect_area_id')
            ->leftJoin('output_defect_types', 'output_defect_types.id', '=', 'output_defect_packing_po_return.defect_type_id')
            ->where('defect_status', 'defect')
            ->where('line_qc_finishing', auth()->user()->username)
            ->orderBy('output_defect_packing_po_return.id', 'DESC');

        $queryReject = DB::table('output_reject_packing_po_return')
            ->selectRaw("
                output_reject_packing_po_return.id,
                output_rfts_packing_po_return.id as rfts_return_id,
                output_rfts_packing_po_return.master_plan_id,
                output_rfts_packing_po_return.so_det_id,
                master_plan.gambar,
                output_reject_packing_po_return.reject_area_x as defect_area_x,
                output_reject_packing_po_return.reject_area_y as defect_area_y,
                DATE_FORMAT(output_reject_packing_po_return.updated_at, '%d-%m-%Y') AS waktu,
                output_reject_packing_po_return.updated_at,
                output_reject_packing_po_return.kode_numbering,
                po,
                line_qc_finishing,
                CONCAT(output_rfts_packing_po_return.kpno, ' - ', output_rfts_packing_po_return.style, ' - ', output_rfts_packing_po_return.color) AS masterplan,
                size,
                output_defect_types.defect_type,
                output_defect_areas.defect_area
            ")
            ->leftJoin('output_rfts_packing_po_return', 'output_rfts_packing_po_return.id', '=', 'output_reject_packing_po_return.output_rfts_packing_po_return_id')
            ->leftJoin('master_plan', 'master_plan.id', '=', 'output_rfts_packing_po_return.master_plan_id')
            ->leftJoin('output_defect_areas', 'output_defect_areas.id', '=', 'output_reject_packing_po_return.reject_area_id')
            ->leftJoin('output_defect_types', 'output_defect_types.id', '=', 'output_reject_packing_po_return.reject_type_id')
            ->where('line_qc_finishing', auth()->user()->username)
            ->whereDate('output_reject_packing_po_return.updated_at', $this->date)
            ->orderBy('output_reject_packing_po_return.id', 'DESC');

        $summary = $query->paginate(10);
        $summaryDefect = $queryDefect->paginate(10);
        $summaryReject = $queryReject->paginate(10);

        return view('livewire.return-reject', [
            'summary' => $summary,
            'summaryDefect' => $summaryDefect,
            'summaryReject' => $summaryReject,
        ]);
    }

    public function dehydrate()
    {
        $this->resetValidation();
        $this->resetErrorBag();
    }
}
