@php
    // Variabel dikirim dari view create/edit; null berarti mode tambah.
    $transaction = $transaction ?? null;
@endphp

@php
    // Nilai awal diambil dari old() agar input tidak hilang saat validasi gagal.
    // Untuk form baru, tanggal default memakai waktu lokal aplikasi.
    $initialDate = old('date', $transaction?->date?->format('Y-m-d') ?? now()->format('Y-m-d'));
    $initialDebit = old('debit', $transaction?->debit ?? 0);
    $initialCredit = old('credit', $transaction?->credit ?? 0);

    // Form Vue hanya punya SATU nominal, jadi mode ditentukan dari sisi yang
    // terisi. Jika keduanya 0 (form baru), default ke debit.
    $initialMode = (float) $initialDebit > 0
        ? 'debit'
        : ((float) $initialCredit > 0 ? 'credit' : 'debit');
    $initialAmount = $initialMode === 'debit' ? $initialDebit : $initialCredit;

    $props = [
        'action' => $action,
        'method' => $method,
        'csrf' => csrf_token(),
        'cancelUrl' => route('transactions.index'),
        'coas' => $coas,
        'initial' => [
            'date' => $initialDate,
            // Cast ke string agar cocok dengan value option yang juga string.
            'coa_id' => (string) old('coa_id', $transaction?->coa_id ?? ''),
            'description' => old('description', $transaction?->description ?? ''),
            'mode' => $initialMode,
            'amount' => (string) $initialAmount,
        ],
        'errors' => [
            'date' => $errors->first('date'),
            'coa_id' => $errors->first('coa_id'),
            'description' => $errors->first('description'),
            // Aturan bisnis memakai key `amount`; error numeric bisa muncul
            // di debit/credit. Digabung agar semuanya tetap tampil.
            'amount' => $errors->first('amount')
                ?: $errors->first('debit')
                ?: $errors->first('credit'),
        ],
    ];
@endphp

{{-- Props dikirim sebagai JSON pada data attribute. Blade meng-escape tanda
     kutip menjadi &quot;, lalu browser mengembalikannya saat dibaca lewat
     `dataset`, sehingga JSON.parse di app.js menerima JSON yang valid. --}}
<div id="transaction-form" data-props="{{ json_encode($props) }}"></div>

{{-- Bundle Vue hanya dimuat di halaman yang membutuhkan, bukan di semua
     halaman CRUD, sehingga halaman lain tetap ringan. --}}
@push('scripts')
    @vite('resources/js/app.js')
@endpush
