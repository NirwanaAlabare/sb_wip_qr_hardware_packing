<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Session\SessionManager;
use App\Models\SignalBit\Rft as RftModel;
use App\Models\SignalBit\Rework;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\Reject;
use App\Models\SignalBit\EndlineOutput;
use App\Models\Nds\Numbering;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use DB;

class Rft extends Component
{
    public $orderInfo;
    public $orderWsDetailSizes;
    public $output;
    public $sizeInput;
    public $sizeInputText;
    public $numberingInput;
    public $numberingCode;
    public $rapidRft;
    public $rapidRftCount;
    public $rft;

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
        'setAndSubmitInputRft' => 'setAndSubmitInput',
        'toInputPanel' => 'resetError'
    ];

    public function mount(SessionManager $session, $orderWsDetailSizes)
    {
        $this->orderWsDetailSizes = $orderWsDetailSizes;
        $session->put('orderWsDetailSizes', $orderWsDetailSizes);
        $this->output = 0;
        $this->sizeInput = null;
        $this->sizeInputText = null;
        $this->numberingInput = null;
        $this->numberingCode = null;
        $this->rapidRft = [];
        $this->rapidRftCount = 0;
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
        $this->numberingInput = null;
        $this->numberingCode = null;

        $this->orderInfo = session()->get('orderInfo', $this->orderInfo);
        $this->orderWsDetailSizes = session()->get('orderWsDetailSizes', $this->orderWsDetailSizes);

        if ($panel == 'rft') {
            $this->emit('qrInputFocus', 'rft');
        }
    }

    public function updateOutput()
    {
        $this->output = RftModel::
            where('master_plan_id', $this->orderInfo->id)->
            where('status', 'NORMAL')->
            count();

        $this->rft = RftModel::
            where('master_plan_id', $this->orderInfo->id)->
            where('status', 'NORMAL')->
            whereRaw("DATE(updated_at) = '".date('Y-m-d')."'")->
            get();
    }

    public function clearInput()
    {
        $this->sizeInput = null;
    }

    public function submitInput()
    {
        $this->emit('qrInputFocus', 'rft');

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
            $insertRft = RftModel::create([
                'master_plan_id' => $this->orderInfo->id,
                'so_det_id' => $this->sizeInput,
                'kode_numbering' => $this->numberingInput,
                'status' => 'NORMAL',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            if ($insertRft) {
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
    }

    public function pushRapidRft($numberingInput, $sizeInput, $sizeInputText, $numberingCode) {
        $exist = false;

        foreach ($this->rapidRft as $item) {
            if (($numberingInput && $item['numberingInput'] == $numberingInput) || ($numberingCode && $item['numberingCode'] == $numberingCode)) {
                $exist = true;
            }
        }

        if (!$exist) {
            $this->rapidRftCount += 1;

            if ($numberingCode) {
                $numberingData = Numbering::where("kode", $numberingCode)->first();

                if ($numberingData) {
                    $sizeInput = $numberingData->so_det_id;
                    $sizeInputText = $numberingData->size;
                    $numberingInput = $numberingData->no_cut_size;
                }
            }

            array_push($this->rapidRft, [
                'numberingInput' => $numberingInput,
                'sizeInput' => $sizeInput,
                'sizeInputText' => $sizeInputText,
                'numberingCode' => $numberingCode,
            ]);
        }
    }

    public function submitRapidInput() {
        $rapidRftFiltered = [];
        $success = 0;
        $fail = 0;

        if ($this->rapidRft && count($this->rapidRft) > 0) {

            for ($i = 0; $i < count($this->rapidRft); $i++) {
                $endlineOutputCount = EndlineOutput::where("kode_numbering", $this->rapidRft[$i]['numberingInput'])->count();

                if (($endlineOutputCount > 0) && !(RftModel::where('kode_numbering', $this->rapidRft[$i]['numberingInput'])->count() > 0 || Defect::where('kode_numbering', $this->rapidRft[$i]['numberingInput'])->count() > 0 || Reject::where('kode_numbering', $this->rapidRft[$i]['numberingInput'])->count() > 0) && ($this->orderWsDetailSizes->where('size', $this->rapidRft[$i]['sizeInputText'])->count() > 0)) {
                    array_push($rapidRftFiltered, [
                        'master_plan_id' => $this->orderInfo->id,
                        'so_det_id' => $this->rapidRft[$i]['sizeInput'],
                        'kode_numbering' => $this->rapidRft[$i]['numberingInput'],
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

        $rapidRftInsert = RftModel::insert($rapidRftFiltered);

        $this->emit('alert', 'success', $success." output berhasil terekam. ");
        $this->emit('alert', 'error', $fail." output gagal terekam.");

        $this->rapidRft = [];
        $this->rapidRftCount = 0;
    }

    public function setAndSubmitInput($scannedNumbering, $scannedSize, $scannedSizeText) {
        $this->numberingInput = $scannedNumbering;
        $this->sizeInput = $scannedSize;
        $this->sizeInputText = $scannedSizeText;

        $this->submitInput();
    }

    public function render(SessionManager $session)
    {
        $this->orderInfo = $session->get('orderInfo', $this->orderInfo);
        $this->orderWsDetailSizes = $session->get('orderWsDetailSizes', $this->orderWsDetailSizes);

        // Get total output
        $this->output = RftModel::
            where('master_plan_id', $this->orderInfo->id)->
            where('status', 'normal')->
            count();

        // Rft
        $this->rft = RftModel::
            where('master_plan_id', $this->orderInfo->id)->
            where('status', 'NORMAL')->
            whereRaw("DATE(updated_at) = '".date('Y-m-d')."'")->
            get();

        return view('livewire.rft');
    }
}
