@extends('layouts.index')

@section('custom-link')
<style>
    .bg-sb-secondary:hover {
        background-color: var(--sb-secondary-color) !important;
        border-color: var(--sb-secondary-color) !important;
        color: #fff !important;
        opacity: .85;
    }
</style>
@endsection

@section('content')
    {{-- Production Panel Livewire --}}
    @livewire('return-waiting')

@endsection

@section('custom-script')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            $('.select2').select2({
                theme: "bootstrap-5",
                width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
                placeholder: $( this ).data( 'placeholder' ),
            });
        });

        Livewire.on('alert', (type, message) => {
            showNotification(type, message);
        });
    </script>
@endsection
