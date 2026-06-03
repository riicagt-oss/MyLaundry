<div wire:poll.1s class="bg-white dark:bg-[#1a262e] rounded-xl border border-[#dbe2e6] dark:border-[#2a3a44] shadow-sm overflow-hidden">
    <div class="px-4 sm:px-8 py-4 sm:py-5 border-b border-[#dbe2e6] dark:border-[#2a3a44] flex items-center justify-between">
        <div>
            <h2 class="text-[#111618] dark:text-white text-base sm:text-xl font-bold tracking-tight">Pesanan Aktif Terbaru</h2>
        </div>
    </div>
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-left min-w-[540px]">
            <thead class="bg-[#f8fafc] dark:bg-[#23313a] text-[#617c89] dark:text-[#a0b4be] text-[10px] sm:text-xs uppercase font-bold tracking-wider">
                <tr>
                    <th class="px-4 sm:px-8 py-3 sm:py-4">ID Pesanan</th>
                    <th class="px-4 sm:px-8 py-3 sm:py-4">Pelanggan</th>
                    <th class="px-4 sm:px-8 py-3 sm:py-4">Layanan</th>
                    <th class="px-4 sm:px-8 py-3 sm:py-4">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#dbe2e6] dark:divide-[#2a3a44] text-sm">
                @forelse($latest as $order)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                    <td class="px-4 sm:px-8 py-3 sm:py-4 font-bold text-primary italic text-xs sm:text-sm">#{{ $order->order_number }}</td>
                    <td class="px-4 sm:px-8 py-3 sm:py-4 text-[#111618] dark:text-white font-semibold text-xs sm:text-sm">{{ $order->customer_name }}</td>

                    <td class="px-4 sm:px-8 py-3 sm:py-4 text-[#617c89] dark:text-[#a0b4be] font-medium">
                        <div class="flex flex-col">
                            @if(in_array($order->status, ['PICKUP', 'MENUNGGU JEMPUT', 'TIBA DI TOKO', 'JEMPUTAN TIBA']))
                                <span class="text-xs sm:text-sm">-</span>
                            @else
                                <span class="text-xs sm:text-sm">{!! nl2br(e(explode('||', $order->service_name ?? 'Layanan Umum')[0])) !!}</span>
                            @endif
                            <div class="flex flex-wrap items-center gap-2 mt-2">
                                @if(!in_array($order->status, ['PICKUP', 'MENUNGGU JEMPUT', 'TIBA DI TOKO', 'JEMPUTAN TIBA']))
                                    @if(strtoupper($order->payment_method) == 'BAYAR NANTI' && $order->cash_received == 0)
                                        <span class="text-[10px] font-bold text-red-500">BELUM BAYAR</span>
                                    @else
                                        <span class="text-[10px] font-bold text-green-500">LUNAS</span>
                                    @endif

                                    @php
                                        $tipeEstimasi = "Reguler";
                                        if ($order->estimation_time && $order->created_at) {
                                            $diffInHours = $order->created_at->diffInHours($order->estimation_time);
                                            if ($diffInHours <= 6) $tipeEstimasi = "Kilat";
                                            elseif ($diffInHours <= 24) $tipeEstimasi = "Ekspres";
                                        } else {
                                            if (preg_match('/Kilat/i', $order->service_name)) $tipeEstimasi = "Kilat";
                                            elseif (preg_match('/Ekspres|Express/i', $order->service_name)) $tipeEstimasi = "Ekspres";
                                        }
                                    @endphp
                                    <span class="text-[10px] bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 px-2 py-0.5 rounded-full font-bold">
                                        {{ $tipeEstimasi }}
                                    </span>
                                @endif

                                @if($order->delivery_type && $order->delivery_type !== 'none')
                                    @php
                                        $typeLabel = "";
                                        if ($order->delivery_type === 'pickup') $typeLabel = "Pick-up";
                                        elseif ($order->delivery_type === 'delivery') $typeLabel = "Delivery";
                                        elseif ($order->delivery_type === 'both') $typeLabel = "Pick-up & Delivery";
                                    @endphp
                                    <span class="text-[10px] bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400 px-2 py-0.5 rounded-full font-bold">
                                        {{ $typeLabel }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </td>

                    <td class="px-4 sm:px-8 py-3 sm:py-4">
                        @if($order->status == 'PROSES')
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] sm:text-xs font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-1.5"></span> PROSES
                        </span>
                        @elseif($order->status == 'ANTRIAN')
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] sm:text-xs font-bold bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-orange-500 mr-1.5"></span> ANTRIAN
                        </span>
                        @elseif($order->status == 'SELESAI')
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] sm:text-xs font-bold bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5"></span> SELESAI
                        </span>
                        @elseif(in_array($order->status, ['PICKUP', 'MENUNGGU JEMPUT']))
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] sm:text-xs font-bold bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-yellow-500 mr-1.5"></span> PICKUP
                        </span>
                        @elseif($order->status == 'TIBA DI TOKO' || $order->status == 'JEMPUTAN TIBA')
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] sm:text-xs font-bold bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-purple-500 mr-1.5"></span> TIBA DI TOKO
                        </span>
                        @else
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] sm:text-xs font-bold bg-gray-100 text-gray-700 dark:bg-gray-800/30 dark:text-gray-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-500 mr-1.5"></span> {{ $order->status }}
                        </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 sm:px-8 py-8 sm:py-10 text-center text-[#617c89] font-medium text-sm">Belum ada pesanan aktif saat ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile Card Layout --}}
    <div class="block md:hidden divide-y divide-[#dbe2e6] dark:divide-[#2a3a44]">
        @forelse($latest as $order)
        <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <span class="font-bold text-primary italic text-xs">#{{ $order->order_number }}</span>
                    <h3 class="text-[#111618] dark:text-white font-semibold text-sm mt-0.5">{{ $order->customer_name }}</h3>
                </div>
                <div>
                    @if($order->status == 'PROSES')
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-1.5"></span> PROSES
                    </span>
                    @elseif($order->status == 'ANTRIAN')
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-orange-500 mr-1.5"></span> ANTRIAN
                    </span>
                    @elseif($order->status == 'SELESAI')
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5"></span> SELESAI
                    </span>
                    @elseif(in_array($order->status, ['PICKUP', 'MENUNGGU JEMPUT']))
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-yellow-500 mr-1.5"></span> PICKUP
                    </span>
                    @elseif($order->status == 'TIBA DI TOKO' || $order->status == 'JEMPUTAN TIBA')
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-purple-500 mr-1.5"></span> TIBA DI TOKO
                    </span>
                    @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-700 dark:bg-gray-800/30 dark:text-gray-400">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-500 mr-1.5"></span> {{ $order->status }}
                    </span>
                    @endif
                </div>
            </div>
            
            <div class="text-[#617c89] dark:text-[#a0b4be] text-xs mt-2">
                @if(in_array($order->status, ['PICKUP', 'MENUNGGU JEMPUT', 'TIBA DI TOKO', 'JEMPUTAN TIBA']))
                    <span class="block mb-1.5">-</span>
                @else
                    <span class="block mb-1.5">{!! nl2br(e(explode('||', $order->service_name ?? 'Layanan Umum')[0])) !!}</span>
                @endif
                <div class="flex flex-wrap items-center gap-2 mt-1">
                    @if(!in_array($order->status, ['PICKUP', 'MENUNGGU JEMPUT', 'TIBA DI TOKO', 'JEMPUTAN TIBA']))
                        @if(strtoupper($order->payment_method) == 'BAYAR NANTI' && $order->cash_received == 0)
                            <span class="text-[9px] font-bold text-red-500">BELUM BAYAR</span>
                        @else
                            <span class="text-[9px] font-bold text-green-500">LUNAS</span>
                        @endif

                        @php
                            $tipeEstimasi = "Reguler";
                            if ($order->estimation_time && $order->created_at) {
                                $diffInHours = $order->created_at->diffInHours($order->estimation_time);
                                if ($diffInHours <= 6) $tipeEstimasi = "Kilat";
                                elseif ($diffInHours <= 24) $tipeEstimasi = "Ekspres";
                            } else {
                                if (preg_match('/Kilat/i', $order->service_name)) $tipeEstimasi = "Kilat";
                                elseif (preg_match('/Ekspres|Express/i', $order->service_name)) $tipeEstimasi = "Ekspres";
                            }
                        @endphp
                        <span class="text-[9px] bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400 px-2 py-0.5 rounded-full font-bold">
                            {{ $tipeEstimasi }}
                        </span>
                    @endif

                    @if($order->delivery_type && $order->delivery_type !== 'none')
                        @php
                            $typeLabel = "";
                            if ($order->delivery_type === 'pickup') $typeLabel = "Pick-up";
                            elseif ($order->delivery_type === 'delivery') $typeLabel = "Delivery";
                            elseif ($order->delivery_type === 'both') $typeLabel = "Pick-up & Delivery";
                        @endphp
                        <span class="text-[9px] bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400 px-2 py-0.5 rounded-full font-bold">
                            {{ $typeLabel }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="p-6 text-center text-[#617c89] text-xs">Belum ada pesanan aktif saat ini.</div>
        @endforelse
    </div>
</div>
