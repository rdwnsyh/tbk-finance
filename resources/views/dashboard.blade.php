@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <section class="dashboard-welcome">
        <div>
            <span class="app-eyebrow">RINGKASAN KESELURUHAN DATA</span>
            <h1>Selamat datang kembali 👋</h1>
            <p>Pantau kondisi keuangan bisnis Anda dalam satu tampilan.</p>
        </div>
        <a href="{{ route('transactions.create') }}" class="dashboard-primary-action">
            <span aria-hidden="true">＋</span>
            Catat Transaksi
        </a>
    </section>

    <section class="dashboard-stats" aria-label="Ringkasan bulan ini">
        <article class="dashboard-stat-card">
            <div class="dashboard-stat-icon stat-icon-purple" aria-hidden="true">↗</div>
            <div>
                <span>Total Pendapatan</span>
                <strong>Rp {{ number_format($overallReport['total_income'], 0, ',', '.') }}</strong>
                <small>Akumulasi seluruh periode</small>
            </div>
        </article>
        <article class="dashboard-stat-card">
            <div class="dashboard-stat-icon stat-icon-orange" aria-hidden="true">↙</div>
            <div>
                <span>Total Pengeluaran</span>
                <strong>Rp {{ number_format($overallReport['total_expense'], 0, ',', '.') }}</strong>
                <small>Akumulasi seluruh periode</small>
            </div>
        </article>
        <article class="dashboard-stat-card">
            <div class="dashboard-stat-icon stat-icon-green" aria-hidden="true">＝</div>
            <div>
                <span>Laba / Rugi Bersih</span>
                <strong @class(['text-negative' => $overallReport['total_net'] < 0])>
                    Rp {{ number_format($overallReport['total_net'], 0, ',', '.') }}
                </strong>
                <small>Akumulasi seluruh periode</small>
            </div>
        </article>
        <article class="dashboard-stat-card">
            <div class="dashboard-stat-icon stat-icon-blue" aria-hidden="true">▤</div>
            <div>
                <span>Transaksi Bulan Ini</span>
                <strong>{{ number_format($transactionCount, 0, ',', '.') }}</strong>
                <small>{{ number_format($coaCount, 0, ',', '.') }} akun COA terdaftar</small>
            </div>
        </article>
    </section>

    <section class="dashboard-content-grid">
        <article class="dashboard-panel dashboard-trend-panel">
            <div class="dashboard-panel-heading">
                <div>
                    <h2>Arus Pendapatan & Pengeluaran</h2>
                    <p>Ringkasan seluruh periode transaksi</p>
                </div>
                <span class="dashboard-period-badge">{{ number_format($trendMonthCount, 0, ',', '.') }} bulan</span>
            </div>

            <div class="dashboard-chart-legend">
                <span><i class="legend-income"></i>Pendapatan</span>
                <span><i class="legend-expense"></i>Pengeluaran</span>
            </div>

            <div class="dashboard-month-chart">
                @foreach ($trend as $month)
                    <div class="dashboard-month-row">
                        <span class="dashboard-month-label">{{ $month['label'] }}</span>
                        <div class="dashboard-bars">
                            <div class="dashboard-bar-track">
                                <span
                                    class="dashboard-bar dashboard-income-bar"
                                    style="width: {{ $month['income_width'] }}%"
                                    title="Pendapatan {{ $month['label'] }}: Rp {{ number_format($month['income'], 0, ',', '.') }}"
                                ></span>
                            </div>
                            <div class="dashboard-bar-track">
                                <span
                                    class="dashboard-bar dashboard-expense-bar"
                                    style="width: {{ $month['expense_width'] }}%"
                                    title="Pengeluaran {{ $month['label'] }}: Rp {{ number_format($month['expense'], 0, ',', '.') }}"
                                ></span>
                            </div>
                        </div>
                        <div class="dashboard-month-values">
                            <span>Rp {{ number_format($month['income'], 0, ',', '.') }}</span>
                            <span>Rp {{ number_format($month['expense'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="dashboard-panel dashboard-shortcuts-panel">
            <div class="dashboard-panel-heading">
                <div>
                    <h2>Akses Cepat</h2>
                    <p>Kelola pembukuan Anda</p>
                </div>
            </div>

            <div class="dashboard-shortcuts">
                <a href="{{ route('transactions.create') }}" class="dashboard-shortcut">
                    <span class="shortcut-icon shortcut-purple" aria-hidden="true">＋</span>
                    <span><strong>Tambah Transaksi</strong><small>Catat pemasukan atau pengeluaran</small></span>
                    <span class="shortcut-arrow" aria-hidden="true">›</span>
                </a>
                <a href="{{ route('coas.index') }}" class="dashboard-shortcut">
                    <span class="shortcut-icon shortcut-blue" aria-hidden="true">▤</span>
                    <span><strong>Daftar COA</strong><small>{{ number_format($coaCount, 0, ',', '.') }} akun tersedia</small></span>
                    <span class="shortcut-arrow" aria-hidden="true">›</span>
                </a>
                <a href="{{ route('profit-loss.index') }}" class="dashboard-shortcut">
                    <span class="shortcut-icon shortcut-green" aria-hidden="true">⌁</span>
                    <span><strong>Laporan Laba Rugi</strong><small>Lihat dan unduh laporan keuangan</small></span>
                    <span class="shortcut-arrow" aria-hidden="true">›</span>
                </a>
                <a href="{{ route('coa-categories.index') }}" class="dashboard-shortcut">
                    <span class="shortcut-icon shortcut-orange" aria-hidden="true">▦</span>
                    <span><strong>Kategori COA</strong><small>{{ number_format($categoryCount, 0, ',', '.') }} kategori</small></span>
                    <span class="shortcut-arrow" aria-hidden="true">›</span>
                </a>
            </div>
        </article>
    </section>

    <section class="dashboard-panel dashboard-recent-panel">
        <div class="dashboard-panel-heading">
            <div>
                <h2>Transaksi Terbaru</h2>
                <p>Aktivitas pencatatan terakhir</p>
            </div>
            <a href="{{ route('transactions.index') }}" class="dashboard-view-all">
                Lihat semua <span aria-hidden="true">→</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Akun</th>
                        <th class="px-4 py-3">Deskripsi</th>
                        <th class="px-4 py-3 text-right">Nominal</th>
                        <th class="px-4 py-3">Tipe</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($recentTransactions as $transaction)
                        <tr>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-600">
                                {{ $transaction->date->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-medium text-slate-900">{{ $transaction->coa->name }}</span>
                                <span class="ml-1 font-mono text-xs text-slate-500">{{ $transaction->coa->code }}</span>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $transaction->description ?: '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right font-medium text-slate-900">
                                Rp {{ number_format((float) $transaction->debit ?: (float) $transaction->credit, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">
                                @if ((float) $transaction->credit > 0)
                                    <span class="dashboard-type-badge type-income">Pendapatan</span>
                                @else
                                    <span class="dashboard-type-badge type-expense">Pengeluaran</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-slate-500">
                                Belum ada transaksi. <a href="{{ route('transactions.create') }}" class="font-medium text-primary">Catat transaksi pertama</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
