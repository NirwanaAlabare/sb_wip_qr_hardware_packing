<div wire:init="loadReworkPage">
    <div class="loading-container-fullscreen" wire:loading wire:target='setAndSubmitInput, submitInput, submitMassRework, submitAllRework'>
        <div class="loading-container">
            <div class="loading"></div>
        </div>
    </div>
    {{-- Production Input --}}
    {{-- <div class="loading-container hidden" id="loading-rework">
        <div class="loading mx-auto"></div>
    </div> --}}
    <div class="row row-gap-3 mb-3">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center bg-rework text-light">
                    <p class="mb-0 fs-5">Scan QR</p>
                    <button class="btn btn-dark" wire:click="$emit('showModal', 'rapidRework')"><i class="fa-solid fa-layer-group"></i></button>
                </div>
                <div class="card-body" wire:ignore.self>
                    @error('numberingInput')
                        <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                            <strong>Error</strong> {{$message}}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @enderror
                    {{-- <div id="rework-reader" width="600px"></div> --}}
                    <input type="text" class="qty-input" id="scannedReworkItem" name="scannedReworkItem">
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center bg-rework text-light">
                    <p class="mb-1 fs-5">Size</p>
                    <div class="d-flex flex-wrap justify-content-md-end align-items-center gap-1">
                        <div class="d-flex align-items-center gap-3 me-3">
                            <p class="mb-1 fs-5">REWORK</p>
                            <p class="mb-1 fs-5">:</p>
                            <p id="rework-qty" class="mb-1 fs-5">{{ $rework->sum('output') }}</p>
                        </div>
                        <button class="btn btn-dark" wire:click="$emit('preSubmitUndo', 'rework')">
                            <i class="fa-regular fa-rotate-left"></i>
                        </button>
                    </div>
                </div>
                @error('sizeInput')
                    <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                        <strong>Error</strong> {{$message}}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @enderror
                <div class="card-body">
                    <div class="loading-container" wire:loading wire:target='setSizeInput'>
                        <div class="loading mx-auto"></div>
                    </div>
                    <div class="loading-container hidden" id="loading-rework">
                        <div class="loading mx-auto"></div>
                    </div>
                    <div class="row h-100 row-gap-3" id="content-rework">
                        @foreach ($orderWsDetailSizes->groupBy('size') as $key => $order)
                            <div class="col-md-4">
                                <div class="bg-rework text-white w-100 h-100 py-auto rounded-3 d-flex flex-column justify-content-center align-items-center">
                                    <p class="fs-3 mb-0">{{ $key }}</p>
                                    {{-- @if ($order->dest != "-" && $order->dest != null)
                                        <p class="fs-6 mb-0">{{ $order->dest }}</p>
                                    @endif --}}
                                    <p class="fs-5 mb-0">{{ $rework->where('size', $key)->sum('output') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="production-input row row-gap-3" {{--id="content-rework"--}}>
        <div class="col-md-12">
            <div class="card">
                <div class="card-header align-items-center bg-rework text-light">
                    <p class="mb-0 fs-5">Defect List</p>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-5 align-self-center">
                            <div class="w-100 h-100" wire:loading wire:target='loadReworkPage'>
                                <div class="loading-container">
                                    <div class="loading"></div>
                                </div>
                            </div>
                            <div class="scroll-defect-area-img" wire:loading.remove wire:target='loadReworkPage'>
                                <div class="all-defect-area-img-container">
                                    @foreach ($allDefectPosition as $defectPosition)
                                        <div class="all-defect-area-img-point" data-x="{{ floatval($defectPosition->defect_area_x) }}" data-y="{{ floatval($defectPosition->defect_area_y) }}"></div>
                                    @endforeach
                                    @if ($allDefectImage)
                                        <img src="http://10.10.5.62:8080/erp/pages/prod_new/upload_files/{{ $allDefectImage->gambar }}" class="all-defect-area-img" id="all-defect-area-img" alt="defect image">
                                    @else
                                        <img src="/assets/images/notfound.png" class="all-defect-area-img" alt="defect image">
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7 table-responsive">
                            <div class="d-flex align-items-center gap-3 my-3">
                                <button class="btn btn-rework fw-bold rounded-0 w-25 h-100 disabled d-none" wire:click="$emit('preSubmitAllRework')">Rework all</button>
                                <input type="text" class="form-control rounded-0 w-75 h-100" wire:model='allDefectListFilter' placeholder="Search defect">
                            </div>
                            <table class="table table-bordered vertical-align-center">
                                <thead>
                                    <tr>
                                        <th>Tipe</th>
                                        <th>Area</th>
                                        <th>Total</th>
                                        <th class="d-none">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($allDefectList->count() < 1)
                                        <tr>
                                            <td colspan="4" class="text-center">
                                                <div wire:loading>
                                                    <div class="loading-small"></div>
                                                </div>
                                                <div wire:loading.remove>
                                                    Defect tidak ditemukan
                                                </div>
                                            </td>
                                        </tr>
                                    @else
                                        @foreach ($allDefectList as $defectList)
                                            <tr>
                                                <td>{{ $defectList->defect_type }}</td>
                                                <td>{{ $defectList->defect_area }}</td>
                                                <td><b>{{$defectList->total}}</b></td>
                                                <td class="d-none">
                                                    <div wire:loading>
                                                        <div class="loading-small"></div>
                                                    </div>
                                                    <div wire:loading.remove>
                                                        <button class="btn btn-sm btn-rework fw-bold w-100"
                                                            wire:click="preSubmitMassRework('{{ $defectList->defect_type_id }}', '{{ $defectList->defect_area_id }}', '{{ $defectList->defect_type }}', '{{ $defectList->defect_area }}')"
                                                        >
                                                            REWORK
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                            {{ $allDefectList->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center bg-rework text-light">
                    <p class="mb-0 fs-5">Data Defect</p>
                    <div class="d-flex justify-content-end align-items-center gap-1">
                        <button type="button" class="btn btn-dark" wire:click="$emit('preSubmitUndo', 'defect')">
                            <i class="fa-regular fa-rotate-left"></i>
                        </button>
                        {{-- <button type="button" class="btn btn-dark">
                            <i class="fa-regular fa-gear"></i>
                        </button> --}}
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <div class="d-flex justify-content-center align-items-center">
                        <input type="text" class="form-control mb-3 rounded-0" id="search-defect" name="search-defect" wire:model='searchDefect' placeholder="Search here...">
                    </div>
                    <table class="table table-bordered text-center align-middle">
                        <tr>
                            <th>No.</th>
                            <th>Waktu</th>
                            <th>QR</th>
                            <th>Size</th>
                            <th>Defect Type</th>
                            <th>Defect Area</th>
                            <th>Defect Area Image</th>
                            <th>Status</th>
                            <th class="d-none">Action</th>
                        </tr>
                        @if ($defects->count() < 1)
                            <tr>
                                <td colspan='8'>Defect tidak ditemukan</td>
                            </tr>
                        @else
                            @foreach ($defects as $defect)
                                <tr>
                                    <td>{{ $defects->firstItem() + $loop->index }}</td>
                                    <td>{{ $defect->updated_at }}</td>
                                    <td>{{ $defect->kode_numbering ? $defect->kode_numbering : '-' }}</td>
                                    <td>{{ $defect->so_det_size }}</td>
                                    <td>{{ $defect->defect_type }}</td>
                                    <td>{{ $defect->defect_area }}</td>
                                    <td>
                                        <button type="button" class="btn btn-dark" wire:click="showDefectAreaImage('{{($allDefectImage ? $allDefectImage->gambar : '')}}', {{$defect->defect_area_x}}, {{$defect->defect_area_y}})'">
                                            <i class="fa-regular fa-image"></i>
                                        </button>
                                    </td>
                                    <td class="text-defect fw-bold">{{ strtoupper($defect->defect_status) }}</td>
                                    <td class="d-none">
                                        <div wire:loading>
                                            <div class="loading-small"></div>
                                        </div>
                                        <div wire:loading.remove>
                                            <button class="btn btn-sm btn-rework fw-bold w-100" wire:click="$emit('preSubmitRework', '{{ $defect->id }}', '{{ $defect->so_det_size }}', '{{ $defect->defect_type }}', '{{ $defect->defect_area }}', '{{ ($allDefectImage ? $allDefectImage->gambar : '') }}', '{{ $defect->defect_area_x }}', '{{ $defect->defect_area_y }}')">
                                                REWORK
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </table>
                    {{ $defects->links() }}
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center bg-rework text-light">
                    <p class="mb-0 fs-5">Data Rework</p>
                    <div class="d-flex justify-content-end align-items-center gap-1">
                        <button type="button" class="btn btn-dark" wire:click="$emit('preSubmitUndo', 'rework')">
                            <i class="fa-regular fa-rotate-left"></i>
                        </button>
                        {{-- <button type="button" class="btn btn-dark">
                            <i class="fa-regular fa-gear"></i>
                        </button> --}}
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <div class="d-flex justify-content-center align-items-center">
                        <input type="text" class="form-control mb-3 rounded-0" id="search-rework" name="search-rework" wire:model='searchRework' placeholder="Search here...">
                    </div>
                    <table class="table table-bordered text-center align-middle">
                        <tr>
                            <th>No.</th>
                            <th>Waktu</th>
                            <th>QR</th>
                            <th>Size</th>
                            <th>Defect Type</th>
                            <th>Defect Area</th>
                            <th>Defect Area Image</th>
                            <th>Status</th>
                            <th class="d-none">Action</th>
                        </tr>
                        @if ($reworks->count() < 1)
                            <tr>
                                <td colspan='8'>Rework tidak ditemukan</td>
                            </tr>
                        @else
                            @foreach ($reworks as $rework)
                                <tr>
                                    <td>{{ $reworks->firstItem() + $loop->index }}</td>
                                    <td>{{ $rework->updated_at }}</td>
                                    <td>{{ $rework->kode_numbering ? $rework->kode_numbering : '-' }}</td>
                                    <td>{{ $rework->so_det_size }}</td>
                                    <td>{{ $rework->defect_type}}</td>
                                    <td>{{ $rework->defect_area }}</td>
                                    <td class="text-rework fw-bold">{{ strtoupper($rework->defect_status) }}</td>
                                    <td>
                                        <button type="button" class="btn btn-dark" wire:click="showDefectAreaImage('{{($allDefectImage ? $allDefectImage->gambar : '')}}', {{$rework->defect_area_x}}, {{$rework->defect_area_y}})'">
                                            <i class="fa-regular fa-image"></i>
                                        </button>
                                    </td>
                                    <td class="d-none">
                                        <div wire:loading>
                                            <div class="loading-small"></div>
                                        </div>
                                        <div wire:loading.remove>
                                            <button class="btn btn-sm btn-defect fw-bold w-100" wire:click="$emit('preCancelRework', '{{ $rework->id }}', '{{ $rework->id }}', '{{ $rework->so_det_size }}', '{{ $rework->defect_type }}', '{{ $rework->defect_area }}', '{{($allDefectImage ? $allDefectImage->gambar : '')}}', {{$rework->defect_area_x}}, {{$rework->defect_area_y}})">CANCEL</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </table>
                    {{ $reworks->links() }}
                </div>
            </div>
        </div>
    </div>

    <div class="modal" tabindex="-1" id="mass-rework-modal" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header bg-rework">
              <h5 class="modal-title text-light fw-bold">REWORK</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="hidden" name="mass-defect-type" id="mass-defect-type" wire:model=massDefectType>
                    @error('massDefectType')
                        <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                            <small>
                                <strong>Error</strong> {{$message}}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </small>
                        </div>
                    @enderror
                    <label class="form-label">Defect Type</label>
                    <input type="text" class="form-control @error('massDefectType') is-invalid @enderror" wire:model=massDefectTypeName disabled>
                </div>
                <div class="mb-3">
                    <input type="hidden" name="mass-defect-area" id="mass-defect-area" wire:model=massDefectArea>
                    @error('massDefectArea')
                        <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                            <small>
                                <strong>Error</strong> {{$message}}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </small>
                        </div>
                    @enderror
                    <label class="form-label">Defect Area</label>
                    <input type="text" class="form-control @error('massDefectArea') is-invalid @enderror" wire:model=massDefectAreaName disabled>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="mb-3">
                            @error('massQty')
                                <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                                    <small>
                                        <strong>Error</strong> {{$message}}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </small>
                                </div>
                            @enderror
                            <label class="form-label">QTY</label>
                            <input type="number" class="form-control @error('massQty') is-invalid @enderror" name="mass-qty" id="mass-qty" value="1" wire:model=massQty>
                        </div>
                    </div>
                    <div class="col">
                        <div class="mb-3" x-data="{ sizeMass: $wire.entangle('massSize') }">
                            @error('massSize')
                                <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                                    <small>
                                        <strong>Error</strong> {{$message}}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </small>
                                </div>
                            @enderror
                            <label class="form-label">Size</label>
                            <select class="form-select @error('massSize') is-invalid @enderror" name="mass-size" id="mass-size" x-model='sizeMass'>
                                <option value="" selected disabled>Select Size</option>
                                @foreach ($massSelectedDefect as $defect)
                                    <option value="{{ $defect->so_det_id }}">{{ $defect->size }} ({{"qty : ".$defect->total}})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-rework" wire:click='submitMassRework()'>Rework</button>
                <button type="button" class="btn btn-no" data-dismiss="modal" wire:click="$emit('hideModal', 'massRework')">Batal</button>
            </div>
          </div>
        </div>
    </div>

    {{-- Rapid Rework --}}
    <div class="modal" tabindex="-1" id="rapid-rework-modal" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-rework text-light">
                    <h5 class="modal-title"><i class="fa-solid fa-clone"></i> Rework Rapid Scan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <p class="text-center">Scanned Item : <b>{{ $rapidReworkCount }}</b></p>
                        <input type="text" class="qty-input" id="rapid-rework-input">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-sb-secondary" data-bs-dismiss="modal" wire:click='submitRapidInput'>Selesai</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        // Scan QR
        // if (document.getElementById("rework-reader")) {
        //     function onScanSuccess(decodedText, decodedResult) {
        //         // handle the scanned code as you like, for example:
        //         console.log(`Code matched = ${decodedText}`, decodedResult);

        //         // break decoded text
        //         let breakDecodedText = decodedText.split('-');

        //         console.log(breakDecodedText);

        //         // set kode_numbering
        //         @this.numberingInput = breakDecodedText[3];

        //         // set so_det_id
        //         @this.sizeInput = breakDecodedText[4];

        //         // set size
        //         @this.sizeInputText = breakDecodedText[5];

        //         // submit
        //         @this.submitInput();

        //         clearReworkScan();
        //     }

        //     Livewire.on('renderQrScanner', async (type) => {
        //         if (type == 'rework') {
        //             document.getElementById('back-button').disabled = true;
        //             await refreshReworkScan(onScanSuccess);
        //             document.getElementById('back-button').disabled = false;
        //         }
        //     });

        //     Livewire.on('toInputPanel', async (type) => {
        //         if (type == 'rework') {
        //             document.getElementById('back-button').disabled = true;
        //             await @this.updateOutput();
        //             await initReworkScan(onScanSuccess);
        //             document.getElementById('back-button').disabled = false;
        //         }
        //     });

        //     Livewire.on('fromInputPanel', () => {
        //         clearReworkScan();
        //     });
        // }

        var scannedReworkItemInput = document.getElementById("scannedReworkItem");

        scannedReworkItemInput.addEventListener("change", async function () {
            const value = this.value;

            this.setAttribute("disabled", true);

            // submit
            await @this.submitInput(value);

            this.removeAttribute("disabled");
            this.value = '';
        });

        var scannedRapidReworkInput = document.getElementById("rapid-rework-input");

        scannedRapidReworkInput.addEventListener("change", function () {
            @this.pushRapidRework(this.value, null, null);

            this.value = '';
        });

        Livewire.on('qrInputFocus', async (type) => {
            if (type == 'rework') {
                scannedReworkItemInput.focus();
            }
        });

        Livewire.on('toInputPanel', async (type) => {
            if (type == 'rework') {
                @this.updateOutput();
                scannedReworkItemInput.focus();
            }
        });

        // Livewire.on('fromInputPanel', () => {
        //     clearReworkScan();
        // });
    </script>
@endpush
