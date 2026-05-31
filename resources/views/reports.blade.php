<x-app-layout>
    <div class="w-full mx-auto">
        {{-- Header Section --}}
        <div class="mb-8 flex flex-wrap justify-between items-start gap-4">
            <div>
                <h2 class="text-[#111618] dark:text-white text-lg md:text-2xl font-bold leading-tight tracking-tight">Laporan Pendapatan</h2>
                <p class="text-slate-500 text-[10px] font-medium uppercase tracking-wider mt-1">
                    Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                </p>
            </div>
        </div>

        {{-- Statistik Ringkas --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            {{-- Pendapatan Laundry --}}
            <div class="bg-white dark:bg-[#1a262e] p-4 md:p-5 rounded-xl border border-slate-200 dark:border-gray-800 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/20 flex items-center justify-center text-indigo-600">
                        <span class="material-symbols-outlined text-xl">local_laundry_service</span>
                    </div>
                </div>
                <p class="text-slate-500 dark:text-slate-400 text-xs font-medium uppercase tracking-wider">Pendapatan Laundry</p>
                <p class="text-xl font-bold text-slate-900 dark:text-white mt-1">
                    Rp {{ number_format($totalLaundryRevenue, 0, ',', '.') }}
                </p>
            </div>

            {{-- Pendapatan Driver --}}
            <div class="bg-white dark:bg-[#1a262e] p-4 md:p-5 rounded-xl border border-slate-200 dark:border-gray-800 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-full bg-amber-100 dark:bg-amber-900/20 flex items-center justify-center text-amber-600">
                        <span class="material-symbols-outlined text-xl">two_wheeler</span>
                    </div>
                </div>
                <p class="text-slate-500 dark:text-slate-400 text-xs font-medium uppercase tracking-wider">Pendapatan Driver</p>
                <p class="text-xl font-bold text-slate-900 dark:text-white mt-1">
                    Rp {{ number_format($totalDeliveryFee, 0, ',', '.') }}
                </p>
            </div>

            {{-- Total Keseluruhan --}}
            <div class="bg-white dark:bg-[#1a262e] p-4 md:p-5 rounded-xl border border-slate-200 dark:border-gray-800 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/20 flex items-center justify-center text-emerald-600">
                        <span class="material-symbols-outlined text-xl">payments</span>
                    </div>
                </div>
                <p class="text-slate-500 dark:text-slate-400 text-xs font-medium uppercase tracking-wider">Total Keseluruhan</p>
                <p class="text-xl font-bold text-slate-900 dark:text-white mt-1">
                    Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                </p>
            </div>

            {{-- Total Pesanan --}}
            <div class="bg-white dark:bg-[#1a262e] p-4 md:p-5 rounded-xl border border-slate-200 dark:border-gray-800 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-full bg-sky-100 dark:bg-sky-900/20 flex items-center justify-center text-sky-600">
                        <span class="material-symbols-outlined text-xl">shopping_cart</span>
                    </div>
                </div>
                <p class="text-slate-500 dark:text-slate-400 text-xs font-medium uppercase tracking-wider">Total Pesanan</p>
                <p class="text-xl font-bold text-slate-900 dark:text-white mt-1">{{ $totalOrders }}</p>
            </div>
        </div>

        {{-- Filter Tanggal --}}
        <div class="bg-white dark:bg-[#1a262e] p-4 md:p-6 rounded-xl border border-slate-200 dark:border-gray-800 shadow-sm mb-8">
            <form action="{{ route('reports.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 gap-6" autocomplete="off">
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tanggal Mulai</label>
                    <input name="start_date" class="w-full h-11 bg-slate-50 dark:bg-[#101c22] border-slate-200 dark:border-gray-700 rounded-xl px-4 text-sm text-slate-900 dark:text-white focus:ring-4 focus:ring-blue-500/10 transition-all" type="date" value="{{ $startDate }}" />
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tanggal Selesai</label>
                    <input name="end_date" class="w-full h-11 bg-slate-50 dark:bg-[#101c22] border-slate-200 dark:border-gray-700 rounded-xl px-4 text-sm text-slate-900 dark:text-white focus:ring-4 focus:ring-blue-500/10 transition-all" type="date" value="{{ $endDate }}" />
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Layanan Driver</label>
                    <select name="delivery_type" class="w-full h-11 bg-slate-50 dark:bg-[#101c22] border-slate-200 dark:border-gray-700 rounded-xl px-4 text-sm text-slate-900 dark:text-white focus:ring-4 focus:ring-blue-500/10 transition-all">
                        <option value="all" {{ $deliveryType == 'all' ? 'selected' : '' }}>Semua Layanan</option>
                        <option value="none" {{ $deliveryType == 'none' ? 'selected' : '' }}>Ambil Sendiri</option>
                        <option value="pickup" {{ $deliveryType == 'pickup' ? 'selected' : '' }}>Pick-up</option>
                        <option value="delivery" {{ $deliveryType == 'delivery' ? 'selected' : '' }}>Delivery</option>
                        <option value="both" {{ $deliveryType == 'both' ? 'selected' : '' }}>Pick-up & Delivery</option>
                    </select>
                </div>
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Metode Pembayaran</label>
                    <select name="payment_method" class="w-full h-11 bg-slate-50 dark:bg-[#101c22] border-slate-200 dark:border-gray-700 rounded-xl px-4 text-sm text-slate-900 dark:text-white focus:ring-4 focus:ring-blue-500/10 transition-all">
                        <option value="all" {{ $paymentMethod == 'all' ? 'selected' : '' }}>Semua Metode</option>
                        <option value="CASH" {{ $paymentMethod == 'CASH' ? 'selected' : '' }}>Tunai</option>
                        <option value="QRIS" {{ $paymentMethod == 'QRIS' ? 'selected' : '' }}>QRIS</option>
                    </select>
                </div>
                <div class="md:col-span-2 flex justify-end gap-3 pt-2">
                    <a href="{{ route('reports.index') }}" class="px-6 h-11 flex items-center text-sm font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-gray-800 rounded-xl transition-all">Reset</a>
                    <button type="submit" class="px-8 h-11 text-sm font-bold bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition-all shadow-md">Terapkan Filter</button>
                </div>
            </form>
        </div>

        {{-- Tabel Laporan --}}
        <form id="bulkDeleteForm" action="{{ route('reports.bulkDelete') }}" method="POST">
            @csrf
            @method('DELETE')
            <div class="bg-white dark:bg-[#1a262e] rounded-xl border border-slate-200 dark:border-gray-800 overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-gray-800 transition-all duration-300" id="tableHeader">
                    {{-- Header Normal (Saat Tidak Ada Pilihan) --}}
                    <div id="defaultHeader" class="flex justify-between items-center w-full">
                        <h3 class="text-slate-900 dark:text-white text-sm md:text-base font-bold">Data Detail Laporan</h3>
                        <a href="{{ route('reports.export', ['start_date' => $startDate, 'end_date' => $endDate, 'delivery_type' => $deliveryType, 'payment_method' => $paymentMethod]) }}"
                            class="flex items-center gap-2 px-4 h-9 bg-[#1D6F42] text-white text-xs font-bold rounded-lg hover:bg-[#155231] transition-all">
                            <span class="material-symbols-outlined text-lg">table_view</span>
                            <span>Ekspor Semua</span>
                        </a>
                    </div>

                    {{-- Header Bulk Action (Muncul Saat Checklist Tercentang) --}}
                    <div id="bulkHeader" class="hidden flex justify-between items-center w-full bg-blue-50 dark:bg-blue-900/10 -mx-6 -my-4 px-6 py-4">
                        <div class="flex items-center gap-3">
                            <span class="w-2 h-6 bg-blue-600 rounded-full"></span>
                            <span class="text-blue-700 dark:text-blue-400 text-sm font-bold" id="selectedCountText">0 Pesanan Terpilih</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="button" id="btnBulkExport"
                                class="flex items-center gap-2 px-4 h-9 bg-emerald-600 text-white text-xs font-bold rounded-lg hover:bg-emerald-700 shadow-sm transition-all">
                                <span class="material-symbols-outlined text-lg">download</span>
                                <span>Ekspor</span>
                            </button>
                            <button type="button" id="btnBulkDelete"
                                class="flex items-center gap-2 px-4 h-9 bg-rose-500 text-white text-xs font-bold rounded-lg hover:bg-rose-600 shadow-sm transition-all">
                                <span class="material-symbols-outlined text-lg">delete</span>
                                <span>Hapus</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left min-w-[900px]">
                        <thead class="bg-slate-50 dark:bg-slate-800/50">
                            <tr>
                                <th class="px-4 sm:px-6 py-3 sm:py-4 w-10">
                                    <input type="checkbox" id="selectAll" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                </th>
                                <th class="px-4 sm:px-6 py-3 sm:py-4 text-[10px] sm:text-[11px] font-bold text-slate-500 uppercase tracking-widest">No. Order</th>
                                <th class="px-4 sm:px-6 py-3 sm:py-4 text-[10px] sm:text-[11px] font-bold text-slate-500 uppercase tracking-widest">Pelanggan</th>
                                <th class="px-4 sm:px-6 py-3 sm:py-4 text-[10px] sm:text-[11px] font-bold text-slate-500 uppercase tracking-widest">Metode</th>
                                <th class="px-4 sm:px-6 py-3 sm:py-4 text-[10px] sm:text-[11px] font-bold text-slate-500 uppercase tracking-widest">Driver</th>
                                <th class="px-4 sm:px-6 py-3 sm:py-4 text-[10px] sm:text-[11px] font-bold text-slate-500 uppercase tracking-widest text-right">Pembayaran</th>
                                <th class="px-4 sm:px-6 py-3 sm:py-4 text-[10px] sm:text-[11px] font-bold text-slate-500 uppercase tracking-widest text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-gray-800">
    @php $currentMonth = null; @endphp {{-- Variabel bantuan untuk melacak bulan --}}
    
    @forelse($reports as $report)
        @php
            // Ambil nama bulan dan tahun dari transaksi saat ini
            // Kita gunakan translatedFormat agar namanya jadi "Januari", "Februari", dst.
            $monthName = $report->updated_at->translatedFormat('F Y');
        @endphp

        {{-- Jika bulan berubah dari baris sebelumnya, tampilkan Header Bulan --}}
        @if($monthName !== $currentMonth)
            <tr class="bg-slate-50/50 dark:bg-slate-800/30">
                <td colspan="7" class="px-4 sm:px-6 py-3">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">
                            {{ $monthName }}
                        </span>
                    </div>
                </td>
            </tr>
            @php $currentMonth = $monthName; @endphp
        @endif

        {{-- Baris Data Transaksi (Tetap seperti kode lama Anda) --}}
        <tr class="hover:bg-slate-50 dark:hover:bg-gray-800/30 transition-colors">
            <td class="px-4 sm:px-6 py-3 sm:py-4">
                <input type="checkbox" name="ids[]" value="{{ $report->id }}" class="report-checkbox rounded border-slate-300 text-blue-600 focus:ring-blue-500">
            </td>
            <td class="px-4 sm:px-6 py-3 sm:py-4">
                <p class="text-xs sm:text-sm font-bold text-blue-600">#{{ $report->order_number ?? $report->invoice_number }}</p>
                <p class="text-[10px] text-slate-500">{{ $report->updated_at->format('d/m/Y H:i') }}</p>
            </td>
            <td class="px-4 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm">
                <p class="font-medium text-slate-900 dark:text-white">{{ $report->customer_name }}</p>
                <div class="text-xs text-slate-500 mt-0.5 flex flex-col gap-0.5">
                    @if($report->items && $report->items->count() > 0)
                        @foreach($report->items as $item)
                            @php
                                $serviceName = explode('||', $item->service_name)[0];
                                $unitDb = strtolower($item->unit ?? '');
                                $isPcs = ($unitDb == 'pcs' || stripos($serviceName, 'satuan') !== false || stripos($serviceName, 'pcs') !== false);
                                $weightValue = $isPcs ? (int)$item->qty_or_weight : $item->qty_or_weight;
                                $alreadyHasUnit = preg_match('/\d+(\.\d+)?\s*(pcs|kg)/i', $serviceName);
                            @endphp
                            <span>
                                @if($alreadyHasUnit)
                                    {{ $serviceName }}
                                @else
                                    {{ $serviceName }} ({{ $weightValue }} {{ $isPcs ? 'Pcs' : 'Kg' }})
                                @endif
                            </span>
                        @endforeach
                    @else
                        @php
                            $serviceName = explode('||', $report->service_name)[0];
                            $unitDb = strtolower($report->unit ?? '');
                            $isPcs = ($unitDb == 'pcs' || stripos($serviceName, 'satuan') !== false || stripos($serviceName, 'pcs') !== false);
                            $weightValue = $isPcs ? (int)$report->weight : $report->weight;
                            $alreadyHasUnit = preg_match('/\d+(\.\d+)?\s*(pcs|kg)/i', $serviceName);
                        @endphp
                        <span>
                            @if($alreadyHasUnit)
                                {{ $serviceName }}
                            @else
                                {{ $serviceName }} ({{ $weightValue }} {{ $isPcs ? 'Pcs' : 'Kg' }})
                            @endif
                        </span>
                    @endif
                </div>
            </td>
            <td class="px-4 sm:px-6 py-3 sm:py-4">
                @if(strtolower($report->payment_method) == 'qris')
                    <span class="px-2 py-1 bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 rounded text-[10px] font-bold uppercase">QRIS</span>
                @else
                    <span class="px-2 py-1 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded text-[10px] font-bold uppercase">Tunai</span>
                @endif
            </td>
            <td class="px-4 sm:px-6 py-3 sm:py-4">
                @if($report->delivery_type == 'none' || !$report->delivery_type)
                    <span class="text-slate-400 text-[10px] font-medium">Ambil Sendiri</span>
                @else
                    @php
                        $typeLabel = "Ambil Sendiri";
                        if ($report->delivery_type === 'pickup') $typeLabel = "Pick-up";
                        elseif ($report->delivery_type === 'delivery') $typeLabel = "Delivery";
                        elseif ($report->delivery_type === 'both') $typeLabel = "Pick-up & Delivery";
                    @endphp
                    <div class="flex flex-col">
                        <span class="px-2 py-0.5 bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 rounded text-[10px] font-bold uppercase w-fit">{{ $typeLabel }}</span>
                        @if($report->delivery_fee > 0)
                            <span class="text-[10px] text-slate-500 mt-1">Ongkir: Rp {{ number_format($report->delivery_fee, 0, ',', '.') }}</span>
                        @endif
                    </div>
                @endif
            </td>
            <td class="px-4 sm:px-6 py-3 sm:py-4 text-right">
                @if(strtolower($report->payment_method) != 'qris')
                    <div class="flex flex-col items-end">
                        <span class="text-[11px] text-slate-500">Uang: Rp {{ number_format($report->cash_received ?? 0, 0, ',', '.') }}</span>
                        <span class="text-[11px] font-bold text-orange-600">Kembali: Rp {{ number_format($report->cash_change ?? 0, 0, ',', '.') }}</span>
                    </div>
                @else
                    <span class="text-xs text-slate-400 italic">Nominal Pas</span>
                @endif
            </td>
            <td class="px-4 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm font-bold text-slate-900 dark:text-white text-right">
                Rp {{ number_format($report->total_price, 0, ',', '.') }}
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="7" class="px-4 sm:px-6 py-12 sm:py-20 text-center text-slate-500 text-sm">Belum ada transaksi untuk periode ini.</td>
        </tr>
    @endforelse
</tbody>
                    </table>
                </div>

                {{-- Mobile Card Layout --}}
                <div class="block md:hidden divide-y divide-slate-100 dark:divide-gray-800">
                    @php $currentMonthMobile = null; @endphp
                    
                    @forelse($reports as $report)
                        @php
                            $monthNameMobile = $report->updated_at->translatedFormat('F Y');
                        @endphp

                        @if($monthNameMobile !== $currentMonthMobile)
                            <div class="bg-slate-50/50 dark:bg-slate-800/30 px-4 py-2 border-y border-slate-100 dark:border-gray-800">
                                <div class="flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                    <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">
                                        {{ $monthNameMobile }}
                                    </span>
                                </div>
                            </div>
                            @php $currentMonthMobile = $monthNameMobile; @endphp
                        @endif

                        <div class="p-4 hover:bg-slate-50 dark:hover:bg-gray-800/30 transition-colors relative">
                            <div class="absolute top-4 right-4">
                                <input type="checkbox" name="ids[]" value="{{ $report->id }}" class="report-checkbox rounded border-slate-300 text-blue-600 focus:ring-blue-500 w-5 h-5">
                            </div>
                            <div class="pr-8 mb-2">
                                <p class="text-xs font-bold text-blue-600">#{{ $report->order_number ?? $report->invoice_number }}</p>
                                <h3 class="font-semibold text-slate-900 dark:text-white text-sm mt-0.5">{{ $report->customer_name }}</h3>
                                <p class="text-[10px] text-slate-500 mt-0.5">{{ $report->updated_at->format('d/m/Y H:i') }}</p>
                            </div>

                            <div class="text-xs text-slate-500 mb-3 flex flex-col gap-0.5">
                                @if($report->items && $report->items->count() > 0)
                                    @foreach($report->items as $item)
                                        @php
                                            $serviceName = explode('||', $item->service_name)[0];
                                            $unitDb = strtolower($item->unit ?? '');
                                            $isPcs = ($unitDb == 'pcs' || stripos($serviceName, 'satuan') !== false || stripos($serviceName, 'pcs') !== false);
                                            $weightValue = $isPcs ? (int)$item->qty_or_weight : $item->qty_or_weight;
                                            $alreadyHasUnit = preg_match('/\d+(\.\d+)?\s*(pcs|kg)/i', $serviceName);
                                        @endphp
                                        <span>
                                            @if($alreadyHasUnit)
                                                {!! nl2br(e($serviceName)) !!}
                                            @else
                                                {!! nl2br(e($serviceName)) !!} ({{ $weightValue }} {{ $isPcs ? 'Pcs' : 'Kg' }})
                                            @endif
                                        </span>
                                    @endforeach
                                @else
                                    @php
                                        $serviceName = explode('||', $report->service_name)[0];
                                        $unitDb = strtolower($report->unit ?? '');
                                        $isPcs = ($unitDb == 'pcs' || stripos($serviceName, 'satuan') !== false || stripos($serviceName, 'pcs') !== false);
                                        $weightValue = $isPcs ? (int)$report->weight : $report->weight;
                                        $alreadyHasUnit = preg_match('/\d+(\.\d+)?\s*(pcs|kg)/i', $serviceName);
                                    @endphp
                                    <span>
                                        @if($alreadyHasUnit)
                                            {!! nl2br(e($serviceName)) !!}
                                        @else
                                            {!! nl2br(e($serviceName)) !!} ({{ $weightValue }} {{ $isPcs ? 'Pcs' : 'Kg' }})
                                        @endif
                                    </span>
                                @endif
                            </div>

                            <div class="flex flex-wrap gap-2 mb-3">
                                @if(strtolower($report->payment_method) == 'qris')
                                    <span class="px-2 py-0.5 bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 rounded text-[9px] font-bold uppercase">QRIS</span>
                                @else
                                    <span class="px-2 py-0.5 bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 rounded text-[9px] font-bold uppercase">TUNAI</span>
                                @endif

                                @if($report->delivery_type == 'none' || !$report->delivery_type)
                                    <span class="px-2 py-0.5 bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400 rounded text-[9px] font-bold uppercase">AMBIL SENDIRI</span>
                                @else
                                    @php
                                        $typeLabel = "Ambil Sendiri";
                                        if ($report->delivery_type === 'pickup') $typeLabel = "Pick-up";
                                        elseif ($report->delivery_type === 'delivery') $typeLabel = "Delivery";
                                        elseif ($report->delivery_type === 'both') $typeLabel = "Pick-up & Delivery";
                                    @endphp
                                    <span class="px-2 py-0.5 bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 rounded text-[9px] font-bold uppercase">{{ $typeLabel }}</span>
                                @endif
                            </div>

                            <div class="mt-3 pt-3 border-t border-slate-100 dark:border-gray-800 flex justify-between items-end">
                                <div>
                                    @if(strtolower($report->payment_method) != 'qris')
                                        <div class="flex flex-col">
                                            <span class="text-[10px] text-slate-500">Uang: Rp {{ number_format($report->cash_received ?? 0, 0, ',', '.') }}</span>
                                            <span class="text-[10px] font-bold text-orange-600">Kembali: Rp {{ number_format($report->cash_change ?? 0, 0, ',', '.') }}</span>
                                        </div>
                                    @else
                                        <span class="text-[10px] text-slate-400 italic">Nominal Pas</span>
                                    @endif
                                    @if($report->delivery_fee > 0)
                                        <span class="text-[10px] text-blue-500 font-medium block mt-0.5">Ongkir: Rp {{ number_format($report->delivery_fee, 0, ',', '.') }}</span>
                                    @endif
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] text-slate-500 block">Total Pembayaran</span>
                                    <span class="text-sm font-bold text-slate-900 dark:text-white">Rp {{ number_format($report->total_price, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-slate-500 text-xs">Belum ada transaksi untuk periode ini.</div>
                    @endforelse
                </div>
            </div>
        </form>
    </div>

    {{-- Script untuk Interaksi --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.report-checkbox');
            const btnDelete = document.getElementById('btnBulkDelete');
            const btnExport = document.getElementById('btnBulkExport');
            const form = document.getElementById('bulkDeleteForm');

            const defaultHeader = document.getElementById('defaultHeader');
            const bulkHeader = document.getElementById('bulkHeader');
            const selectedCountText = document.getElementById('selectedCountText');

            // Fungsi cek tombol aktif/tidak
            function toggleButtons() {
                const checkedCount = document.querySelectorAll('.report-checkbox:checked').length;
                
                if (checkedCount > 0) {
                    defaultHeader.classList.add('hidden');
                    bulkHeader.classList.remove('hidden');
                    bulkHeader.classList.add('flex');
                    selectedCountText.innerText = `${checkedCount} Pesanan Terpilih`;
                } else {
                    defaultHeader.classList.remove('hidden');
                    bulkHeader.classList.add('hidden');
                    bulkHeader.classList.remove('flex');
                }
            }

            // Pilih semua
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = selectAll.checked);
                toggleButtons();
            });

            // Pilih satuan
            checkboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    toggleButtons();
                    // Update selectAll state
                    if (!this.checked) selectAll.checked = false;
                    if (document.querySelectorAll('.report-checkbox:checked').length === checkboxes.length) {
                        selectAll.checked = true;
                    }
                });
            });

            // Konfirmasi Hapus
            btnDelete.addEventListener('click', function() {
                const count = document.querySelectorAll('.report-checkbox:checked').length;
                
                Swal.fire({
                    title: 'HAPUS LAPORAN?',
                    text: `Anda akan menghapus ${count} data laporan terpilih secara permanen.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#f43f5e',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'YA, HAPUS',
                    cancelButtonText: 'BATAL',
                    borderRadius: '1rem'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Pastikan method adalah DELETE
                        const methodInput = form.querySelector('input[name="_method"]');
                        if (methodInput) methodInput.value = 'DELETE';
                        form.action = "{{ route('reports.bulkDelete') }}";
                        form.submit();
                    }
                });
            });

            // Aksi Ekspor Terpilih
            if(btnExport) {
                btnExport.addEventListener('click', function() {
                    // Ubah form action ke route ekspor massal
                    const originalAction = form.action;
                    form.action = "{{ route('reports.exportBulk') }}";
                    
                    // Nonaktifkan _method DELETE karena ekspor menggunakan POST
                    const methodInput = form.querySelector('input[name="_method"]');
                    if(methodInput) methodInput.disabled = true;

                    form.submit();

                    // Kembalikan form ke state semula setelah disubmit
                    setTimeout(() => {
                        form.action = originalAction;
                        if(methodInput) methodInput.disabled = false;
                    }, 500);
                });
            }
        });
    </script>
</x-app-layout>
