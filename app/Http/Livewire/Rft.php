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
use App\Models\Nds\OutputPacking;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use DB;

class Rft extends Component
{
    public $orderInfo;
    public $orderWsDetailSizes;
    public $sizeInput;
    public $sizeInputText;
    public $noCutInput;
    public $numberingInput;
    public $rapidRft;
    public $rapidRftCount;
    public $rft;

    protected $rules = [
        'sizeInput' => 'required',
        'noCutInput' => 'required',
        'numberingInput' => 'required',
    ];

    protected $messages = [
        'sizeInput.required' => 'Harap scan qr.',
        'noCutInput.required' => 'Harap scan qr.',
        'numberingInput.required' => 'Harap scan qr.'
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
        $this->sizeInput = null;
        $this->sizeInputText = null;
        $this->noCutInput = null;
        $this->numberingInput = null;
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

    private function checkIfNumberingExists($numberingInput = null): bool
    {
        if (DB::table('output_rfts_packing')->where('kode_numbering', ($numberingInput ?? $this->numberingInput))->exists()) {
            $this->addError('numberingInput', 'Kode QR sudah discan di RFT.');
            return true;
        }

        if (DB::table('output_defects_packing')->where('kode_numbering', ($numberingInput ?? $this->numberingInput))->exists()) {
            $this->addError('numberingInput', 'Kode QR sudah discan di Defect.');
            return true;
        }

        if (DB::table('output_rejects_packing')->where('kode_numbering', ($numberingInput ?? $this->numberingInput))->exists()) {
            $this->addError('numberingInput', 'Kode QR sudah discan di Reject.');
            return true;
        }

        return false;
    }

    public function updateWsDetailSizes($panel)
    {
        $this->sizeInput = null;
        $this->sizeInputText = null;
        $this->noCutInput = null;
        $this->numberingInput = null;

        $this->orderInfo = session()->get('orderInfo', $this->orderInfo);
        $this->orderWsDetailSizes = session()->get('orderWsDetailSizes', $this->orderWsDetailSizes);
        $this->selectedColor = $this->orderInfo->id;
        $this->selectedColorName = $this->orderInfo->color;

        $this->emit('setSelectedSizeSelect2', $this->selectedColor);

        if ($panel == 'rft') {
            $this->emit('qrInputFocus', 'rft');
        }
    }

    public function updateOutput()
    {
        $this->rft = DB::connection('mysql_sb')->table('output_rfts_packing')->
            leftJoin("so_det", "so_det.id", "=", "output_rfts_packing.so_det_id")->
            where('master_plan_id', $this->orderInfo->id)->
            where('status', 'NORMAL')->
            get();
    }

    public function clearInput()
    {
        $this->sizeInput = null;
    }

    public function submitInput($value)
    {
        ini_set('memory_limit', '2048M');

        $this->emit('qrInputFocus', 'rft');

        $numberingInput = $value;

        if ($numberingInput) {
            // if (str_contains($numberingInput, 'WIP')) {
            //     $numberingData = DB::connection("mysql_nds")->table("stocker_numbering")->where("kode", $numberingInput)->first();
            // } else {
            //     $numberingCodes = explode('_', $numberingInput);

            //     if (count($numberingCodes) > 2) {
            //         $numberingInput = substr($numberingCodes[0],0,4)."_".$numberingCodes[1]."_".$numberingCodes[2];
            //         $numberingData = DB::connection("mysql_nds")->table("year_sequence")->selectRaw("year_sequence.*, year_sequence.id_year_sequence no_cut_size")->where("id_year_sequence", $numberingInput)->first();
            //     } else {
            //         $numberingData = DB::connection("mysql_nds")->table("month_count")->selectRaw("month_count.*, month_count.id_month_year no_cut_size")->where("id_month_year", $numberingInput)->first();
            //     }
            // }

            // One Straight Format
            $numberingData = DB::connection("mysql_nds")->table("year_sequence")->selectRaw("year_sequence.*, year_sequence.id_year_sequence no_cut_size")->where("id_year_sequence", $numberingInput)->first();

            if ($numberingData) {
                $this->sizeInput = $numberingData->so_det_id;
                $this->sizeInputText = $numberingData->size;
                $this->noCutInput = $numberingData->no_cut_size;
                $this->numberingInput = $numberingInput;

                if (!$this->sizeInput) {
                    return $this->emit('alert', 'error', "QR belum terdaftar.");
                }

                $validatedData = $this->validate();

                if ($this->checkIfNumberingExists($numberingInput)) {
                    return;
                }

                $endlineOutputData = DB::connection('mysql_sb')->table('output_rfts')->where("kode_numbering", $numberingInput)->first();
                // $endlineOutputData = true;

                if ($endlineOutputData) {
                    $currentData = $this->orderWsDetailSizes->where('so_det_id', $this->sizeInput)->first();
                    if ($currentData && $this->orderInfo && (trim($currentData['color']) == trim($this->orderInfo->color))) {
                        $insertRft = RftModel::create([
                            'master_plan_id' => $this->orderInfo->id,
                            'so_det_id' => $this->sizeInput,
                            'no_cut_size' => $this->noCutInput,
                            'kode_numbering' => $numberingInput,
                            'status' => 'NORMAL',
                            'created_by' => Auth::user()->username,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ]);

                        $rftNds = OutputPacking::where("kode_numbering", $numberingInput)->first();
                        if (!$rftNds) {
                            $insertRftNds = OutputPacking::create([
                                'sewing_line' => $this->orderInfo->sewing_line,
                                'master_plan_id' => $this->orderInfo->id,
                                'so_det_id' => $this->sizeInput,
                                'no_cut_size' => $this->noCutInput,
                                'kode_numbering' => $numberingInput,
                                'status' => 'NORMAL',
                                'created_by' => Auth::user()->username,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now()
                            ]);
                        }

                        if ($insertRft) {
                            $this->emit('alert', 'success', "1 output berukuran ".$this->sizeInputText." berhasil terekam.");

                            $this->sizeInput = '';
                            $this->sizeInputText = '';
                            $this->noCutInput = '';
                            $this->numberingInput = '';
                        } else {
                            $this->emit('alert', 'error', "Terjadi kesalahan. Output tidak berhasil direkam.");
                        }
                    } else {
                        $this->emit('alert', 'error', "Terjadi kesalahan. QR tidak sesuai.");
                    }
                } else {
                    $this->emit('alert', 'error', "Output dari <b>QC</b> tidak ditemukan.");
                }
            } else {
                $this->emit('alert', 'error', "Terjadi kesalahan. QR tidak sesuai.");
            }
        } else {
            $this->emit('alert', 'error', "Terjadi kesalahan. QR tidak sesuai.");
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
                array_push($this->rapidRft, [
                    'numberingInput' => $numberingInput,
                ]);
            }
        }
    }

    public function submitRapidInput() {
        ini_set('memory_limit', '2048M');

        $rapidRftFiltered = [];
        $rapidRftFilteredNds = [];
        $success = 0;
        $fail = 0;

        if ($this->rapidRft && count($this->rapidRft) > 0) {

            for ($i = 0; $i < count($this->rapidRft); $i++) {
                // if (str_contains($this->rapidRft[$i]['numberingInput'], 'WIP')) {
                //     $numberingData = DB::connection("mysql_nds")->table("stocker_numbering")->where("kode", $this->rapidRft[$i]['numberingInput'])->first();
                // } else {
                //     $numberingCodes = explode('_', $this->rapidRft[$i]['numberingInput']);

                //     if (count($numberingCodes) > 2) {
                //         $this->rapidRft[$i]['numberingInput'] = substr($numberingCodes[0],0,4)."_".$numberingCodes[1]."_".$numberingCodes[2];
                //         $numberingData = DB::connection("mysql_nds")->table("year_sequence")->selectRaw("year_sequence.*, year_sequence.id_year_sequence no_cut_size")->where("id_year_sequence", $this->rapidRft[$i]['numberingInput'])->first();
                //     } else {
                //         $numberingData = DB::connection("mysql_nds")->table("month_count")->selectRaw("month_count.*, month_count.id_month_year no_cut_size")->where("id_month_year", $this->rapidRft[$i]['numberingInput'])->first();
                //     }
                // }

                // One Straight Format
                $numberingData = DB::connection("mysql_nds")->table("year_sequence")->selectRaw("year_sequence.*, year_sequence.id_year_sequence no_cut_size")->where("id_year_sequence", $this->rapidRft[$i]['numberingInput'])->first();

                $endlineOutputCount = DB::connection('mysql_sb')->table('output_rfts')->where("kode_numbering", $this->rapidRft[$i]['numberingInput'])->count();

                if (($endlineOutputCount > 0) && ((DB::connection('mysql_sb')->table('output_rfts_packing')->where('kode_numbering', $this->rapidRft[$i]['numberingInput'])->count() + DB::connection('mysql_sb')->table('output_defects_packing')->where('kode_numbering', $this->rapidRft[$i]['numberingInput'])->count() + DB::connection('mysql_sb')->table('output_rejects_packing')->where('kode_numbering', $this->rapidRft[$i]['numberingInput'])->count()) < 1) && ($this->orderWsDetailSizes->where('so_det_id', $numberingData->so_det_id)->count() > 0)) {
                    array_push($rapidRftFiltered, [
                        'master_plan_id' => $this->orderInfo->id,
                        'so_det_id' => $numberingData->so_det_id,
                        'no_cut_size' => $numberingData->no_cut_size,
                        'kode_numbering' => $this->rapidRft[$i]['numberingInput'],
                        'status' => 'NORMAL',
                        'created_by' => Auth::user()->username,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);

                    array_push($rapidRftFilteredNds, [
                        'sewing_line' => $this->orderInfo->sewing_line,
                        'master_plan_id' => $this->orderInfo->id,
                        'so_det_id' => $numberingData->so_det_id,
                        'no_cut_size' => $numberingData->no_cut_size,
                        'kode_numbering' => $this->rapidRft[$i]['numberingInput'],
                        'status' => 'NORMAL',
                        'created_by' => Auth::user()->username,
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
        $rapidRftInsertNds = OutputPacking::insert($rapidRftFilteredNds);

        $this->emit('alert', 'success', $success." output berhasil terekam. ");
        $this->emit('alert', 'error', $fail." output gagal terekam.");

        $this->rapidRft = [];
        $this->rapidRftCount = 0;
    }

    public function setAndSubmitInput($scannedNumbering, $scannedSize, $scannedSizeText) {
        ini_set('memory_limit', '2048M');

        $this->numberingInput = $scannedNumbering;
        $this->sizeInput = $scannedSize;
        $this->sizeInputText = $scannedSizeText;

        $this->submitInput();
    }

    public function render(SessionManager $session)
    {
        // if (isset($this->errorBag->messages()['numberingInput']) && collect($this->errorBag->messages()['numberingInput'])->contains(function ($message) {return Str::contains($message, 'Kode QR sudah discan');})) {
        //     foreach ($this->errorBag->messages()['numberingInput'] as $message) {
        //         $this->emit('alert', 'warning', $message);
        //     }
        // } else if ((isset($this->errorBag->messages()['numberingInput']) && collect($this->errorBag->messages()['numberingInput'])->contains("Harap scan qr.")) || (isset($this->errorBag->messages()['sizeInput']) && collect($this->errorBag->messages()['sizeInput'])->contains("Harap scan qr."))) {
        //     $this->emit('alert', 'error', "Harap scan QR.");
        // }
        if (isset($this->errorBag->messages()['numberingInput'])) {
            foreach ($this->errorBag->messages()['numberingInput'] as $message) {
                $this->emit('alert', 'error', $message);
            }
        }

        $this->orderInfo = $session->get('orderInfo', $this->orderInfo);
        $this->orderWsDetailSizes = $session->get('orderWsDetailSizes', $this->orderWsDetailSizes);

        $this->selectedColor = $this->orderInfo->id;
        $this->selectedColorName = $this->orderInfo->color;

        $this->emit('setSelectedSizeSelect2', $this->selectedColor);

        // Rft
        $this->rft = collect(DB::select("select output_rfts_packing.*, so_det.size, COUNT(output_rfts_packing.id) output from `output_rfts_packing` left join `so_det` on `so_det`.`id` = `output_rfts_packing`.`so_det_id` where `master_plan_id` = '".$this->orderInfo->id."' and `status` = 'NORMAL' group by so_det.id"));

        return view('livewire.rft');
    }
}
