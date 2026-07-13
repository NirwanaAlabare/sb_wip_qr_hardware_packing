<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\Auth;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\ProductType;
use App\Models\SignalBit\DefectType;
use App\Models\SignalBit\DefectArea;
use App\Models\SignalBit\EndlineOutput;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\Reject;
use App\Models\SignalBit\Rework;
use App\Models\SignalBit\Defect as DefectModel;
use Carbon\Carbon;
use DB;

class ReturnDefect extends Component
{
    use WithFileUploads;

    public $orderInfo;
    public $orderWsDetailSizes;
    public $output;
    public $outputInput;
    public $sizeInput;
    public $sizeInputText;
    public $defectTypes;
    public $defectAreas;
    public $productTypes;
    public $defectType;
    public $defectArea;
    public $productType;
    public $defectTypeAdd;
    public $defectAreaAdd;
    public $productTypeAdd;
    public $productTypeImageAdd;
    public $defectAreaPositionX;
    public $defectAreaPositionY;
    public $selectedId;
    public $masterPlanId;
    public $soDetId;
    public $kode_qr;
    public $po;
    public $worksheet_style;
    public $color;
    public $size;
    public $packing_line;

    public function openDefectModal($id, $masterPlanId, $soDetId)
    {
        $this->selectedId = $id;
        $this->masterPlanId = $masterPlanId;
        $this->soDetId = $soDetId;

        $this->emit('showModal', 'defect');
    }

    protected $rules = [
        // 'outputInput' => 'required|numeric|min:1',
        // 'sizeInput' => 'required',
        // 'productType' => 'required',
        'defectType' => 'required',
        'defectArea' => 'required',
        'defectAreaPositionX' => 'required',
        'defectAreaPositionY' => 'required',
    ];

    protected $messages = [
        // 'outputInput.required' => 'Harap tentukan kuantitas output.',
        // 'outputInput.numeric' => 'Harap isi kuantitas output dengan angka.',
        // 'outputInput.min' => 'Kuantitas output tidak bisa kurang dari 1.',
        // 'sizeInput.required' => 'Harap tentukan ukuran output.',
        // 'productType.required' => 'Harap tentukan tipe produk.',
        'defectType.required' => 'Harap tentukan jenis defect.',
        'defectArea.required' => 'Harap tentukan area defect.',
        'defectAreaPositionX.required' => "Harap tentukan posisi defect area dengan mengklik tombol 'gambar' di samping 'select product type'.",
        'defectAreaPositionY.required' => "Harap tentukan posisi defect area dengan mengklik tombol 'gambar' di samping 'select product type'.",
    ];

    protected $listeners = [
        'setDefectAreaPosition' => 'setDefectAreaPosition',
        'updateWsDetailSizes' => 'updateWsDetailSizes',
        'updateOutput' => 'updateOutput',
        'clearInput' => 'clearInput',
    ];

    public function mount(SessionManager $session)
    {
        // $this->orderWsDetailSizes = $orderWsDetailSizes;
        // $session->put('orderWsDetailSizes', $orderWsDetailSizes);
        $this->output = 0;
        $this->outputInput = 1;
        $this->sizeInput = null;
        $this->defectType = null;
        $this->defectArea = null;
        $this->productType = null;
        $this->defectAreaPositionX = null;
        $this->defectAreaPositionY = null;
    }

    public function dehydrate()
    {
        $this->resetValidation();
        $this->resetErrorBag();
    }

    public function updateWsDetailSizes()
    {
        $this->outputInput = 1;
        $this->sizeInput = null;
        $this->sizeInputText = '';

        // $this->orderInfo = session()->get('orderInfo', $this->orderInfo);
        // $this->orderWsDetailSizes = session()->get('orderWsDetailSizes', $this->orderWsDetailSizes);
    }

    public function updateOutput()
    {
        $this->output = collect(DB::select("select output_defects_packing.*, so_det.size, COUNT(output_defects_packing.id) output from `output_defects_packing` left join `so_det` on `so_det`.`id` = `output_defects_packing`.`so_det_id` where `master_plan_id` = '".$this->orderInfo->id."' and `status` = 'NORMAL' group by so_det.id"));
    }

    public function updatedproductTypeImageAdd()
    {
        $this->validate([
            'productTypeImageAdd' => 'image',
        ]);
    }

    // public function submitProductType()
    // {
    //     if ($this->productTypeAdd && $this->productTypeImageAdd) {

    //         $productTypeImageAddName = md5($this->productTypeImageAdd . microtime()).'.'.$this->productTypeImageAdd->extension();
    //         $this->productTypeImageAdd->storeAs('public/images', $productTypeImageAddName);

    //         $createProductType = ProductType::create([
    //             'product_type' => $this->productTypeAdd,
    //             'image' => $productTypeImageAddName,
    //         ]);

    //         if ($createProductType) {
    //             $this->emit('alert', 'success', 'Product Time : '.$this->productTypeAdd.' berhasil ditambahkan.');

    //             $this->productTypeAdd = null;
    //             $this->productTypeImageAdd = null;
    //         } else {
    //             $this->emit('alert', 'error', 'Terjadi kesalahan.');
    //         }
    //     } else {
    //         $this->emit('alert', 'error', 'Harap tentukan nama tipe produk beserta gambarnya');
    //     }
    // }

    // public function submitDefectType()
    // {
    //     if ($this->defectTypeAdd) {
    //         $createDefectType = DefectType::create([
    //             'defect_type' => $this->defectTypeAdd
    //         ]);

    //         if ($createDefectType) {
    //             $this->emit('alert', 'success', 'Defect type : '.$this->defectTypeAdd.' berhasil ditambahkan.');

    //             $this->defectTypeAdd = '';
    //         } else {
    //             $this->emit('alert', 'error', 'Terjadi kesalahan.');
    //         }
    //     } else {
    //         $this->emit('alert', 'error', 'Harap tentukan nama defect type');
    //     }
    // }

    // public function submitDefectArea()
    // {
    //     if ($this->defectAreaAdd) {

    //         $createDefectArea = DefectArea::create([
    //             'defect_area' => $this->defectAreaAdd,
    //         ]);

    //         if ($createDefectArea) {
    //             $this->emit('alert', 'success', 'Defect area : '.$this->defectAreaAdd.' berhasil ditambahkan.');

    //             $this->defectAreaAdd = null;
    //         } else {
    //             $this->emit('alert', 'error', 'Terjadi kesalahan.');
    //         }
    //     } else {
    //         $this->emit('alert', 'error', 'Harap tentukan nama defect area');
    //     }
    // }

    public function clearInput()
    {
        $this->outputInput = 1;
        $this->sizeInput = '';
    }

    public function outputIncrement()
    {
        $this->outputInput++;
    }

    public function showDefectAreaImage($gambar, $x, $y)
    {
        $this->emit('showDefectAreaImage', $gambar, $x, $y);
    }

    public function hideDefectAreaImage()
    {
        $this->defectImage = null;
        $this->defectPositionX = null;
        $this->defectPositionY = null;
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

    public function selectDefectAreaPosition()
    {
        $masterPlan = MasterPlan::select('gambar')->find($this->masterPlanId);

        if ($masterPlan) {
            $this->emit('showSelectDefectArea', $masterPlan->gambar);
        } else {
            $this->emit('alert', 'error', 'Harap pilih tipe produk terlebih dahulu');
        }
    }

    public function setDefectAreaPosition($x, $y)
    {
        $this->defectAreaPositionX = $x;
        $this->defectAreaPositionY = $y;
    }

    public function preSubmitInput()
    {
        $this->emit('clearSelectDefectAreaPoint');

        $this->defectType = null;
        $this->defectArea = null;
        $this->productType = null;
        $this->defectAreaPositionX = null;
        $this->defectAreaPositionY = null;

        $this->validateOnly('outputInput');
        $this->validateOnly('sizeInput');

        $endlineOutputData = EndlineOutput::selectRaw("output_rfts.*")->leftJoin("master_plan", "master_plan.id", "=", "output_rfts.master_plan_id")->where("id_ws", $this->orderInfo->id_ws)->where("color", $this->orderInfo->color)->where("so_det_id", $this->sizeInput)->count();
        $currentRftData = Rft::selectRaw("output_rfts_packing.*")->leftJoin("master_plan", "master_plan.id", "=", "output_rfts_packing.master_plan_id")->where('id_ws', $this->orderInfo->id_ws)->where("color", $this->orderInfo->color)->where("so_det_id", $this->sizeInput)->count();
        $currentDefectData = DefectModel::selectRaw("output_defects_packing.*")->leftJoin("master_plan", "master_plan.id", "=", "output_defects_packing.master_plan_id")->where('id_ws', $this->orderInfo->id_ws)->where("color", $this->orderInfo->color)->where("so_det_id", $this->sizeInput)->where("defect_status", "defect")->count();
        $currentRejectData = Reject::selectRaw("output_rejects_packing.*")->leftJoin("master_plan", "master_plan.id", "=", "output_rejects_packing.master_plan_id")->where('id_ws', $this->orderInfo->id_ws)->where("color", $this->orderInfo->color)->where("so_det_id", $this->sizeInput)->count();
        $currentOutputData = $currentRftData+$currentDefectData+$currentRejectData;
        $balanceOutputData = $endlineOutputData-$currentOutputData;

        $additionalMessage = $balanceOutputData < $this->outputInput && $balanceOutputData > 0 ? "<b>".($this->outputInput - $balanceOutputData)."</b> output melebihi batas input." : null;
        // if ($balanceOutputData < $this->outputInput) {
        //     $this->outputInput = $balanceOutputData;
        // }

        if ($this->outputInput > 0) {
            if ($additionalMessage) {
                $this->emit('alert', 'error', $additionalMessage);
            }

            $this->emit('showModal', 'defect');
        } else {
            $this->emit('alert', 'error', "Output packing-line tidak bisa melebihi endline.");
        }
    }

    public function submitInput(SessionManager $session)
    {
        $validatedData = $this->validate();

        $insertData = [];
        for ($i = 0; $i < $this->outputInput; $i++)
        {
            array_push($insertData, [
                'output_rfts_packing_po_return_id' => $this->selectedId,
                'so_det_id' => $this->soDetId,
                'master_plan_id' => $this->masterPlanId,
                'kode_numbering' => $this->kode_qr,
                'defect_type_id' => $this->defectType,
                'defect_area_id' => $this->defectArea,
                'defect_area_x' => $this->defectAreaPositionX,
                'defect_area_y' => $this->defectAreaPositionY,
                'defect_status' => 'defect',
                'status' => 'NORMAL',
                'created_by' => Auth::user()->username,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }

        $insertDefect = DB::table('output_defect_packing_po_return')->insert($insertData);

        if ($insertDefect) {
            DB::table('output_rfts_packing_po_return')->where('id', $this->selectedId)
            ->update([
                'status'     => 'defect',
                'updated_at' => Carbon::now(),
            ]);

            $type = DefectType::select('defect_type')->find($this->defectType);
            $area = DefectArea::select('defect_area')->find($this->defectArea);
            $getSize = DB::table('so_det')
                ->select('id', 'size')
                ->where('id', $this->soDetId)
                ->first();

            $this->emit('alert', 'success', $this->outputInput." output DEFECT berukuran ".$getSize->size." dengan jenis defect : ".$type->defect_type." dan area defect : ".$area->defect_area." berhasil terekam.");
            $this->emit('hideModal', 'defect');

            $this->reset([
                'selectedId',
                'masterPlanId',
                'soDetId',
                'kode_qr',
                'po',
                'worksheet_style',
                'color',
                'size',
                'packing_line',
                'defectType',
                'defectArea',
                'defectAreaPositionX',
                'defectAreaPositionY',
            ]);
            $this->emit('clearSelectDefectAreaPoint');
            $this->outputInput = 1;
            $this->soDetId = '';
        } else {
            $this->emit('alert', 'error', "Terjadi kesalahan. Output tidak berhasil direkam.");
        }
    }

    public function render(SessionManager $session)
    {
        // $this->orderInfo = $session->get('orderInfo', $this->orderInfo);
        $this->orderWsDetailSizes = $session->get('orderWsDetailSizes', $this->orderWsDetailSizes);

        // Get total output
        // $this->output = collect(DB::select("select output_defects_packing.*, so_det.size, COUNT(output_defects_packing.id) output from `output_defects_packing` left join `so_det` on `so_det`.`id` = `output_defects_packing`.`so_det_id` where `master_plan_id` = '".$this->orderInfo->id."' and `defect_status` = 'defect' group by so_det.id"));

        // Defect types
        $this->productTypes = ProductType::orderBy('product_type')->get();

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
                DATE_FORMAT(created_at, '%d-%m-%Y') AS waktu,
                created_at,
                kode_numbering,
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
                output_rfts_packing_po_return.master_plan_id,
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

        $summary = $query->paginate(10);
        $summaryDefect = $queryDefect->paginate(10);

        return view('livewire.return-defect', [
            'summary' => $summary,
            'summaryDefect' => $summaryDefect,
        ]);
    }
}
