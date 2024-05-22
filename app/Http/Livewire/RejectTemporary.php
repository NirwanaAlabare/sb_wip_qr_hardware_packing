<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\Auth;
use App\Models\SignalBit\Reject as RejectModel;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\EndlineOutput;
use App\Models\SignalBit\TemporaryOutput;
use App\Models\Nds\Numbering;
use Carbon\Carbon;
use DB;

class RejectTemporary extends Component
{
    public $orderDate;
    public $orderWsDetailSizes;
    public $sizeInput;
    public $sizeInputText;
    public $noCutInput;
    public $numberingInput;
    public $reject;

    public $rapidReject;
    public $rapidRejectCount;

    protected $rules = [
        'sizeInput' => 'required',
        'noCutInput' => 'required',
        'numberingInput' => 'required|unique:output_rfts_packing,kode_numbering|unique:output_defects_packing,kode_numbering|unique:output_rejects_packing,kode_numbering|unique:temporary_output_packing,kode_numbering',
    ];

    protected $messages = [
        'sizeInput.required' => 'Harap scan qr.',
        'noCutInput.required' => 'Harap scan qr.',
        'numberingInput.required' => 'Harap scan qr.',
        'numberingInput.unique' => 'Kode qr sudah discan.',
    ];

    protected $listeners = [
        'updateWsDetailSizes' => 'updateWsDetailSizes',
        'setAndSubmitInputReject' => 'setAndSubmitInput',
        'toInputPanel' => 'resetError'
    ];

    public function mount(SessionManager $session, $orderDate, $orderWsDetailSizes)
    {
        $this->ordeDate = $orderDate;
        $this->orderWsDetailSizes = $orderWsDetailSizes;
        $session->put('orderWsDetailSizes', $orderWsDetailSizes);
        $this->sizeInput = null;

        $this->rapidReject = [];
        $this->rapidRejectCount = 0;
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

        $this->orderInfo = session()->get('orderInfo', $this->orderInfo);
        $this->orderWsDetailSizes = session()->get('orderWsDetailSizes', $this->orderWsDetailSizes);

        if ($panel == 'reject') {
            $this->emit('qrInputFocus', 'reject');
        }
    }

    public function clearInput()
    {
        $this->sizeInput = null;
        $this->noCutInput = null;
        $this->numberingInput = null;
    }

    public function submitInput()
    {
        $this->emit('qrInputFocus', 'reject');

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
            $insertReject = RejectModel::create([
                'master_plan_id' => $thisOrderWsDetailSize['master_plan_id'],
                'so_det_id' => $this->sizeInput,
                'no_cut_size' => $this->noCutInput,
                'kode_numbering' => $this->numberingInput,
                'status' => 'NORMAL',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            if ($insertReject) {
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
                'tipe_output' => 'reject',
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

            $this->emit('alert', 'error', "Terjadi kesalahan. QR tidak sesuai.");
        }

        $this->emit('qrInputFocus', 'reject');
    }

    public function setAndSubmitInput($scannedNumbering) {
        $this->numberingInput = $scannedNumbering;

        $this->submitInput();
    }

    public function pushRapidReject($numberingInput, $sizeInput, $sizeInputText) {
        $exist = false;

        foreach ($this->rapidReject as $item) {
            if (($numberingInput && $item['numberingInput'] == $numberingInput)) {
                $exist = true;
            }
        }

        if (!$exist) {
            $this->rapidRejectCount += 1;

            if ($numberingInput) {
                $numberingData = Numbering::where("kode", $numberingInput)->first();

                if ($numberingData) {
                    $sizeInput = $numberingData->so_det_id;
                    $sizeInputText = $numberingData->size;
                    $noCutInput = $numberingData->no_cut_size;
                }
            }

            array_push($this->rapidReject, [
                'numberingInput' => $numberingInput,
                'sizeInput' => $sizeInput,
                'sizeInputText' => $sizeInputText,
                'noCutInput' => $noCutInput,
            ]);
        }
    }

    public function submitRapidInput() {
        $rapidRejectFiltered = [];
        $rapidTemporaryFiltered = [];
        $success = 0;
        $temporary = 0;
        $fail = 0;

        if ($this->rapidReject && count($this->rapidReject) > 0) {
            for ($i = 0; $i < count($this->rapidReject); $i++) {
                $endlineOutputData = EndlineOutput::where("kode_numbering", $this->rapidReject[$i]['numberingInput'])->first();
                $thisOrderWsDetailSize = $this->orderWsDetailSizes->where('so_det_id', $this->rapidReject[$i]['sizeInput'])->first();
                if (!(RejectModel::where('kode_numbering', $this->rapidReject[$i]['numberingInput'])->count() > 0 || Rft::where('kode_numbering', $this->rapidReject[$i]['numberingInput'])->count() > 0 || Defect::where('kode_numbering', $this->rapidReject[$i]['numberingInput'])->count() > 0 || TemporaryOutput::where('kode_numbering', $this->rapidReject[$i]['numberingInput'])->count() > 0) && ($thisOrderWsDetailSize) && ($endlineOutputData)) {
                    array_push($rapidRejectFiltered, [
                        'master_plan_id' => $thisOrderWsDetailSize['master_plan_id'],
                        'so_det_id' => $this->rapidReject[$i]['sizeInput'],
                        'no_cut_size' => $this->rapidReject[$i]['noCutInput'],
                        'kode_numbering' => $this->rapidReject[$i]['numberingInput'],
                        'status' => 'NORMAL',
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);

                    $success += 1;
                } else {
                    if (!(RejectModel::where('kode_numbering', $this->rapidReject[$i]['numberingInput'])->count() > 0 || Rft::where('kode_numbering', $this->rapidReject[$i]['numberingInput'])->count() > 0 || Defect::where('kode_numbering', $this->rapidReject[$i]['numberingInput'])->count() > 0 || TemporaryOutput::where('kode_numbering', $this->rapidReject[$i]['numberingInput'])->count() > 0)) {
                        array_push($rapidTemporaryFiltered, [
                            'line_id' => Auth::user()->line_id,
                            'so_det_id' => $this->rapidReject[$i]['sizeInput'],
                            'no_cut_size' => $this->rapidReject[$i]['noCutInput'],
                            'size' => $this->rapidReject[$i]['sizeInputText'],
                            'kode_numbering' => $this->rapidReject[$i]['numberingInput'],
                            'tipe_output' => 'reject',
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
        $rapidRejectInsert = RejectModel::insert($rapidRejectFiltered);
        $temporaryInsert = TemporaryOutput::insert($rapidTemporaryFiltered);

        $this->emit('alert', 'success', $success." output berhasil terekam. ");
        $this->emit('alert', 'warning', $temporary." output berhasil terekam di TEMPORARY. ");
        $this->emit('alert', 'error', $fail." output gagal terekam.");

        $this->rapidReject = [];
        $this->rapidRejectCount = 0;
    }

    public function render(SessionManager $session)
    {
        $this->orderWsDetailSizes = $session->get('orderWsDetailSizes', $this->orderWsDetailSizes);

        // Reject
        $this->reject = TemporaryOutput::
            where('temporary_output_packing.line_id', Auth::user()->line_id)->
            whereRaw('(DATE(temporary_output_packing.created_at) = "'.$this->orderDate.'" OR DATE(temporary_output_packing.updated_at) = "'.$this->orderDate.'")')->
            where('tipe_output', 'reject')->
            get();

        return view('livewire.reject-temporary');
    }
}
