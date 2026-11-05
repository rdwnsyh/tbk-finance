@extends('layouts.app')

@section('title', 'Edit Kategori COA - TBK Finance')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">Edit Kategori COA</h1>
        <p class="mt-1 text-sm text-slate-600">
            Perbarui nama atau tipe kategori. Perubahan tipe memengaruhi perhitungan laporan.
        </p>
    </div>

    {{-- Model yang di-bind route dikirim ke partial agar form terisi
         dan otomatis memakai route update. --}}
    @include('coa-categories._form', ['coaCategory' => $coaCategory])
@endsection
