@extends('layouts.app')

@section('title', 'Edit Transaksi - TBK Finance')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">Edit Transaksi</h1>
        <p class="mt-1 text-sm text-slate-600">
            Perbarui tanggal, COA, deskripsi, atau sisi nominal transaksi.
        </p>
    </div>

    @include('transactions._vue_form', [
        'action' => route('transactions.update', $transaction),
        'method' => 'PUT',
        'transaction' => $transaction,
    ])
@endsection
