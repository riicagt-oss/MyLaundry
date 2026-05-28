<x-app-layout>
    <div class="w-full mx-auto space-y-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard') }}" class="flex items-center justify-center w-10 h-10 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors text-[#111618] dark:text-white">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h2 class="text-[#111618] dark:text-white text-lg md:text-2xl font-bold tracking-tight">Daftar {{ ucfirst(strtolower($statusUpper)) }}</h2>
        </div>

        <div class="bg-white dark:bg-[#1a262e] rounded-xl border border-[#dbe2e6] dark:border-[#2a3a44] shadow-sm overflow-hidden">
            
            {{-- Toolbar: Tombol Hapus (Kiri) & Search (Kanan) --}}
            <div class="px-4 md:px-6 py-4 border-b border-[#dbe2e6] dark:border-[#2a3a44] flex flex-col md:flex-row md:items-center justify-between gap-4">
                
                {{-- Tombol Hapus Terpilih --}}
                <button type="submit" form="bulk-delete-form" id="btn-bulk-delete" disabled
                    class="flex items-center gap-2 px-4 py-2 bg-red-200 text-white text-sm font-bold rounded-lg transition-all cursor-not-allowed">
                    <span class="material-symbols-outlined text-sm">delete</span>
                    Hapus Terpilih
                </button>

                {{-- Form Pencarian --}}
                <form action="{{ url()->current() }}" method="GET" id="search-form" class="relative w-full md:w-72" autocomplete="off">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#617c89] text-xl">search</span>
                    
                    {{-- autocomplete="off" untuk menghapus riwayat saran browser --}}
                    <input name="search" id="search-input" value="{{ request('search') }}" 
                        autocomplete="off" 
                        class="w-full pl-10 pr-4 py-2 rounded-lg border border-[#dbe2e6] dark:border-[#2a3a44] bg-[#f8fafc] dark:bg-[#23313a] text-sm focus:ring-primary focus:border-primary transition-all" 
                        placeholder="Cari pelanggan..." type="text"/>
                </form>
            </div>

            {{-- Form pembungkus untuk fitur hapus massal --}}
            <form action="{{ route('orders.bulkDelete') }}" method="POST" id="bulk-delete-form" onsubmit="event.preventDefault(); confirmBulkDelete();">
                @csrf
                @method('DELETE')

                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left min-w-[800px]">
                        <thead class="bg-[#f8fafc] dark:bg-[#23313a] text-[#617c89] dark:text-[#a0b4be] text-[10px] sm:text-xs uppercase font-bold tracking-wider">
                            <tr>
                                <th class="px-4 sm:px-6 py-3 text-center w-10">
                                    <input type="checkbox" id="select-all" class="rounded border-gray-300 text-primary focus:ring-primary">
                                </th>
                                <th class="px-4 sm:px-6 py-3">ID Pesanan</th>
                                <th class="px-4 sm:px-6 py-3">Pelanggan</th>
                                <th class="px-4 sm:px-6 py-3">Layanan</th>
                                <th class="px-4 sm:px-6 py-3">Pembayaran</th>
                                <th class="px-4 sm:px-6 py-3">Waktu Masuk</th>
                                <th class="px-4 sm:px-6 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#dbe2e6] dark:divide-[#2a3a44] text-sm">
                            @forelse($orders as $order)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-4 sm:px-6 py-3 sm:py-4 text-center">
                                    <input type="checkbox" name="selected_ids[]" value="{{ $order->id }}" class="order-checkbox rounded border-gray-300 text-primary focus:ring-primary">
                                </td>
                                <td class="px-4 sm:px-6 py-3 sm:py-4 font-bold text-primary text-xs sm:text-sm">#{{ $order->order_number }}</td>
                                <td class="px-4 sm:px-6 py-3 sm:py-4 font-medium text-slate-900 dark:text-white text-xs sm:text-sm">{{ $order->customer_name }}</td>
                                <td class="px-4 sm:px-6 py-3 sm:py-4 text-[#617c89] dark:text-[#a0b4be] text-xs sm:text-sm">
                                    {!! nl2br(e(explode('||', $order->service_name)[0])) !!}
                                    <div class="flex flex-wrap items-center gap-2 mt-2">
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
                                </td>
                                <td class="px-4 sm:px-6 py-3 sm:py-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-slate-700 dark:text-slate-200">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                                        @if(strtoupper($order->payment_method) == 'BAYAR NANTI' && $order->cash_received == 0)
                                            <span class="text-[10px] font-bold text-red-500 uppercase">BELUM BAYAR</span>
                                        @else
                                            <span class="text-[10px] font-bold text-green-500 uppercase">LUNAS ({{ strtoupper($order->payment_method) == 'CASH' ? 'TUNAI' : $order->payment_method }})</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 sm:px-6 py-3 sm:py-4 text-[#617c89] dark:text-[#a0b4be] text-xs">{{ $order->created_at->format('H:i, d M Y') }}</td>
                                <td class="px-4 sm:px-6 py-3 sm:py-4 text-center">
                                    @php
                                        $color = [
                                            'ANTRIAN' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                                            'PROSES' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                            'SELESAI' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                            'PICKUP' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                            'TIBA DI TOKO' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                                            'JEMPUTAN TIBA' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                                        ][$order->status] ?? 'bg-gray-100';
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-[10px] sm:text-xs font-bold whitespace-nowrap {{ $color }}">
                                        {{ $order->status }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-4 sm:px-6 py-8 sm:py-10 text-center text-[#617c89] text-sm">
                                    Tidak ada data pesanan @if(request('search')) untuk "{{ request('search') }}" @endif.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Card Layout --}}
                <div class="block md:hidden divide-y divide-[#dbe2e6] dark:divide-[#2a3a44]">
                    @forelse($orders as $order)
                    <div class="p-4 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors relative">
                        <div class="absolute top-4 right-4">
                            <input type="checkbox" name="selected_ids[]" value="{{ $order->id }}" class="order-checkbox rounded border-gray-300 text-primary focus:ring-primary w-5 h-5">
                        </div>
                        <div class="pr-8">
                            <span class="font-bold text-primary italic text-xs">#{{ $order->order_number }}</span>
                            <h3 class="text-[#111618] dark:text-white font-semibold text-sm mt-0.5">{{ $order->customer_name }}</h3>
                            <div class="text-[#617c89] dark:text-[#a0b4be] text-xs mt-0.5">{{ $order->created_at->format('H:i, d M Y') }}</div>
                        </div>
                        
                        <div class="text-[#617c89] dark:text-[#a0b4be] text-xs mt-3">
                            <span class="block mb-1.5">{!! nl2br(e(explode('||', $order->service_name)[0])) !!}</span>
                            <div class="flex flex-wrap items-center gap-2 mt-1">
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

                        <div class="mt-3 pt-3 border-t border-[#dbe2e6] dark:border-[#2a3a44] flex justify-between items-center">
                            <div class="flex flex-col">
                                <span class="font-bold text-slate-700 dark:text-slate-200 text-sm">Rp {{ number_format($order->total_price, 0, ',', '.') }}</span>
                                @if(strtoupper($order->payment_method) == 'BAYAR NANTI' && $order->cash_received == 0)
                                    <span class="text-[9px] font-bold text-red-500 uppercase">BELUM BAYAR</span>
                                @else
                                    <span class="text-[9px] font-bold text-green-500 uppercase">LUNAS ({{ strtoupper($order->payment_method) == 'CASH' ? 'TUNAI' : $order->payment_method }})</span>
                                @endif
                            </div>
                            <div>
                                @php
                                    $color = [
                                        'ANTRIAN' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                                        'PROSES' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                        'SELESAI' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                        'PICKUP' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                        'TIBA DI TOKO' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                                        'JEMPUTAN TIBA' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                                    ][$order->status] ?? 'bg-gray-100';
                                @endphp
                                <span class="px-2 py-1 rounded-full text-[10px] font-bold whitespace-nowrap {{ $color }}">
                                    {{ $order->status }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-6 text-center text-[#617c89] text-xs">
                        Tidak ada data pesanan @if(request('search')) untuk "{{ request('search') }}" @endif.
                    </div>
                    @endforelse
                </div>
                
                @if($orders->hasPages())
                <div class="px-4 md:px-6 py-4 border-t border-[#dbe2e6] dark:border-[#2a3a44]">
                    {{ $orders->appends(request()->query())->links() }}
                </div>
                @endif
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmBulkDelete() {
            Swal.fire({
                title: 'HAPUS PESANAN?',
                text: 'Apakah Anda yakin ingin menghapus data pesanan yang dipilih?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#13a4ec',
                cancelButtonColor: '#f43f5e',
                confirmButtonText: 'YA, HAPUS',
                cancelButtonText: 'BATAL',
                borderRadius: '1.5rem'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('bulk-delete-form');
                    form.onsubmit = null;
                    form.submit();
                }
            });
        }

        // --- LOGIKA SEARCH OTOMATIS (LIVE SEARCH) ---
        const searchInput = document.getElementById('search-input');
        const searchForm = document.getElementById('search-form');
        let timer;

        // Auto-focus kursor ke posisi terakhir setelah halaman reload
        window.onload = function() {
            if (searchInput.value !== "") {
                searchInput.focus();
                const val = searchInput.value;
                searchInput.value = '';
                searchInput.value = val;
            }
        };

        searchInput.addEventListener('input', function() {
            clearTimeout(timer);
            // Jeda 500ms agar server tidak kelelahan saat user mengetik cepat
            timer = setTimeout(() => {
                searchForm.submit();
            }, 500); 
        });


        // --- LOGIKA CHECKBOX & TOMBOL HAPUS DINAMIS ---
        const selectAll = document.getElementById('select-all');
        const checkboxes = document.querySelectorAll('.order-checkbox');
        const btnDelete = document.getElementById('btn-bulk-delete');

        function updateButtonState() {
            const checkedCount = document.querySelectorAll('.order-checkbox:checked').length;
            
            if (checkedCount > 0) {
                btnDelete.disabled = false;
                btnDelete.classList.replace('bg-red-200', 'bg-red-600');
                btnDelete.classList.replace('cursor-not-allowed', 'hover:bg-red-700');
            } else {
                btnDelete.disabled = true;
                btnDelete.classList.replace('bg-red-600', 'bg-red-200');
                btnDelete.classList.add('cursor-not-allowed');
                btnDelete.classList.remove('hover:bg-red-700');
            }
        }

        selectAll.addEventListener('change', function() {
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateButtonState();
        });

        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateButtonState);
        });
    </script>
</x-app-layout>