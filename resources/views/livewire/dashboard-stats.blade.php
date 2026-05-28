<div wire:poll.1s>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-6">
        
        {{-- Card Total Pesanan --}}
        <div class="bg-white dark:bg-[#1a262e] p-3 md:p-6 rounded-xl border border-[#dbe2e6] dark:border-[#2a3a44] shadow-sm flex flex-col gap-2 md:gap-4">
            <div class="w-7 h-7 md:w-10 md:h-10 flex items-center justify-center bg-primary/10 text-primary rounded-lg">
                <span class="material-symbols-outlined text-lg md:text-2xl">format_list_numbered</span>
            </div>
            <div>
                <h3 class="text-[#111618] dark:text-white text-base md:text-2xl font-bold tracking-tight">{{ number_format($total) }}</h3>
                <p class="text-[#617c89] dark:text-[#a0b4be] text-[10px] md:text-sm font-medium">Total Pesanan</p>
            </div>
        </div>

        {{-- Card Antrian (Klik-able) --}}
        <a href="{{ route('orders.by_status', 'antrian') }}" class="bg-white dark:bg-[#1a262e] p-3 md:p-6 rounded-xl border border-[#dbe2e6] dark:border-[#2a3a44] shadow-sm flex flex-col gap-2 md:gap-4 hover:border-orange-400 transition-all group">
            <div class="w-7 h-7 md:w-10 md:h-10 flex items-center justify-center bg-orange-100 text-orange-600 rounded-lg group-hover:bg-orange-600 group-hover:text-white transition-colors">
                <span class="material-symbols-outlined text-lg md:text-2xl">hourglass_top</span>
            </div>
            <div>
                <h3 class="text-[#111618] dark:text-white text-base md:text-2xl font-bold tracking-tight">{{ $antrian }}</h3>
                <p class="text-[#617c89] dark:text-[#a0b4be] text-[10px] md:text-sm font-medium">Antrian</p>
            </div>
        </a>

        {{-- Card Proses (Klik-able) --}}
        <a href="{{ route('orders.by_status', 'proses') }}" class="bg-white dark:bg-[#1a262e] p-3 md:p-6 rounded-xl border border-[#dbe2e6] dark:border-[#2a3a44] shadow-sm flex flex-col gap-2 md:gap-4 hover:border-blue-400 transition-all group">
            <div class="w-7 h-7 md:w-10 md:h-10 flex items-center justify-center bg-blue-100 text-blue-600 rounded-lg group-hover:bg-blue-600 group-hover:text-white transition-colors">
                <span class="material-symbols-outlined text-lg md:text-2xl">waves</span>
            </div>
            <div>
                <h3 class="text-[#111618] dark:text-white text-base md:text-2xl font-bold tracking-tight">{{ $proses }}</h3>
                <p class="text-[#617c89] dark:text-[#a0b4be] text-[10px] md:text-sm font-medium">Proses</p>
            </div>
        </a>

        {{-- Card Selesai (Klik-able) --}}
        <a href="{{ route('orders.by_status', 'selesai') }}" class="bg-white dark:bg-[#1a262e] p-3 md:p-6 rounded-xl border border-[#dbe2e6] dark:border-[#2a3a44] shadow-sm flex flex-col gap-2 md:gap-4 hover:border-green-400 transition-all group">
            <div class="w-7 h-7 md:w-10 md:h-10 flex items-center justify-center bg-green-100 text-green-600 rounded-lg group-hover:bg-green-600 group-hover:text-white transition-colors">
                <span class="material-symbols-outlined text-lg md:text-2xl">check_circle</span>
            </div>
            <div>
                <h3 class="text-[#111618] dark:text-white text-base md:text-2xl font-bold tracking-tight">{{ number_format($selesai) }}</h3>
                <p class="text-[#617c89] dark:text-[#a0b4be] text-[10px] md:text-sm font-medium">Selesai</p>
            </div>
        </a>
    </div>
</div>
