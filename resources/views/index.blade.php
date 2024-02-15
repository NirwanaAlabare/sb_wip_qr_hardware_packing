@extends('layouts.index')

@section('content')
    <livewire:order-list/>
@endsection

@section('custom-script')
    <script>
        Livewire.on('showFilterModal', () => {
            showFilterModal();
        });
    </script>
@endsection

