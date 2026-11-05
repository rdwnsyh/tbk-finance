@extends('layouts.app')

@section('title', 'Laporan Laba Rugi - TBK Finance')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-slate-900">Laporan Laba Rugi</h1>
        <p class="mt-1 text-sm text-slate-600">
            Pendapatan dihitung dari credit dikurangi debit pada COA income;
            beban dihitung dari debit dikurangi credit pada COA expense.
        </p>
    </div>

    {{-- Filter memakai GET agar rentang bisa di-bookmark dan dibagikan. --}}
    <form
        method="GET"
        action="{{ route('profit-loss.index') }}"
        class="mb-6 flex flex-wrap items-end gap-3 rounded-lg border border-slate-200 bg-white p-4"
    >
        <div>
            <label for="start_month" class="mb-1 block text-sm font-medium text-slate-700">
                Bulan Mulai
            </label>
            <input
                type="month"
                id="start_month"
                name="start_month"
                value="{{ old('start_month', $start->format('Y-m')) }}"
                class="rounded-md border px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 @error('start_month') border-red-500 focus:ring-red-200 @else border-slate-300 focus:ring-slate-200 @enderror"
            >
            @error('start_month')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="end_month" class="mb-1 block text-sm font-medium text-slate-700">
                Bulan Akhir
            </label>
            <input
                type="month"
                id="end_month"
                name="end_month"
                value="{{ old('end_month', $end->format('Y-m')) }}"
                class="rounded-md border px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 @error('end_month') border-red-500 focus:ring-red-200 @else border-slate-300 focus:ring-slate-200 @enderror"
            >
            @error('end_month')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="min-w-48 flex-1">
            <label for="search" class="mb-1 block text-sm font-medium text-slate-700">
                Cari bulan di tabel
            </label>
            <input
                type="search"
                id="search"
                name="search"
                value="{{ $search }}"
                maxlength="100"
                placeholder="Nama bulan atau tahun"
                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-slate-200"
            >
        </div>

        <button
            type="submit"
            class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700"
        >
            Tampilkan
        </button>

        <a
            href="{{ route('profit-loss.index') }}"
            class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
        >
            Reset
        </a>

        {{-- Rentang aktif dibawa ke URL export agar file yang diunduh sama
             dengan tabel yang sedang dilihat. --}}
        <a
            href="{{ route('profit-loss.export', ['start_month' => $start->format('Y-m'), 'end_month' => $end->format('Y-m'), 'search' => $search]) }}"
            class="rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-500"
        >
            Export Excel
        </a>
    </form>

    @include('reports._profit_loss_table', ['report' => $report])

    <p class="mt-3 text-xs text-slate-500">
        Bulan tanpa transaksi tetap ditampilkan dengan nilai 0.
    </p>
@endsection
