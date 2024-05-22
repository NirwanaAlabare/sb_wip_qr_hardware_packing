<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Session\SessionManager;
use App\Models\SignalBit\TemporaryOutput;
use App\Models\SignalBit\Rft as RftModel;
use App\Models\SignalBit\Rework;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\Reject;
use App\Models\SignalBit\EndlineOutput;
use App\Models\Nds\Numbering;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use DB;

class RftTemporary extends Component
{
    public $orderDate;
    public $orderWsDetailSizes;
    public $sizeInput;
    public $sizeInputText;
    public $numberingCode;
    public $numberingInput;
    public $rapidRft;
    public $rapidRftCount;
    public $rft;
    public $submitting;

    protected $rules = [
        'sizeInput' => 'required',
        'noCutInput' => 'required',
        'numberingInput' => 'required|unique:output_rfts,kode_numbering|unique:output_defects,kode_numbering|unique:output_rejects,kode_numbering|unique:temporary_output_packing,kode_numbering',
    ];

    protected $messages = [
        'sizeInput.required' => 'Harap scan qr.',
        'noCutInput.required' => 'Harap scan qr.',
        'numberingInput.required' => 'Harap scan qr.',
        'numberingInput.unique' => 'Kode qr sudah discan.',
    ];

    protected $listeners = [
        'updateWsDetailSizes' => 'updateWsDetailSizes',
        'setAndSubmitInputRft' => 'setAndSubmitInput',
        'toInputPanel' => 'resetError'
    ];

    public function mount(SessionManager $session, $orderDate, $orderWsDetailSizes)
    {
        $this->orderDate = $orderDate;
        $this->orderWsDetailSizes = $orderWsDetailSizes;
        $session->put('orderWsDetailSizes', $orderWsDetailSizes);
        $this->output = 0;
        $this->sizeInput = null;
        $this->sizeInputText = null;
        $this->noCutInput = null;
        $this->numberingInput = null;
        $this->rapidRft = [];
        $this->rapidRftCount = 0;
        $this->rft = [];
        $this->submitting = false;
    }

    public function dehydrate()
    {
        $this->resetValidation();
        $this->resetErrorBag();
    }

    public function resetError() {
        $this->resetValidation();
        $this->resetErrorBag();
    }

    public function updateWsDetailSizes($panel)
    {
        $this->sizeInput = null;
        $this->sizeInputText = null;
        $this->noCutInput = null;
        $this->numberingInput = null;

        $this->orderWsDetailSizes = session()->get('orderWsDetailSizes', $this->orderWsDetailSizes);

        if ($panel == 'rft') {
            $this->emit('qrInputFocus', 'rft');
        }
    }

    public function clearInput()
    {
        $this->sizeInput = null;
    }

    public function submitInput()
    {
        $this->emit('qrInputFocus', 'rft');

        if ($this->numberingInput) {
            $numberingData = Numbering::where("kode", $this->numberingInput)->first();

            if ($numberingData) {
                $this->sizeInput = $numberingData->so_det_id;
                $this->sizeInputText = $numberingData->size;
                $this->noCutInput = $numberingData->no_cut_size;
            }
        }

        $validatedData = $this->validate();

        $endlineOutputData = EndlineOutput::where("kode_numbering", $this->numberingInput)->first();
        $thisOrderWsDetailSize = $this->orderWsDetailSizes->where('so_det_id', $this->sizeInput)->first();
        if ($endlineOutputData && $thisOrderWsDetailSize) {
            $insertRft = RftModel::create([
                'master_plan_id' => $thisOrderWsDetailSize['master_plan_id'],
                'so_det_id' => $this->sizeInput,
                'no_cut_size' => $this->noCutInput,
                'kode_numbering' => $this->numberingInput,
                'status' => 'NORMAL',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            if ($insertRft) {
                $this->emit('alert', 'warning', "MASTER PLAN DITEMUKAN");
                $this->emit('alert', 'success', "1 output berukuran ".$this->sizeInputText." berhasil terekam.");

                $this->sizeInput = '';
                $this->sizeInputText = '';
                $this->noCutInput = '';
                $this->numberingInput = '';
            } else {
                $this->emit('alert', 'error', "Terjadi kesalahan. Output tidak berhasil direkam.");
            }
        } else {
            $insertTemp = TemporaryOutput::create([
                'line_id' => Auth::user()->line_id,
                'so_det_id' => $this->sizeInput,
                'no_cut_size' => $this->noCutInput,
                'size' => $this->sizeInputText,
                'kode_numbering' => $this->numberingInput,
                'tipe_output' => 'rft',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            if ($insertTemp) {
                $this->emit('alert', 'warning', "TANPA MASTER PLAN");
                $this->emit('alert', 'success', "1 output berukuran ".$this->sizeInputText." berhasil terekam di TEMPORARY.");

                $this->sizeInput = '';
                $this->sizeInputText = '';
                $this->noCutInput = '';
                $this->numberingInput = '';
            } else {
                $this->emit('alert', 'error', "Terjadi kesalahan. Output tidak berhasil direkam.");
            }
        }
    }

    public function pushRapidRft($numberingInput, $sizeInput, $sizeInputText) {
        $exist = false;

        foreach ($this->rapidRft as $item) {
            if (($numberingInput && $item['numberingInput'] == $numberingInput)) {
                $exist = true;
            }
        }

        if (!$exist) {
            $this->rapidRftCount += 1;

            if ($numberingInput) {
                $numberingData = Numbering::where("kode", $numberingInput)->first();

                if ($numberingData) {
                    $sizeInput = $numberingData->so_det_id;
                    $sizeInputText = $numberingData->size;
                    $noCutInput = $numberingData->no_cut_size;
                }
            }

            array_push($this->rapidRft, [
                'numberingInput' => $numberingInput,
                'sizeInput' => $sizeInput,
                'sizeInputText' => $sizeInputText,
                'noCutInput' => $noCutInput,
            ]);
        }
    }

    public function submitRapidInput() {
        $rapidRftFiltered = [];
        $rapidTemporaryFiltered = [];
        $success = 0;
        $temporary = 0;
        $fail = 0;

        if ($this->rapidRft && count($this->rapidRft) > 0) {
            for ($i = 0; $i < count($this->rapidRft); $i++) {
                $endlineOutputData = EndlineOutput::where("kode_numbering", $this->rapidRft[$i]['numberingInput'])->first();
                $thisOrderWsDetailSize = $this->orderWsDetailSizes->where('so_det_id', $this->rapidRft[$i]['sizeInput'])->first();
                if (!(RftModel::where('kode_numbering', $this->rapidRft[$i]['numberingInput'])->count() > 0 || Defect::where('kode_numbering', $this->rapidRft[$i]['numberingInput'])->count() > 0 || Reject::where('kode_numbering', $this->rapidRft[$i]['numberingInput'])->count() > 0) && ($thisOrderWsDetailSize) && ($endlineOutputData)) {
                    array_push($rapidRftFiltered, [
                        'master_plan_id' => $thisOrderWsDetailSize['master_plan_id'],
                        'so_det_id' => $this->rapidRft[$i]['sizeInput'],
                        'no_cut_size' => $this->rapidRft[$i]['noCutInput'],
                        'kode_numbering' => $this->rapidRft[$i]['numberingInput'],
                        'status' => 'NORMAL',
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);

                    $success += 1;
                } else {
                    if (!(RftModel::where('kode_numbering', $this->rapidRft[$i]['numberingInput'])->count() > 0 || Defect::where('kode_numbering', $this->rapidRft[$i]['numberingInput'])->count() > 0 || Reject::where('kode_numbering', $this->rapidRft[$i]['numberingInput'])->count() > 0 || TemporaryOutput::where('kode_numbering', $this->rapidRft[$i]['numberingInput'])->count() > 0)) {
                        array_push($rapidTemporaryFiltered, [
                            'line_id' => Auth::user()->line_id,
                            'so_det_id' => $this->rapidRft[$i]['sizeInput'],
                            'no_cut_size' => $this->rapidRft[$i]['noCutInput'],
                            'size' => $this->rapidRft[$i]['sizeInputText'],
                            'kode_numbering' => $this->rapidRft[$i]['numberingInput'],
                            'tipe_output' => 'rft',
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]);

                        $temporary += 1;
                    } else {
                        $fail += 1;
                    }
                }
            }
        }

        $rapidRftInsert = RftModel::insert($rapidRftFiltered);
        $temporaryInsert = TemporaryOutput::insert($rapidTemporaryFiltered);

        $this->emit('alert', 'success', $success." output berhasil terekam. ");
        $this->emit('alert', 'warning', $temporary." output berhasil terekam di TEMPORARY. ");
        $this->emit('alert', 'error', $fail." output gagal terekam.");

        $this->rapidRft = [];
        $this->rapidRftCount = 0;
    }

    public function setAndSubmitInput($scannedNumbering) {
        $this->numberingInput = $scannedNumbering;

        $this->submitInput();
    }

    public function render(SessionManager $session)
    {
        $this->orderWsDetailSizes = $session->get('orderWsDetailSizes', $this->orderWsDetailSizes);

        $this->rft = TemporaryOutput::
            where('temporary_output_packing.line_id', Auth::user()->line_id)->
            whereRaw('(DATE(temporary_output_packing.created_at) = "'.$this->orderDate.'" OR DATE(temporary_output_packing.updated_at) = "'.$this->orderDate.'")')->
            where('tipe_output', 'rft')->
            get();

        return view('livewire.rft-temporary');
    }
}
