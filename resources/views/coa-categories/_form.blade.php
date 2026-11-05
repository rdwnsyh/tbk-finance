@use('App\Models\CoaCategory')

@php($isEdit = $coaCategory !== null)

{{-- Partial yang sama dipakai create & edit supaya aturan tampilan dan nama
     field tidak berbeda antara dua halaman (mengurangi bug "form edit lupa field"). --}}
<form
    method="POST"
    action="{{ $isEdit ? route('coa-categories.update', $coaCategory) : route('coa-categories.store') }}"
    class="max-w-xl space-y-6 rounded-lg border border-slate-200 bg-white p-6"
>
    @csrf
    {{-- HTML hanya mendukung GET/POST. Saat edit, method asli diubah menjadi PUT
         agar cocok dengan route `update` milik resource controller. --}}
    @if ($isEdit)
        @method('PUT')
    @endif

    <div>
        <label for="name" class="mb-1 block text-sm font-medium text-slate-700">
            Nama Kategori
        </label>

        {{-- `old()` dipakai agar input tidak hilang ketika validasi gagal.
             Argumen kedua menjadi nilai awal saat form dibuka pertama kali. --}}
        <input
            type="text"
            name="name"
            id="name"
            value="{{ old('name', $coaCategory?->name) }}"
            placeholder="Contoh: Salary"
            @class([
                'w-full rounded-md border px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2',
                'border-red-500 focus:ring-red-200' => $errors->has('name'),
                'border-slate-300 focus:ring-slate-200' => ! $errors->has('name'),
            ])
        >

        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="type" class="mb-1 block text-sm font-medium text-slate-700">
            Tipe Kategori
        </label>

        <select
            name="type"
            id="type"
            @class([
                'w-full rounded-md border px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2',
                'border-red-500 focus:ring-red-200' => $errors->has('type'),
                'border-slate-300 focus:ring-slate-200' => ! $errors->has('type'),
            ])
        >
            {{-- Placeholder tidak punya value agar tidak bisa terkirim sebagai pilihan sah. --}}
            <option value="" disabled @selected(old('type', $coaCategory?->type) === null)>
                Pilih tipe
            </option>
            <option
                value="{{ CoaCategory::TYPE_INCOME }}"
                @selected(old('type', $coaCategory?->type) === CoaCategory::TYPE_INCOME)
            >
                Income
            </option>
            <option
                value="{{ CoaCategory::TYPE_EXPENSE }}"
                @selected(old('type', $coaCategory?->type) === CoaCategory::TYPE_EXPENSE)
            >
                Expense
            </option>
        </select>

        @error('type')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center gap-3">
        <button
            type="submit"
            class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700"
        >
            {{ $isEdit ? 'Simpan Perubahan' : 'Simpan' }}
        </button>

        <a
            href="{{ route('coa-categories.index') }}"
            class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
        >
            Batal
        </a>
    </div>
</form>
