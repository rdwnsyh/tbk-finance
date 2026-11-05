<?php

namespace App\Http\Controllers;

use App\Http\Requests\CoaRequest;
use App\Models\Coa;
use App\Models\CoaCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CoaController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'in:income,expense'],
        ]);
        $search = trim($filters['search'] ?? '');
        $type = $filters['type'] ?? '';

        // Eager loading `coaCategory` mencegah N+1 saat menampilkan nama kategori.
        // `withCount('transactions')` dipakai untuk menampilkan jumlah transaksi
        // sekaligus menjadi konteks tombol hapus.
        $coas = Coa::query()
            ->with('coaCategory')
            ->withCount('transactions')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhereHas('coaCategory', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($type !== '', fn ($query) => $query->whereHas(
                'coaCategory',
                fn ($query) => $query->where('type', $type),
            ))
            ->orderBy('code')
            ->paginate(10)
            ->withQueryString();

        return view('coas.index', compact('coas', 'search', 'type'));
    }

    public function create(): View
    {
        // Dropdown butuh daftar kategori. Urutkan per nama; pengelompokan
        // income/expense dilakukan di Blade agar tidak bergantung pada MySQL.
        $coaCategories = CoaCategory::orderBy('name')->get();

        return view('coas.create', compact('coaCategories'));
    }

    public function store(CoaRequest $request): RedirectResponse
    {
        Coa::create($request->validated());

        return redirect()
            ->route('coas.index')
            ->with('success', 'COA berhasil ditambahkan.');
    }

    public function edit(Coa $coa): View
    {
        $coaCategories = CoaCategory::orderBy('name')->get();

        return view('coas.edit', compact('coa', 'coaCategories'));
    }

    public function update(CoaRequest $request, Coa $coa): RedirectResponse
    {
        $coa->update($request->validated());

        return redirect()
            ->route('coas.index')
            ->with('success', 'COA berhasil diperbarui.');
    }

    public function destroy(Coa $coa): RedirectResponse
    {
        // COA yang sudah dipakai transaksi tidak boleh dihapus agar laporan
        // lama tidak kehilangan akun. Foreign key restrictOnDelete adalah
        // pengaman terakhir bila ada proses lain di antara cek dan delete.
        if ($coa->transactions()->exists()) {
            return redirect()
                ->route('coas.index')
                ->with('error', 'COA "'.$coa->code.' - '.$coa->name.'" tidak bisa dihapus karena masih digunakan oleh transaksi.');
        }

        $coa->delete();

        return redirect()
            ->route('coas.index')
            ->with('success', 'COA berhasil dihapus.');
    }
}
