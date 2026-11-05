@use('App\Models\CoaCategory')

@php($isEdit = $coa !== null)

{{-- Nilai terpilih disamakan sebagai string karena old() menghasilkan string,
     sedangkan id dari model bertipe integer. Tanpa cast, perbandingan ketat
     bisa gagal dan dropdown tidak menampilkan pilihan sebelumnya. --}}
@php($selectedCategoryId = (string) old('coa_category_id', $coa?->coa_category_id))

@php($incomeCategories = $coaCategories->where('type', CoaCategory::TYPE_INCOME))
@php($expenseCategories = $coaCategories->where('type', CoaCategory::TYPE_EXPENSE))

<form
    method="POST"
    action="{{ $isEdit ? route('coas.update', $coa) : route('coas.store') }}"
    class="max-w-xl space-y-6 rounded-lg border border-slate-200 bg-white p-6"
>
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    @if ($coaCategories->isEmpty())
        {{-- COA tidak bisa dibuat tanpa kategori; arahkan user ke langkah yang benar. --}}
        <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            Belum ada kategori COA. Silakan
            <a href="{{ route('coa-categories.create') }}" class="font-medium underline">
                tambah kategori
            </a>
            terlebih dahulu.
        </div>
    @endif

    <div>
        <label for="code" class="mb-1 block text-sm font-medium text-slate-700">
            Kode COA
        </label>

        <input
            type="number"
            name="code"
            id="code"
            min="1"
            step="1"
            inputmode="numeric"
            value="{{ old('code', $coa?->code) }}"
            placeholder="Contoh: 401"
            @class([
                'w-full rounded-md border px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2',
                'border-red-500 focus:ring-red-200' => $errors->has('code'),
                'border-slate-300 focus:ring-slate-200' => ! $errors->has('code'),
            ])
        >

        @error('code')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="name" class="mb-1 block text-sm font-medium text-slate-700">
            Nama COA
        </label>

        <input
            type="text"
            name="name"
            id="name"
            value="{{ old('name', $coa?->name) }}"
            placeholder="Contoh: Gaji Karyawan"
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
        <label for="coa_category_id" class="mb-1 block text-sm font-medium text-slate-700">
            Kategori
        </label>

        <select
            name="coa_category_id"
            id="coa_category_id"
            @class([
                'w-full rounded-md border px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2',
                'border-red-500 focus:ring-red-200' => $errors->has('coa_category_id'),
                'border-slate-300 focus:ring-slate-200' => ! $errors->has('coa_category_id'),
            ])
        >
            <option value="" disabled @selected($selectedCategoryId === '')>
                Pilih kategori
            </option>

            {{-- Dikelompokkan agar user mudah membedakan akun pendapatan
                 dan pengeluaran tanpa harus membaca tipe satu per satu. --}}
            @if ($incomeCategories->isNotEmpty())
                <optgroup label="Income">
                    @foreach ($incomeCategories as $category)
                        <option
                            value="{{ $category->id }}"
                            @selected($selectedCategoryId === (string) $category->id)
                        >
                            {{ $category->name }}
                        </option>
                    @endforeach
                </optgroup>
            @endif

            @if ($expenseCategories->isNotEmpty())
                <optgroup label="Expense">
                    @foreach ($expenseCategories as $category)
                        <option
                            value="{{ $category->id }}"
                            @selected($selectedCategoryId === (string) $category->id)
                        >
                            {{ $category->name }}
                        </option>
                    @endforeach
                </optgroup>
            @endif
        </select>

        @error('coa_category_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center gap-3">
        <button
            type="submit"
            class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700"
            @disabled($coaCategories->isEmpty())
        >
            {{ $isEdit ? 'Simpan Perubahan' : 'Simpan' }}
        </button>

        <a
            href="{{ route('coas.index') }}"
            class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
        >
            Batal
        </a>
    </div>
</form>
