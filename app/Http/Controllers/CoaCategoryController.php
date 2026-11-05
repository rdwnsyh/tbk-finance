<?php

namespace App\Http\Controllers;

use App\Http\Requests\CoaCategoryRequest;
use App\Models\CoaCategory;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CoaCategoryController extends Controller
{
    /**
     * Menampilkan daftar kategori dengan pagination.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'in:income,expense'],
        ]);
        $search = trim($filters['search'] ?? '');
        $type = $filters['type'] ?? '';

        // `withCount('coas')` menghitung jumlah COA dalam SATU query tambahan,
        // bukan satu query per baris (mencegah N+1). Angka ini dipakai untuk
        // ditampilkan sekaligus memberi konteks mengapa sebuah kategori tidak
        // bisa dihapus.
        $coaCategories = CoaCategory::query()
            ->withCount('coas')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->when($type !== '', fn ($query) => $query->where('type', $type))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('coa-categories.index', compact('coaCategories', 'search', 'type'));
    }

    /**
     * Form tambah kategori.
     */
    public function create(): View
    {
        return view('coa-categories.create');
    }

    /**
     * Simpan kategori baru. Data sudah divalidasi oleh CoaCategoryRequest,
     * jadi controller tidak perlu memeriksa ulang (tetap tipis).
     */
    public function store(CoaCategoryRequest $request): RedirectResponse
    {
        CoaCategory::create($request->validated());

        return redirect()
            ->route('coa-categories.index')
            ->with('success', 'Kategori COA berhasil ditambahkan.');
    }

    /**
     * Form edit kategori.
     */
    public function edit(CoaCategory $coaCategory): View
    {
        return view('coa-categories.edit', compact('coaCategory'));
    }

    /**
     * Perbarui kategori.
     */
    public function update(CoaCategoryRequest $request, CoaCategory $coaCategory): RedirectResponse
    {
        $coaCategory->update($request->validated());

        return redirect()
            ->route('coa-categories.index')
            ->with('success', 'Kategori COA berhasil diperbarui.');
    }

    /**
     * Hapus kategori hanya jika belum dipakai COA.
     */
    public function destroy(CoaCategory $coaCategory): RedirectResponse
    {
        // Cek relasi lebih dulu supaya pesan error ramah pengguna.
        // Foreign key `restrictOnDelete` di database tetap menjadi pengaman
        // terakhir bila ada proses lain menyisipkan COA tepat di antara
        // pengecekan dan proses delete.
        if ($coaCategory->coas()->exists()) {
            return redirect()
                ->route('coa-categories.index')
                ->with('error', 'Kategori "'.$coaCategory->name.'" tidak bisa dihapus karena masih digunakan oleh COA.');
        }

        $coaCategory->delete();

        return redirect()
            ->route('coa-categories.index')
            ->with('success', 'Kategori COA berhasil dihapus.');
    }
}
