@extends('layouts.app')

@section('title', 'Transaksi - TBK Finance')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Transaksi</h1>
            <p class="mt-1 text-sm text-slate-600">
                Setiap transaksi hanya boleh berisi debit atau credit (satu sisi).
            </p>
        </div>

        <a
            href="{{ route('transactions.create') }}"
            class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700"
        >
            Tambah Transaksi
        </a>
    </div>

    <form
        method="GET"
        action="{{ route('transactions.index') }}"
        class="mb-4 flex flex-wrap items-end gap-3 rounded-lg border border-slate-200 bg-white p-4"
    >
        <div class="min-w-56 flex-1">
            <label for="search" class="mb-1 block text-sm font-medium text-slate-700">
                Cari transaksi
            </label>
            <input
                type="search"
                id="search"
                name="search"
                value="{{ $search }}"
                maxlength="100"
                placeholder="Deskripsi, kode, atau nama COA"
                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-slate-200"
            >
        </div>

        <div>
            <label for="date_from" class="mb-1 block text-sm font-medium text-slate-700">
                Dari tanggal
            </label>
            <input
                type="date"
                id="date_from"
                name="date_from"
                value="{{ $dateFrom }}"
                class="rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-slate-200"
            >
        </div>

        <div>
            <label for="date_to" class="mb-1 block text-sm font-medium text-slate-700">
                Sampai tanggal
            </label>
            <input
                type="date"
                id="date_to"
                name="date_to"
                value="{{ $dateTo }}"
                class="rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-slate-200"
            >
        </div>

        <button
            type="submit"
            class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700"
        >
            Cari
        </button>

        <a
            href="{{ route('transactions.index') }}"
            class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
        >
            Reset
        </a>
    </form>

    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">COA</th>
                    <th class="px-4 py-3">Deskripsi</th>
                    <th class="px-4 py-3 text-right">Debit</th>
                    <th class="px-4 py-3 text-right">Credit</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($transactions as $transaction)
                    <tr>
                        {{-- Format tanggal lokal (d/m/Y) agar mudah dibaca. --}}
                        <td class="whitespace-nowrap px-4 py-3 text-slate-700">
                            {{ $transaction->date->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3">
                            {{-- Relasi `coa` sudah di-eager-load oleh controller. --}}
                            <span class="font-mono text-slate-600">{{ $transaction->coa->code }}</span>
                            <span class="text-slate-900">{{ $transaction->coa->name }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $transaction->description ?: '-' }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right font-mono text-slate-700">
                            {{ (float) $transaction->debit > 0 ? number_format((float) $transaction->debit, 2, ',', '.') : '-' }}
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-right font-mono text-slate-700">
                            {{ (float) $transaction->credit > 0 ? number_format((float) $transaction->credit, 2, ',', '.') : '-' }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a
                                    href="{{ route('transactions.edit', $transaction) }}"
                                    class="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                                >
                                    Edit
                                </a>

                                {{-- Konfirmasi tidak menyisipkan input user agar aman
                                     meski deskripsi mengandung tanda kutip. --}}
                                <form
                                    method="POST"
                                    action="{{ route('transactions.destroy', $transaction) }}"
                                    onsubmit="return confirm('Hapus transaksi ini?');"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="rounded-md border border-red-300 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50"
                                    >
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-slate-500">
                            {{ $search !== '' || $dateFrom !== '' || $dateTo !== '' ? 'Tidak ada transaksi yang cocok dengan filter.' : 'Belum ada transaksi.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $transactions->links() }}
    </div>
@endsection
