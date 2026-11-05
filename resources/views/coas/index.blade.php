@use('App\Models\CoaCategory')

@extends('layouts.app')

@section('title', 'COA - TBK Finance')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Chart of Account (COA)</h1>
            <p class="mt-1 text-sm text-slate-600">
                Daftar akun beserta kategori dan jumlah transaksi yang memakainya.
            </p>
        </div>

        <a
            href="{{ route('coas.create') }}"
            class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700"
        >
            Tambah COA
        </a>
    </div>

    <form
        method="GET"
        action="{{ route('coas.index') }}"
        class="mb-4 flex flex-wrap items-end gap-3 rounded-lg border border-slate-200 bg-white p-4"
    >
        <div class="min-w-56 flex-1">
            <label for="search" class="mb-1 block text-sm font-medium text-slate-700">
                Cari COA
            </label>
            <input
                type="search"
                id="search"
                name="search"
                value="{{ $search }}"
                maxlength="100"
                placeholder="Kode, nama COA, atau kategori"
                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-slate-200"
            >
        </div>

        <div>
            <label for="type" class="mb-1 block text-sm font-medium text-slate-700">
                Tipe kategori
            </label>
            <select
                id="type"
                name="type"
                class="rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-slate-200"
            >
                <option value="">Semua tipe</option>
                <option value="income" @selected($type === 'income')>Income</option>
                <option value="expense" @selected($type === 'expense')>Expense</option>
            </select>
        </div>

        <button
            type="submit"
            class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700"
        >
            Cari
        </button>

        <a
            href="{{ route('coas.index') }}"
            class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
        >
            Reset
        </a>
    </form>

    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Kode</th>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Jumlah Transaksi</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($coas as $coa)
                    <tr>
                        <td class="px-4 py-3 font-mono text-slate-700">
                            {{ $coa->code }}
                        </td>
                        <td class="px-4 py-3 font-medium text-slate-900">
                            {{ $coa->name }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap items-center gap-2">
                                {{-- Relasi sudah di-eager-load di controller,
                                     jadi mengakses nama kategori tidak menambah query. --}}
                                <span class="text-slate-700">{{ $coa->coaCategory->name }}</span>
                                <span
                                    @class([
                                        'inline-flex rounded-full px-2 py-0.5 text-xs font-medium',
                                        'bg-green-100 text-green-800' => $coa->coaCategory->type === CoaCategory::TYPE_INCOME,
                                        'bg-red-100 text-red-800' => $coa->coaCategory->type === CoaCategory::TYPE_EXPENSE,
                                    ])
                                >
                                    {{ $coa->coaCategory->type === CoaCategory::TYPE_INCOME ? 'Income' : 'Expense' }}
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $coa->transactions_count }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a
                                    href="{{ route('coas.edit', $coa) }}"
                                    class="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                                >
                                    Edit
                                </a>

                                {{-- Konfirmasi tidak menyisipkan input user agar aman
                                     meski nama mengandung tanda kutip. --}}
                                <form
                                    method="POST"
                                    action="{{ route('coas.destroy', $coa) }}"
                                    onsubmit="return confirm('Hapus COA ini?');"
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
                        <td colspan="5" class="px-4 py-10 text-center text-slate-500">
                            {{ $search !== '' || $type !== '' ? 'Tidak ada COA yang cocok dengan filter.' : 'Belum ada COA.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $coas->links() }}
    </div>
@endsection
