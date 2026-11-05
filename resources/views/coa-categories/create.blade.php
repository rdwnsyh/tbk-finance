@extends('layouts.app')

@section('title', 'Tambah Kategori COA - TBK Finance')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">Tambah Kategori COA</h1>
        <p class="mt-1 text-sm text-slate-600">
            Isi nama kategori dan pilih apakah termasuk pendapatan atau pengeluaran.
        </p>
    </div>

    {{-- `null` dikirim eksplisit agar partial tahu ini mode create
         (bukan edit) dan memakai route store. --}}
    @include('coa-categories._form', ['coaCategory' => null])
@endsection
