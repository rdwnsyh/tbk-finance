<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title', 'TBK Finance')</title>

        @vite('resources/css/app.css')
    </head>
    <body class="app-body">
        <div class="app-shell">
            <aside class="app-sidebar">
                <a href="{{ route('dashboard') }}" class="app-brand">
                    <span class="app-brand-mark" aria-hidden="true">T</span>
                    <span>TBK <span>Finance</span></span>
                </a>

                <p class="app-nav-label">WORKSPACE</p>
                <nav class="app-nav" aria-label="Navigasi utama">
                    <a href="{{ route('dashboard') }}" @class(['app-nav-link', 'is-active' => request()->routeIs('dashboard')])>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="8" height="8" rx="2"/><rect x="13" y="3" width="8" height="5" rx="2"/><rect x="13" y="10" width="8" height="11" rx="2"/><rect x="3" y="13" width="8" height="8" rx="2"/></svg>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('coa-categories.index') }}" @class(['app-nav-link', 'is-active' => request()->routeIs('coa-categories.*')])>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v15H6.5A2.5 2.5 0 0 0 4 20.5z"/><path d="M4 5.5v15M8 7h8M8 11h7"/></svg>
                        <span>Kategori COA</span>
                    </a>
                    <a href="{{ route('coas.index') }}" @class(['app-nav-link', 'is-active' => request()->routeIs('coas.*')])>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M8 8h8M8 12h8M8 16h4"/></svg>
                        <span>Chart of Accounts</span>
                    </a>
                    <a href="{{ route('transactions.index') }}" @class(['app-nav-link', 'is-active' => request()->routeIs('transactions.*')])>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h15l-3-3M20 17H5l3 3"/><path d="M19 7v4M5 13v4"/></svg>
                        <span>Transaksi</span>
                    </a>
                    <a href="{{ route('profit-loss.index') }}" @class(['app-nav-link', 'is-active' => request()->routeIs('profit-loss.*')])>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V5M4 19h17"/><path d="m7 15 4-4 3 2 6-7"/></svg>
                        <span>Laporan Laba Rugi</span>
                    </a>
                </nav>

                <div class="app-sidebar-footer">
                    <span class="app-status-dot"></span>
                    <span>Sistem pembukuan aktif</span>
                </div>
            </aside>

            <div class="app-main">
                <header class="app-topbar">
                    <div class="app-topbar-title">
                        <span class="app-eyebrow">FINANCE WORKSPACE</span>
                        <strong>@yield('title', 'TBK Finance')</strong>
                    </div>
                    <div class="app-user">
                        <div class="app-user-avatar">TB</div>
                        <div>
                            <strong>Administrator</strong>
                            <span>TBK Finance</span>
                        </div>
                    </div>
                </header>

                <main class="app-content">
                    @if (session('success'))
                        <div class="app-alert app-alert-success" role="status">
                            <span class="app-alert-icon" aria-hidden="true">✓</span>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="app-alert app-alert-error" role="alert">
                            <span class="app-alert-icon" aria-hidden="true">!</span>
                            {{ session('error') }}
                        </div>
                    @endif

                    @yield('content')
                </main>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
