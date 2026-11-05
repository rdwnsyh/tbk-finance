@extends('layouts.app')

@section('title', 'Tambah COA - TBK Finance')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">Tambah COA</h1>
        <p class="mt-1 text-sm text-slate-600">
            Kode akun harus unik. Kategori menentukan posisi akun di laporan.
        </p>
    </div>

    {{-- null dikirim eksplisit agar partial memakai route store. --}}
    @include('coas._form', ['coa' => null])
@endsection
