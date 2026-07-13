<?php

namespace App\Http\Livewire;

// use App\Models\SignalBit\ReturnPacking;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ReturnWaiting extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap'; 

    public $startDate;
    public $endDate;

    public function mount()
    {

        $this->startDate = Carbon::today()->format('Y-m-d');
        $this->endDate   = Carbon::today()->format('Y-m-d');
    }

    public function dehydrate()
    {
        $this->resetValidation();
        $this->resetErrorBag();
    }

    public function render()
    {
        $query = DB::table('output_rfts_packing_po_return')
            ->selectRaw("
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

        $summary = $query->paginate(10);

        return view('livewire.return-waiting', [
            'summary' => $summary,
        ]);
    }
}