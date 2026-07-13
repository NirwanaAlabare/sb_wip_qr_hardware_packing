<div>
    <div class="loading-container-fullscreen" wire:loading wire:target="selectDefectAreaPosition, preSubmitInput, submitInput, updateOrder">
        <div class="loading-container">
            <div class="loading"></div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="card h-100">

            <div class="card-header d-flex justify-content-between align-items-center bg-defect text-light">
                <p class="mb-0 fs-5">DEFECT</p>
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

                <div class="card-header d-flex justify-content-between align-items-center bg-defect text-light">
                    <p class="mb-0 fs-5">WAITING LIST</p>

                    <span class="badge bg-light text-dark fs-6">
                        {{ $summary->total() }}
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
                                </tr>
                            </thead>
                            <tbody>
                                @if ($summary->count() < 1)
                                    <tr>
                                        <td colspan='5'>Waiting tidak ditemukan</td>
                                    </tr>
                                @else
                                    @foreach ($summary as $row)
                                        <tr>
                                            <td>{{ $row->created_at }}</td>
                                            <td>{{ $row->kode_numbering }}</td>
                                            <td>{{ $row->po }}</td>
                                            <td>{{ $row->line_qc_finishing }}</td>
                                            <td>{{ $row->masterplan }}</td>
                                            <td>{{ $row->size }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-2">
                        {{ $summary->links() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
    
                <div class="card-header d-flex justify-content-between align-items-center bg-defect text-light">
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
                                        <td colspan='10'>Defect tidak ditemukan</td>
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
    </div>


    {{-- Defect Modal --}}
    <div class="modal" tabindex="-1" id="defect-modal" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-defect text-light">
                    <h5 class="modal-title">DEFECT</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        {{-- <div class="mb-3">
                            @error('productType')
                                <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                                    <small>
                                        <strong>Error</strong> {{$message}}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </small>
                                </div>
                            @enderror
                            <div class="d-flex align-items-center mb-1">
                                <button type="button" class="btn btn-sm btn-light rounded-0 me-1" wire:click="$emit('showModal', 'addProductType')">
                                    <i class="fa-regular fa-plus fa-xs"></i>
                                </button>
                                <label class="form-label me-1 mb-0">Product Type</label>
                            </div>
                            <div wire:ignore id="select-product-type-container">
                                <select class="form-select @error('productType') is-invalid @enderror" id="product-type-select2" wire:model='productType'>
                                    <option value="" selected>Select product type</option>
                                    @foreach ($productTypes as $product)
                                        <option value="{{ $product->id }}">
                                            {{ $product->product_type }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div> --}}
                        <div class="mb-3">
                            @error('defectType')
                                <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                                    <small>
                                        <strong>Error</strong> {{$message}}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </small>
                                </div>
                            @enderror
                            <div class="d-flex align-items-center mb-1">
                                <button type="button" class="btn btn-sm btn-light rounded-0 me-1" wire:click="$emit('showModal', 'addDefectType')">
                                    <i class="fa-regular fa-plus fa-xs"></i>
                                </button>
                                <label class="form-label me-1 mb-0">Defect Type</label>
                            </div>
                            <div wire:ignore id="select-defect-type-container">
                                <select class="form-select @error('defectType') is-invalid @enderror" id="defect-type-select2" wire:model='defectType'>
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
                            @error('defectArea')
                                <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                                    <small>
                                        <strong>Error</strong> {{$message}}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </small>
                                </div>
                            @enderror
                            <div class="d-flex align-items-center mb-1">
                                <button type="button" class="btn btn-sm btn-light rounded-0 me-1" wire:click="$emit('showModal', 'addDefectArea')">
                                    <i class="fa-regular fa-plus fa-xs"></i>
                                </button>
                                <label class="form-label me-1 mb-0">Defect Area</label>
                            </div>
                            <div class="d-flex gap-1">
                                <div class="w-75" wire:ignore id="select-defect-area-container">
                                    <select class="form-select @error('defectArea') is-invalid @enderror" id="defect-area-select2" wire:model='defectArea'>
                                        <option value="" selected>Select defect area</option>
                                        @foreach ($defectAreas as $defect)
                                            <option value="{{ $defect->id }}">
                                                {{ $defect->defect_area }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="w-25">
                                    <button type="button" wire:click="selectDefectAreaPosition" class="btn btn-dark w-100">
                                        <i class="fa-regular fa-image"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            @if ($errors->has('defectAreaPositionX') || $errors->has('defectAreaPositionY'))
                                <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                                    <small>
                                        <strong>Error</strong> Harap tentukan posisi defect area dengan mengklik tombol <button type="button"class="btn btn-dark btn-sm"><i class="fa-regular fa-image fa-2xs"></i></button> di samping 'select defect area'.
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </small>
                                </div>
                            @endif
                            <div class="d-none">
                                <label class="form-label me-1 mb-2">Defect Area Position</label>
                                <div class="row">
                                    <div class="col d-flex justify-content-center align-items-center">
                                        <label class="form-label me-1 mb-0">X </label>
                                        <div class="d-flex">
                                            <input class="form-control @error('defectAreaPositionX') is-invalid @enderror" id="defect-area-position-x-livewire" wire:model='defectAreaPositionX' readonly>
                                        </div>
                                    </div>
                                    <div class="col d-flex justify-content-center align-items-center">
                                        <label class="form-label me-1 mb-1">Y </label>
                                        <div class="d-flex">
                                            <input class="form-control @error('defectAreaPositionY') is-invalid @enderror" id="defect-area-position-x-livewire" wire:model='defectAreaPositionY' readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-sb-secondary" wire:click='submitInput'>Selesai</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Product Type --}}
    <div class="modal" tabindex="-1" id="product-type-modal" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-defect text-light">
                    <h5 class="modal-title">TAMBAH PRODUCT TYPE</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            @error('defectAreaAdd')
                                <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                                    <small>
                                        <strong>Error</strong> {{$message}}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </small>
                                </div>
                            @enderror
                            <label class="form-label me-1 mb-0">Product Type</label>
                            <input type="text" class="form-control" name="product-type-add" id="product-type-add" wire:model='productTypeAdd'>
                        </div>
                        <div class="mb-3">
                            @error('productTypeImageAdd')
                                <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                                    <small>
                                        <strong>Error</strong> {{$message}}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </small>
                                </div>
                            @enderror
                            <label class="form-label me-1 mb-0">Product Type Image</label>
                            <input type="file" class="form-control" name="product-type-image-add" id="product-type-image-add" style="border-radius: 5px 5px 0 0;" wire:model='productTypeImageAdd'>
                            <div class="d-flex justify-content-center border" style="border-radius: 0 0 5px 5px;">
                                @if ($productTypeImageAdd)
                                    <img src="{{ $productTypeImageAdd->temporaryUrl() }}" class="img-fluid">
                                @else
                                    <p class="text-center mb-1">*Preview Gambar*</p>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-sb-secondary" wire:click='submitProductType'>Tambahkan</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Defect Type --}}
    <div class="modal" id="defect-type-modal" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-defect text-light">
                    <h5 class="modal-title">TAMBAH DEFECT TYPE</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            @error('defectTypeAdd')
                                <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                                    <small>
                                        <strong>Error</strong> {{$message}}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </small>
                                </div>
                            @enderror
                            <label class="form-label me-1 mb-0">Defect Type</label>
                            <input type="text" class="form-control" name="defect-type-add" id="defect-type-add" wire:model='defectTypeAdd'>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-sb-secondary" wire:click='submitDefectType'>Tambahkan</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Defect Area --}}
    <div class="modal" tabindex="-1" id="defect-area-modal" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-defect text-light">
                    <h5 class="modal-title">TAMBAH DEFECT AREA</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            @error('defectAreaAdd')
                                <div class="alert alert-danger alert-dismissible fade show mb-0 rounded-0" role="alert">
                                    <small>
                                        <strong>Error</strong> {{$message}}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </small>
                                </div>
                            @enderror
                            <label class="form-label me-1 mb-0">Defect Area</label>
                            <input type="text" class="form-control" name="defect-area-add" id="defect-area-add" wire:model='defectAreaAdd'>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-sb-secondary" wire:click='submitDefectArea'>Tambahkan</button>
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
            // Product Type
            $("#scannedItemReturn").focus();

            Livewire.on('showModal', function (modal) {
                $('#' + modal + '-modal').modal('show');
            });

            Livewire.on('hideModal', function (modal) {
                $('#' + modal + '-modal').modal('hide');
                setTimeout(() => {
                    document.getElementById('scannedItemReturn').focus();
                }, 500);
            });

            $('#product-type-select2').select2({
                theme: "bootstrap-5",
                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                placeholder: $( this ).data( 'placeholder' ),
                dropdownParent: $('#defect-modal .modal-content #select-product-type-container')
            });

            $('#product-type-select2').on('change', function (e) {
                var productType = $('#product-type-select2').select2("val");
                @this.set('productType', productType);
            });

            // Defect Type
            $('#defect-type-select2').select2({
                theme: "bootstrap-5",
                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                placeholder: $( this ).data( 'placeholder' ),
                dropdownParent: $('#defect-modal .modal-content #select-defect-type-container')
            });

            $('#defect-type-select2').on('change', function (e) {
                var defectType = $('#defect-type-select2').select2("val");
                @this.set('defectType', defectType);
            });

            // Defect Area
            $('#defect-area-select2').select2({
                theme: "bootstrap-5",
                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                placeholder: $( this ).data( 'placeholder' ),
                dropdownParent: $('#defect-modal .modal-content #select-defect-area-container')
            });

            $('#defect-area-select2').on('change', function (e) {
                var defectArea = $('#defect-area-select2').select2("val");
                @this.set('defectArea', defectArea);
            });

            Livewire.on('clearSelectDefectAreaPoint', () => {
                $('#product-type-select2').val("").trigger('change');
                $('#defect-type-select2').val("").trigger('change');
                $('#defect-area-select2').val("").trigger('change');
            });

            document.getElementById('defect-input').addEventListener("keyup", async (event) => {
                if (event.key === 'Enter' || event.keyCode === 13) {
                    await @this.preSubmitInput();
                    let el = document.querySelector( ':focus' );
                    if( el ) el.blur();
                }
            });
        })

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
                url: "{{ route('get-scanned-item-return-defect') }}",
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
