<div wire:init="loadRejectPage">
    <div class="loading-container-fullscreen" wire:loading wire:target="selectRejectAreaPosition, preSubmitInput, submitInput, updateOrder">
        <div class="loading-container">
            <div class="loading"></div>
        </div>
    </div>

    <div class="col-md-12">
        <div class="card h-100">

            <div class="card-header d-flex justify-content-between align-items-center bg-reject text-light">
                <p class="mb-0 fs-5">REJECT</p>
            </div>

            <div class="production-input row g-4 py-3 px-2">
                <div class="col-md-4">
                    <div class="card h-100">
                        <div class="card-body p-4" wire:ignore.self>
                            @error('numberingInput')
                                <div class="alert alert-danger alert-dismissible fade show mb-3 rounded-0" role="alert">
                                    <strong>Error</strong> {{$message}}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @enderror

                            <input type="text" class="qty-input w-100" style="height: 250px;;" id="scannedItemReturn">
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card h-100">
                        <div class="card-body p-4">
                            <div class="row g-3">

                                <div class="col-md-6">
                                    <label class="form-label">Kode QR</label>
                                    <input type="text" class="form-control" wire:model="kode_qr" readonly>

                                    <div class="invalid-feedback d-block">
                                        @error('kode_qr') {{ $message }} @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">PO</label>
                                    <input type="text" class="form-control" wire:model="po" readonly>
                                    <div class="invalid-feedback d-block">
                                        @error('po') {{ $message }} @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Worksheet Style</label>
                                    <input type="text" class="form-control" wire:model="worksheet_style" readonly>
                                    
                                    <div class="invalid-feedback d-block">
                                        @error('worksheet_style') {{ $message }} @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Color</label>
                                    <input type="text" class="form-control" wire:model="color" readonly>

                                    <div class="invalid-feedback d-block">
                                        @error('color') {{ $message }} @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Size</label>
                                    <input type="text" class="form-control" wire:model="size" readonly>

                                    <div class="invalid-feedback d-block">
                                        @error('size') {{ $message }} @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Packing Line</label>
                                    <input type="text" class="form-control" wire:model="packing_line" readonly>

                                    <div class="invalid-feedback d-block">
                                        @error('packing_line') {{ $message }} @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center bg-reject text-light">
                    <p class="mb-0 fs-5">DEFECT LIST</p>
                    <span class="badge bg-light text-dark fs-6">
                        {{ $summaryDefect->total() }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-center align-middle">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Kode QR</th>
                                    <th>PO</th>
                                    <th>Line</th>
                                    <th>Masterplan</th>
                                    <th>Size</th>
                                    <th>Defect Type</th>
                                    <th>Defect Area</th>
                                    <th>Gambar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($summaryDefect->count() < 1)
                                    <tr>
                                        <td colspan='9'>Defect tidak ditemukan</td>
                                    </tr>
                                @else
                                    @foreach ($summaryDefect as $row)
                                        <tr>
                                            <td>{{ $row->updated_at }}</td>
                                            <td>{{ $row->kode_numbering }}</td>
                                            <td>{{ $row->po }}</td>
                                            <td>{{ $row->line_qc_finishing }}</td>
                                            <td>{{ $row->masterplan }}</td>
                                            <td>{{ $row->size }}</td>
                                            <td>{{ $row->defect_type }}</td>
                                            <td>{{ $row->defect_area }}</td>
                                            <td>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-dark"
                                                    wire:click="showDefectAreaImage('{{ $row->gambar }}', {{ $row->defect_area_x }}, {{ $row->defect_area_y }})">
                                                    <i class="fa-regular fa-image"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-2">
                        {{ $summaryDefect->links() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center bg-reject text-light">
                    <p class="mb-0 fs-5">REJECT LIST</p>
                    <span class="badge bg-light text-dark fs-6">
                        {{ $summaryReject->total() }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered text-center align-middle">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Kode QR</th>
                                    <th>PO</th>
                                    <th>Line</th>
                                    <th>Masterplan</th>
                                    <th>Size</th>
                                    <th>Defect Type</th>
                                    <th>Defect Area</th>
                                    <th>Gambar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($summaryReject->count() < 1)
                                    <tr>
                                        <td colspan='9'>Reject tidak ditemukan</td>
                                    </tr>
                                @else
                                    @foreach ($summaryReject as $row)
                                        <tr>
                                            <td>{{ $row->updated_at }}</td>
                                            <td>{{ $row->kode_numbering }}</td>
                                            <td>{{ $row->po }}</td>
                                            <td>{{ $row->line_qc_finishing }}</td>
                                            <td>{{ $row->masterplan }}</td>
                                            <td>{{ $row->size }}</td>
                                            <td>{{ $row->defect_type }}</td>
                                            <td>{{ $row->defect_area }}</td>
                                            <td>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-dark"
                                                    wire:click="showDefectAreaImage('{{ $row->gambar }}', {{ $row->defect_area_x }}, {{ $row->defect_area_y }})">
                                                    <i class="fa-regular fa-image"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-2">
                        {{ $summaryReject->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Reject Modal --}}
    <div class="modal" tabindex="-1" id="reject-modal" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-reject text-light">
                    <h5 class="modal-title">REJECT</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            @error('rejectType')
                                <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                                    <small>
                                        <strong>Error</strong> {{$message}}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </small>
                                </div>
                            @enderror
                            <div class="d-flex align-items-center mb-1">
                                <label class="form-label me-1 mb-0">Reject Type</label>
                            </div>
                            <div wire:ignore id="select-reject-type-container">
                                <select class="form-select @error('rejectType') is-invalid @enderror" id="reject-type-select2" wire:model='rejectType'>
                                    <option value="" selected>Select defect type</option>
                                    @foreach ($defectTypes as $defect)
                                        <option value="{{ $defect->id }}">
                                            {{ $defect->defect_type }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            @error('rejectArea')
                                <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                                    <small>
                                        <strong>Error</strong> {{$message}}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </small>
                                </div>
                            @enderror
                            <div class="d-flex align-items-center mb-1">
                                <label class="form-label me-1 mb-0">Reject Area</label>
                            </div>
                            <div class="d-flex gap-1">
                                <div class="w-75" wire:ignore id="select-reject-area-container">
                                    <select class="form-select @error('rejectArea') is-invalid @enderror" id="reject-area-select2" wire:model='rejectArea'>
                                        <option value="" selected>Select defect area</option>
                                        @foreach ($defectAreas as $defect)
                                            <option value="{{ $defect->id }}">
                                                {{ $defect->defect_area }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="w-25">
                                    <button type="button" wire:click="selectRejectAreaPosition" class="btn btn-dark w-100">
                                        <i class="fa-regular fa-image"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            @if ($errors->has('rejectAreaPositionX') || $errors->has('rejectAreaPositionY'))
                                <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                                    <small>
                                        <strong>Error</strong> Harap tentukan posisi reject area dengan mengklik tombol <button type="button"class="btn btn-dark btn-sm"><i class="fa-regular fa-image fa-2xs"></i></button> di samping 'select defect area'.
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </small>
                                </div>
                            @endif
                            <div class="d-none">
                                <label class="form-label me-1 mb-2">Reject Area Position</label>
                                <div class="row">
                                    <div class="col d-flex justify-content-center align-items-center">
                                        <label class="form-label me-1 mb-0">X </label>
                                        <div class="d-flex">
                                            <input class="form-control @error('rejectAreaPositionX') is-invalid @enderror" id="reject-area-position-x-livewire" wire:model='rejectAreaPositionX' readonly>
                                        </div>
                                    </div>
                                    <div class="col d-flex justify-content-center align-items-center">
                                        <label class="form-label me-1 mb-1">Y </label>
                                        <div class="d-flex">
                                            <input class="form-control @error('rejectAreaPositionY') is-invalid @enderror" id="reject-area-position-y-livewire" wire:model='rejectAreaPositionY' readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" wire:click='submitInput'>Selesai</button>
                </div>
            </div>
        </div>
    </div>
</div>

<a href="{{ url()->previous() }}" class="back bg-sb-secondary text-light text-center w-auto">
    <i class="fa-regular fa-reply"></i>
</a>

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {

            $("#scannedItemReturn").focus();

            // Defect Type
            $('#reject-type-select2').select2({
                theme: "bootstrap-5",
                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                placeholder: $( this ).data( 'placeholder' ),
                dropdownParent: $('#reject-modal .modal-content #select-reject-type-container')
            });

            $('#reject-type-select2').on('change', function (e) {
                var rejectType = $('#reject-type-select2').select2("val");
                @this.set('rejectType', rejectType);
            });

            // Defect Area
            $('#reject-area-select2').select2({
                theme: "bootstrap-5",
                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                placeholder: $( this ).data( 'placeholder' ),
                dropdownParent: $('#reject-modal .modal-content #select-reject-area-container')
            });

            $('#reject-area-select2').on('change', function (e) {
                var rejectArea = $('#reject-area-select2').select2("val");
                @this.set('rejectArea', rejectArea);
            });

            $(document).on('select2:open', () => {
                document.querySelector('.select2-search__field').focus();
            });

            Livewire.on('clearSelectRejectAreaPoint', () => {
                $('#reject-type-select2').val("").trigger('change');
                $('#reject-area-select2').val("").trigger('change');
            });

            Livewire.on('hideModal', function (modal) {
                $('#' + modal + '-modal').modal('hide');

                setTimeout(() => {
                    const input = document.getElementById('scannedItemReturn');

                    if (input) {
                        input.focus();
                        input.select();
                    }
                }, 500);
            });

            document.getElementById('reject-input').addEventListener("keyup", async (event) => {
                if (event.key === 'Enter' || event.keyCode === 13) {
                    await @this.preSubmitInput();
                    let el = document.querySelector( ':focus' );
                    if( el ) el.blur();
                }
            });
        })

        Livewire.on('setRejectData', (data) => {
            setTimeout(() => {
                $('#reject-type-select2').val(data.type).trigger('change');
                $('#reject-area-select2').val(data.area).trigger('change');

                $('#reject-area-position-x-livewire').val(data.x);
                $('#reject-area-position-y-livewire').val(data.y);
            }, 100);
        });

        $('#scannedItemReturn').on('keydown', function(e) {
            if (e.keyCode === 13) {
                e.preventDefault();

                let id = $(this).val();
                getScannedItem(id);

                $(this).val('');
            }
        });

        function getScannedItem(id) {
            $.ajax({
                url: "{{ route('get-scanned-item-return-reject') }}",
                type: "GET",
                data: {
                    id: id
                },
                success: function(response) {

                    $('#scannedItemReturn').val('');

                    if (response) {

                        @this.set('kode_qr', response.kode_numbering);
                        @this.set('po', response.po);
                        @this.set('worksheet_style', response.kpno + ' - ' + response.style);
                        @this.set('color', response.color);
                        @this.set('size', response.size);
                        @this.set('packing_line', response.packing_line);

                        @this.call(
                            'openDefectModal',
                            response.output_rfts_packing_po_return_id,
                            response.id,
                            response.master_plan_id,
                            response.so_det_id
                        );
                    }
                },
                error: function(xhr) {
                    iziToast.warning({
                        title: 'Warning!',
                        message: xhr.responseJSON?.message,
                        position: 'topCenter',
                        transitionIn: 'slideInRight',
                        timeout: 2000
                    });
                }
            });
        }
    </script>
@endpush