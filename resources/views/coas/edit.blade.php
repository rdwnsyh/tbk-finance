@extends('layouts.app')

@section('title', 'Edit COA - TBK Finance')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">Edit COA</h1>
        <p class="mt-1 text-sm text-slate-600">
            Perbarui kode, nama, atau kategori akun.
        </p>
    </div>

    @include('coas._form', ['coa' => $coa])
@endsection
