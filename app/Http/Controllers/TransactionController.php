<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Models\Coa;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
        ]);
        $search = trim($filters['search'] ?? '');
        $dateFrom = $filters['date_from'] ?? '';
        $dateTo = $filters['date_to'] ?? '';

        // Eager load `coa` agar menampilkan kode/nama COA tidak memicu N+1.
        // Urutkan dari tanggal terbaru; `id` dipakai sebagai penentu urutan
        // ketika beberapa transaksi memiliki tanggal yang sama.
        $transactions = Transaction::query()
            ->with('coa')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('description', 'like', "%{$search}%")
                        ->orWhereHas('coa', function ($query) use ($search) {
                            $query->where('code', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($dateFrom !== '', fn ($query) => $query->where('date', '>=', $dateFrom))
            ->when($dateTo !== '', fn ($query) => $query->where('date', '<=', $dateTo))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('transactions.index', compact('transactions', 'search', 'dateFrom', 'dateTo'));
    }

    public function create(): View
    {
        return view('transactions.create', [
            'coas' => $this->coaOptions(),
        ]);
    }

    public function store(TransactionRequest $request): RedirectResponse
    {
        Transaction::create($this->normalizedData($request));

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaksi berhasil ditambahkan.');
    }

    public function edit(Transaction $transaction): View
    {
        return view('transactions.edit', [
            'transaction' => $transaction,
            'coas' => $this->coaOptions(),
        ]);
    }

    public function update(TransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $transaction->update($this->normalizedData($request));

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaksi berhasil diperbarui.');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        // Transaksi adalah ujung relasi, jadi tidak ada foreign key yang
        // menghalangi penghapusan.
        $transaction->delete();

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaksi berhasil dihapus.');
    }

    /**
     * Opsi COA untuk dropdown Vue.
     *
     * Eager load `coaCategory` karena komponen butuh tipe kategori untuk
     * mengelompokkan pilihan Income/Expense. Data dipetakan menjadi array
     * sederhana agar aman dikirim sebagai JSON ke browser.
     *
     * @return Collection<int, array{id:int, code:int, name:string, category:string, type:string}>
     */
    private function coaOptions(): Collection
    {
        return Coa::query()
            ->with('coaCategory')
            ->orderBy('code')
            ->get()
            ->map(fn (Coa $coa) => [
                'id' => $coa->id,
                'code' => $coa->code,
                'name' => $coa->name,
                'category' => $coa->coaCategory->name,
                'type' => $coa->coaCategory->type,
            ]);
    }

    /**
     * Menormalkan data sebelum disimpan:
     * - debit/credit yang tidak dikirim dianggap 0 agar aturan "salah satu
     *   sisi 0" tetap konsisten di database.
     * - deskripsi kosong disimpan sebagai NULL, bukan string kosong.
     */
    private function normalizedData(TransactionRequest $request): array
    {
        $data = $request->validated();

        $data['debit'] = $data['debit'] ?? 0;
        $data['credit'] = $data['credit'] ?? 0;
        $data['description'] = filled($data['description'] ?? null)
            ? $data['description']
            : null;

        return $data;
    }
}
