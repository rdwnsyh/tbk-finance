@use('App\Models\CoaCategory')

@extends('layouts.app')

@section('title', 'Kategori COA - TBK Finance')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Kategori COA</h1>
            <p class="mt-1 text-sm text-slate-600">
                Tipe kategori menentukan apakah COA dihitung sebagai pendapatan atau pengeluaran.
            </p>
        </div>

        <a
            href="{{ route('coa-categories.create') }}"
            class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700"
        >
            Tambah Kategori
        </a>
    </div>

    <form
        method="GET"
        action="{{ route('coa-categories.index') }}"
        class="mb-4 flex flex-wrap items-end gap-3 rounded-lg border border-slate-200 bg-white p-4"
    >
        <div class="min-w-56 flex-1">
            <label for="search" class="mb-1 block text-sm font-medium text-slate-700">
                Cari kategori
            </label>
            <input
                type="search"
                id="search"
                name="search"
                value="{{ $search }}"
                maxlength="100"
                placeholder="Nama kategori"
                class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-slate-200"
            >
        </div>

        <div>
            <label for="type" class="mb-1 block text-sm font-medium text-slate-700">
                Tipe
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
            href="{{ route('coa-categories.index') }}"
            class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
        >
            Reset
        </a>
    </form>

    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Tipe</th>
                    <th class="px-4 py-3">Jumlah COA</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                {{-- @forelse menampilkan empty state otomatis bila data kosong,
                     lebih rapi daripada mengecek count() manual di Blade. --}}
                @forelse ($coaCategories as $category)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">
                            {{ $category->name }}
                        </td>
                        <td class="px-4 py-3">
                            {{-- Warna berbeda dipakai sebagai penanda cepat tipe kategori. --}}
                            <span
                                @class([
                                    'inline-flex rounded-full px-2 py-1 text-xs font-medium',
                                    'bg-green-100 text-green-800' => $category->type === CoaCategory::TYPE_INCOME,
                                    'bg-red-100 text-red-800' => $category->type === CoaCategory::TYPE_EXPENSE,
                                ])
                            >
                                {{ $category->type === CoaCategory::TYPE_INCOME ? 'Income' : 'Expense' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $category->coas_count }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a
                                    href="{{ route('coa-categories.edit', $category) }}"
                                    class="rounded-md border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50"
                                >
                                    Edit
                                </a>

                                {{-- Form terpisah diperlukan karena HTML hanya mendukung
                                     GET/POST; @method('DELETE') memberi tahu Laravel
                                     bahwa request ini harus ditangani sebagai DELETE. --}}
                                {{-- Teks konfirmasi sengaja tidak menyisipkan nama kategori:
                                     nama berasal dari input user dan bisa mengandung tanda kutip
                                     yang merusak string JavaScript di atribut ini. --}}
                                <form
                                    method="POST"
                                    action="{{ route('coa-categories.destroy', $category) }}"
                                    onsubmit="return confirm('Hapus kategori ini?');"
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
                        <td colspan="4" class="px-4 py-10 text-center text-slate-500">
                            {{ $search !== '' || $type !== '' ? 'Tidak ada kategori yang cocok dengan filter.' : 'Belum ada kategori COA.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Link pagination otomatis disembunyikan kalau hanya ada satu halaman. --}}
    <div class="mt-4">
        {{ $coaCategories->links() }}
    </div>
@endsection
