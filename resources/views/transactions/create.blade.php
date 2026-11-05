@extends('layouts.app')

@section('title', 'Tambah Transaksi - TBK Finance')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">Tambah Transaksi</h1>
        <p class="mt-1 text-sm text-slate-600">
            Pilih COA, lalu tentukan apakah nominal masuk ke sisi debit atau credit.
        </p>
    </div>

    @include('transactions._vue_form', [
        'action' => route('transactions.store'),
        'method' => 'POST',
        'transaction' => null,
    ])
@endsection
