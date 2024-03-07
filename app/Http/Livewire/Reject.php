<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Session\SessionManager;
use App\Models\SignalBit\Reject as RejectModel;
use App\Models\SignalBit\Rft;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\EndLineOutput;
use App\Models\Nds\Numbering;
use Carbon\Carbon;
use DB;

class Reject extends Component
{
    public $orderInfo;
    public $orderWsDetailSizes;
    public $output;
    public $sizeInput;
    public $sizeInputText;
    public $numberingInput;
    public $numberingCode;
    public $reject;

    public $rapidReject;
    public $rapidRejectCount;

    protected $rules = [
        'sizeInput' => 'required',
        'numberingInput' => 'required|unique:output_rfts_packing,kode_numbering|unique:output_defects_packing,kode_numbering|unique:output_rejects_packing,kode_numbering',
    ];

    protected $messages = [
        'sizeInput.required' => 'Harap scan qr.',
        'numberingInput.required' => 'Harap scan qr.',
        'numberingInput.unique' => 'Kode qr sudah discan.',
    ];

    protected $listeners = [
        'updateWsDetailSizes' => 'updateWsDetailSizes',
        'setAndSubmitInputReject' => 'setAndSubmitInput',
        'toInputPanel' => 'resetError'
    ];

    public function mount(SessionManager $session, $orderWsDetailSizes)
    {
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
        $this->numberingInput = null;
        $this->numberingCode = null;

        $this->orderInfo = session()->get('orderInfo', $this->orderInfo);
        $this->orderWsDetailSizes = session()->get('orderWsDetailSizes', $this->orderWsDetailSizes);

        if ($panel == 'reject') {
            $this->emit('qrInputFocus', 'reject');
        }
    }

    public function updateOutput()
    {
        // Get total output
        $this->output = RejectModel::
            where('master_plan_id', $this->orderInfo->id)->
            count();

        // Reject
        $this->reject = RejectModel::
            where('master_plan_id', $this->orderInfo->id)->
            whereRaw("DATE(updated_at) = '".date('Y-m-d')."'")->
            get();
    }

    public function clearInput()
    {
        $this->sizeInput = null;
        $this->numberingInput = null;
        $this->numberingCode = null;
    }

    public function submitInput()
    {
        $this->emit('qrInputFocus', 'reject');

        if ($this->numberingCode) {
            $numberingData = Numbering::where("kode", $this->numberingCode)->first();

            if ($numberingData) {
                $this->sizeInput = $numberingData->so_det_id;
                $this->sizeInputText = $numberingData->size;
                $this->numberingInput = $numberingData->no_cut_size;
            }
        }

        $validatedData = $this->validate();

        $endlineOutputData = EndlineOutput::where("kode_numbering", $this->numberingInput)->first();

        if ($endlineOutputData && $this->orderWsDetailSizes->where('size', $this->sizeInputText)->count() > 0) {
            $insertReject = RejectModel::create([
                'master_plan_id' => $this->orderInfo->id,
                'so_det_id' => $this->sizeInput,
                'kode_numbering' => $this->numberingInput,
                'status' => 'NORMAL',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            if ($insertReject) {
                $this->emit('alert', 'success', "1 output berukuran ".$this->sizeInputText." berhasil terekam.");

                $this->sizeInput = '';
                $this->sizeInputText = '';
                $this->numberingInput = '';
                $this->numberingCode = '';
            } else {
                $this->emit('alert', 'error', "Terjadi kesalahan. Output tidak berhasil direkam.");
            }
        } else {
            $this->emit('alert', 'error', "Terjadi kesalahan. QR tidak sesuai.");
        }

        $this->emit('qrInputFocus', 'reject');
    }

    public function setAndSubmitInput($scannedNumbering, $scannedSize, $scannedSizeText, $scannedNumberingCode) {
        $this->numberingCode = $scannedNumberingCode;
        $this->numberingInput = $scannedNumbering;
        $this->sizeInput = $scannedSize;
        $this->sizeInputText = $scannedSizeText;

        $this->submitInput();
    }

    public function pushRapidReject($numberingInput, $sizeInput, $sizeInputText, $numberingCode) {
        $exist = false;

        foreach ($this->rapidReject as $item) {
            if (($numberingInput && $item['numberingInput'] == $numberingInput) || ($numberingCode && $item['numberingCode'] == $numberingCode)) {
                $exist = true;
            }
        }

        if (!$exist) {
            $this->rapidRejectCount += 1;

            if ($numberingCode) {
                $numberingData = Numbering::where("kode", $numberingCode)->first();

                if ($numberingData) {
                    $sizeInput = $numberingData->so_det_id;
                    $sizeInputText = $numberingData->size;
                    $numberingInput = $numberingData->no_cut_size;
                }
            }

            array_push($this->rapidReject, [
                'numberingInput' => $numberingInput,
                'sizeInput' => $sizeInput,
                'sizeInputText' => $sizeInputText,
                'numberingCode' => $numberingCode,
            ]);
        }
    }

    public function submitRapidInput() {
        $rapidRejectFiltered = [];
        $success = 0;
        $fail = 0;

        if ($this->rapidReject && count($this->rapidReject) > 0) {

            for ($i = 0; $i < count($this->rapidReject); $i++) {
                $endlineOutputCount = EndlineOutput::where("kode_numbering", $this->rapidReject[$i]['numberingInput'])->count();

                if (($endlineOutputCount > 0) && !(RejectModel::where('kode_numbering', $this->rapidReject[$i]['numberingInput'])->count() > 0 || Rft::where('kode_numbering', $this->rapidReject[$i]['numberingInput'])->count() > 0 || Defect::where('kode_numbering', $this->rapidReject[$i]['numberingInput'])->count() > 0) && ($this->orderWsDetailSizes->where('size', $this->rapidReject[$i]['sizeInputText'])->count() > 0)) {
                    array_push($rapidRejectFiltered, [
                        'master_plan_id' => $this->orderInfo->id,
                        'so_det_id' => $this->rapidReject[$i]['sizeInput'],
                        'kode_numbering' => $this->rapidReject[$i]['numberingInput'],
                        'status' => 'NORMAL',
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);

                    $success += 1;
                } else {
                    $fail += 1;
                }
            }
        }

        $rapidRejectInsert = RejectModel::insert($rapidRejectFiltered);

        $this->emit('alert', 'success', $success." output berhasil terekam. ");
        $this->emit('alert', 'error', $fail." output gagal terekam.");

        $this->rapidReject = [];
        $this->rapidRejectCount = 0;
    }

    public function render(SessionManager $session)
    {
        $this->orderInfo = $session->get('orderInfo', $this->orderInfo);
        $this->orderWsDetailSizes = $session->get('orderWsDetailSizes', $this->orderWsDetailSizes);

        // Get total output
        $this->output = RejectModel::
            where('master_plan_id', $this->orderInfo->id)->
            count();

        // Reject
        $this->reject = RejectModel::
            where('master_plan_id', $this->orderInfo->id)->
            whereRaw("DATE(updated_at) = '".date('Y-m-d')."'")->
            get();

        return view('livewire.reject');
    }
}
