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

            <div class="card-header d-flex justify-content-between align-items-center bg-rft text-light">
                <p class="mb-0 fs-5">WAITING CHECK</p>

                <span class="badge bg-light text-dark fs-6">
                    {{ $summary->total() }}
                </span>
            </div>

            <div class="card-body">
                {{-- <div class="d-flex justify-content-between align-items-end gap-2 mb-3">
                    <div class="d-flex gap-3">
                        <div>
                            <label class="form-label mb-1">Tanggal Awal</label>
                            <input type="date" class="form-control rounded-0" wire:model="startDate">
                        </div>
                        <div>
                            <label class="form-label mb-1">Tanggal Akhir</label>
                            <input type="date" class="form-control rounded-0" wire:model="endDate">
                        </div>
                    </div>

                    <div style="width: 300px;">
                        <input type="text" class="form-control rounded-0" wire:model="searchSummary" placeholder="Search here...">
                    </div>
                </div> --}}
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
                <div class="mt-2">
                    {{ $summary->links() }}
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
        
    </script>
@endpush
