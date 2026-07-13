<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Carbon;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\Rework as ReworkModel;
use DB;

class ReturnRework extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // filters
    public $orderInfo;
    public $orderWsDetailSizes;
    public $searchDefect;
    public $searchRework;

    // defect position
    public $defectImage;
    public $defectPositionX;
    public $defectPositionY;

    // defect list
    public $allDefectListFilter;
    public $allDefectImage;
    public $allDefectPosition;
    // public $allDefectList;

    // mass rework
    public $massQty;
    public $massSize;
    public $massDefectType;
    public $massDefectTypeName;
    public $massDefectArea;
    public $massDefectAreaName;
    public $massSelectedDefect;

    public $info;

    public $rework;

    public $idDefect;
    public $kode_qr;
    public $po;
    public $worksheet_style;
    public $color;
    public $size;
    public $packing_line;
    public $date = '';

    protected $listeners = [
        'hideDefectAreaImageClear' => 'hideDefectAreaImage',
        'openDefectModal' => 'openDefectModal'
    ];

    public function loadReworkPage()
    {
        $this->emit('loadReworkPageJs');
    }

    public function mount(SessionManager $session)
    {
        // $this->orderWsDetailSizes = $orderWsDetailSizes;
        // $session->put('orderWsDetailSizes', $orderWsDetailSizes);

        $this->massSize = '';

        $this->info = true;
        $this->date = date('Y-m-d');
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

    public function updatingSearchDefect()
    {
        $this->resetPage('defectsPage');
    }

    public function updatingSearchRework()
    {
        $this->resetPage('reworksPage');
    }

    // public function openDefectModal($id)
    // {
    //     $update = DB::table('output_defect_packing_po_return')
    //         ->where('id', $id)
    //         ->update([
    //             'defect_status' => 'reworked',
    //             'reworked_at'   => Carbon::now(),
    //             'reworked_by'   => Auth::user()->username,
    //         ]);

    //     if ($update) {
    //         $this->reset([
    //             'kode_qr',
    //             'po',
    //             'worksheet_style',
    //             'color',
    //             'size',
    //             'packing_line',
    //         ]);
    //         $this->emit('focusScanInput');
    //         $this->emit('alert', 'success', 'Defect berhasil diproses ke tahap Rework.');
    //     } else {
    //         $this->emit('alert', 'error', 'Terjadi kesalahan. Defect gagal diproses ke tahap Rework.');
    //     }
    // }


    public function save()
    {
        $update = DB::table('output_defect_packing_po_return')
            ->where('id', $this->idDefect)
            ->update([
                'defect_status' => 'reworked',
                'reworked_at'   => Carbon::now(),
                'reworked_by'   => Auth::user()->username,
            ]);

        if ($update) {
            $this->reset([
                'idDefect',
                'kode_qr',
                'po',
                'worksheet_style',
                'color',
                'size',
                'packing_line',
            ]);

            $this->emit('focusScanInput');
            $this->emit('alert', 'success', 'Defect berhasil diproses ke tahap Rework.');
        } else {
            $this->emit('alert', 'error', 'Terjadi kesalahan. Defect gagal diproses ke tahap Rework.');
        }
    }

    public function render(SessionManager $session)
    {
        $query = DB::table('output_defect_packing_po_return')
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
            ->whereDate('output_defect_packing_po_return.updated_at', $this->date)
            ->orderBy('output_defect_packing_po_return.id', 'DESC');

        $queryRework = DB::table('output_defect_packing_po_return')
            ->selectRaw("
                output_defect_packing_po_return.id,
                output_rfts_packing_po_return.master_plan_id,
                master_plan.gambar,
                output_defect_packing_po_return.defect_area_x,
                output_defect_packing_po_return.defect_area_y,
                DATE_FORMAT(output_defect_packing_po_return.reworked_at, '%d-%m-%Y') AS waktu,
                output_defect_packing_po_return.reworked_at,
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
            ->where('defect_status', 'reworked')
            ->where('line_qc_finishing', auth()->user()->username)
            ->whereDate('output_defect_packing_po_return.reworked_at', $this->date)
            ->orderBy('output_defect_packing_po_return.id', 'DESC');

        $summary = $query->paginate(10);
        $summaryRework = $queryRework->paginate(10);

        return view('livewire.return-rework', [
            'summary' => $summary,
            'summaryRework' => $summaryRework,
        ]);
    }
}
