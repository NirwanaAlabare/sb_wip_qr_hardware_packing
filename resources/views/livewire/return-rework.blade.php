@push('styles')
    <style>
        #product-po + .select2-container .select2-selection__placeholder {
            color: #000 !important;
        }
    </style>
@endpush

<div class="p-3">
    <div class="col-md-12">
        <div class="card h-100">

            <div class="card-header d-flex justify-content-between align-items-center bg-rework text-light">
                <p class="mb-0 fs-5">REWORK</p>
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

                        <div class="row justify-content-center pb-4">
                            <div class="col-md-6">
                                <button type="button"
                                        class="btn btn-success w-100"
                                        wire:click="save">
                                    Simpan
                                </button>
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
    
                <div class="card-header d-flex justify-content-between align-items-center bg-rework text-light">
                    <p class="mb-0 fs-5">DEFECT LIST</p>
    
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
                                    <th>Defect Type</th>
                                    <th>Defect Area</th>
                                    <th>Gambar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($summary->count() < 1)
                                    <tr>
                                        <td colspan='9'>Defect tidak ditemukan</td>
                                    </tr>
                                @else
                                    @foreach ($summary as $row)
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
                        {{ $summary->links() }}
                    </div>
                </div>
            </div>
        </div>
    
        <div class="col-md-6">
            <div class="card h-100">
    
                <div class="card-header d-flex justify-content-between align-items-center bg-rework text-light">
                    <p class="mb-0 fs-5">REWORK LIST</p>
    
                    <span class="badge bg-light text-dark fs-6">
                        {{ $summaryRework->total() }}
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
                                @if ($summaryRework->count() < 1)
                                    <tr>
                                        <td colspan='9'>Rework tidak ditemukan</td>
                                    </tr>
                                @else
                                    @foreach ($summaryRework as $row)
                                        <tr>
                                            <td>{{ $row->reworked_at }}</td>
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
                        </div>
                    </table>
                    <div class="mt-2">
                        {{ $summaryRework->links() }}
                    </div>
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
        });

        document.addEventListener("DOMContentLoaded", function () {
        });

        document.addEventListener("livewire:load", function () {
            Livewire.hook('message.processed', (message, component) => {
            });
        });

        Livewire.on('resetSelect2', () => {
        });

        Livewire.on('afterSave', () => {
        });

        Livewire.on('reloadPage', () => {
            location.reload();
        });
        
        function confirmRework(id) {
            Swal.fire({
                title: 'Konfirmasi Proses',
                text: 'Apakah Anda yakin ingin memproses defect ini ke tahap Rework?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, lanjutkan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.emit('openDefectModal', id);
                }
            });
        }

        Livewire.on('focusScanInput', () => {
            setTimeout(() => {
                const input = document.getElementById('scannedItemReturn');

                if (input) {
                    input.focus();
                    input.select();
                }
            }, 500);
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
                url: "{{ route('get-scanned-item-return-rework') }}",
                type: "GET",
                data: {
                    id: id
                },
                success: function(response) {

                    $('#scannedItemReturn').val('');

                    if (response) {
                        @this.set('idDefect', response.id);
                        @this.set('kode_qr', response.kode_numbering);
                        @this.set('po', response.po);
                        @this.set('worksheet_style', response.kpno + ' - ' + response.style);
                        @this.set('color', response.color);
                        @this.set('size', response.size);
                        @this.set('packing_line', response.packing_line);

                        // confirmRework(response.id);
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
