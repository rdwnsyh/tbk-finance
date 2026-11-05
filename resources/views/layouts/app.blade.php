<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title', 'TBK Finance')</title>

        {{-- Hanya CSS yang dimuat di semua halaman. JavaScript/Vue dimuat khusus
             di halaman yang membutuhkan lewat @stack('scripts'), supaya halaman
             CRUD biasa tidak ikut mengunduh bundle Vue. --}}
        @vite('resources/css/app.css')
    </head>
    <body class="min-h-screen bg-slate-100 text-slate-800">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-3 px-4 py-4">
                <a href="{{ route('coa-categories.index') }}" class="text-lg font-semibold text-slate-900">
                    TBK Finance
                </a>

                <nav class="flex flex-wrap items-center gap-1 text-sm">
                    {{-- Route::has dipakai agar menu modul yang belum dibuat tidak
                         dirender sebagai link rusak. Menu otomatis muncul ketika
                         route-nya ditambahkan pada langkah berikutnya. --}}
                    @foreach ([
                        'coa-categories.index' => 'Kategori COA',
                        'coas.index' => 'COA',
                        'transactions.index' => 'Transaksi',
                        'profit-loss.index' => 'Laporan',
                    ] as $routeName => $label)
                        @if (Route::has($routeName))
                            @php($routePattern = str_replace('.index', '.*', $routeName))
                            @php($isActive = request()->routeIs($routePattern))
                            <a
                                href="{{ route($routeName) }}"
                                @class([
                                    'rounded-md px-3 py-2 font-medium',
                                    'bg-slate-900 text-white' => $isActive,
                                    'text-slate-600 hover:bg-slate-100' => ! $isActive,
                                ])
                            >
                                {{ $label }}
                            </a>
                        @endif
                    @endforeach
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-8">
            {{-- Pesan sukses/error berasal dari flash session yang di-set controller. --}}
            @if (session('success'))
                <div class="mb-6 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>

        {{-- Slot untuk script tambahan (mis. bundle Vue di halaman transaksi). --}}
        @stack('scripts')
    </body>
</html>
