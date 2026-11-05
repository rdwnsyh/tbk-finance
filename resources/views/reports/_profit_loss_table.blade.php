<div class="overflow-x-auto rounded-lg border border-slate-200 bg-white">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
            <tr>
                <th class="px-4 py-3">Bulan</th>
                <th class="px-4 py-3 text-right">Pendapatan</th>
                <th class="px-4 py-3 text-right">Beban</th>
                <th class="px-4 py-3 text-right">Laba/Rugi Bersih</th>
            </tr>
        </thead>

        <tbody class="divide-y divide-slate-100">
            @forelse ($report['rows'] as $row)
                <tr>
                    <td class="whitespace-nowrap px-4 py-3 text-slate-700">
                        {{ $row['label'] }}
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-right font-mono text-slate-700">
                        {{ number_format($row['income'], 2, ',', '.') }}
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-right font-mono text-slate-700">
                        {{ number_format($row['expense'], 2, ',', '.') }}
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-right font-mono font-medium {{ $row['net'] < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                        {{ number_format($row['net'], 2, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="px-4 py-10 text-center text-slate-500">
                        Tidak ada data pada rentang ini.
                    </td>
                </tr>
            @endforelse
        </tbody>

        <tfoot class="bg-slate-50 text-sm font-semibold">
            <tr>
                <td class="px-4 py-3 text-slate-700">Total</td>
                <td class="whitespace-nowrap px-4 py-3 text-right font-mono text-slate-900">
                    {{ number_format($report['total_income'], 2, ',', '.') }}
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-right font-mono text-slate-900">
                    {{ number_format($report['total_expense'], 2, ',', '.') }}
                </td>
                <td class="whitespace-nowrap px-4 py-3 text-right font-mono {{ $report['total_net'] < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                    {{ number_format($report['total_net'], 2, ',', '.') }}
                </td>
            </tr>
        </tfoot>
    </table>
</div>
