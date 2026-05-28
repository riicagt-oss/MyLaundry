<x-app-layout>
    {{-- Wrapper utama disamakan persis dengan halaman Kelola Satuan --}}
    <div class="w-full mx-auto">
        
        {{-- Header Section --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-lg md:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Dashboard Beranda</h2>
                <p class="text-slate-500 mt-1 text-xs">Ringkasan performa dan pesanan aktif laundry Anda.</p>
            </div>
        </div>

        {{-- Statistik Ringkas --}}
        <div class="mt-8 mb-8">
            <livewire:dashboard-stats />
        </div>

        {{-- Section Grafik Pelanggan --}}
        <div class="bg-white dark:bg-[#1a262e] p-3 md:p-8 rounded-xl border border-[#dbe2e6] dark:border-[#2a3a44] shadow-sm mb-4 md:mb-8 relative overflow-hidden">
            <div class="mb-4 sm:mb-8 relative z-10">
                <h2 class="text-[#111618] dark:text-white text-base md:text-xl font-bold tracking-tight">Grafik Pelanggan</h2>
                <p class="text-[#617c89] dark:text-[#a0b4be] text-xs sm:text-sm font-medium">Jumlah pelanggan harian (7 Hari Terakhir)</p>
            </div>

            <div class="line-chart-container relative z-10">
                <svg class="chart-svg" preserveAspectRatio="none" viewBox="0 0 1000 200">
                    <defs>
                        <linearGradient id="gradient" x1="0%" x2="0%" y1="0%" y2="100%">
                            <stop offset="0%" stop-color="#13a4ec" stop-opacity="0.6"></stop>
                            <stop offset="100%" stop-color="#13a4ec" stop-opacity="0"></stop>
                        </linearGradient>
                        
                        <filter id="glow" x="-20%" y="-20%" width="140%" height="140%">
                            <feDropShadow dx="0" dy="6" stdDeviation="6" flood-color="#13a4ec" flood-opacity="0.4"/>
                        </filter>
                    </defs>

                    <line x1="0" y1="50" x2="1000" y2="50" stroke="#e2e8f0" stroke-width="1" stroke-dasharray="8,8" class="dark:stroke-slate-700/50" />
                    <line x1="0" y1="100" x2="1000" y2="100" stroke="#e2e8f0" stroke-width="1" stroke-dasharray="8,8" class="dark:stroke-slate-700/50" />
                    <line x1="0" y1="150" x2="1000" y2="150" stroke="#e2e8f0" stroke-width="1" stroke-dasharray="8,8" class="dark:stroke-slate-700/50" />

                    <path class="chart-area" d="M 0,200 {{ $chartPoints }} 1000,200 Z" fill="url(#gradient)"></path>

                    <polyline class="chart-line" points="{{ $chartPoints }}" fill="none" stroke="#13a4ec" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" filter="url(#glow)" />

                    @foreach(explode(' ', $chartPoints) as $point)
                        @php $coord = explode(',', $point); @endphp
                        @if(count($coord) == 2)
                            <circle class="chart-point cursor-pointer dark:stroke-[#1a262e]" 
                                    cx="{{ $coord[0] }}" 
                                    cy="{{ $coord[1] }}" 
                                    fill="#13a4ec" 
                                    r="5" 
                                    stroke="#ffffff" 
                                    stroke-width="2.5">
                            </circle>
                        @endif
                    @endforeach
                </svg>
            </div>

            <div class="flex sm:grid sm:grid-cols-7 w-full mt-4 sm:mt-6 text-center relative z-10 overflow-x-auto gap-2 sm:gap-0 pb-2 sm:pb-0">
                @foreach($days as $index => $day)
                <div class="flex flex-col group flex-shrink-0 min-w-[3rem] sm:min-w-0">
                    <span class="text-[8px] sm:text-[10px] md:text-[11px] font-bold text-[#617c89] dark:text-[#a0b4be] uppercase tracking-wider mb-1 whitespace-nowrap">{{ $day }}</span>
                    <span class="text-xs sm:text-sm font-extrabold text-[#111618] dark:text-white">{{ $chartData[$index] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <livewire:active-orders />
    </div>

    {{-- STYLE DIPINDAH KE BAWAH AGAR TIDAK MENDORONG JUDUL KE BAWAH --}}
    <style>
        .line-chart-container {
            position: relative;
            height: 180px;
            width: 100%;
        }

        @media (min-width: 640px) {
            .line-chart-container {
                height: 250px;
            }
        }

        .chart-svg {
            width: 100%;
            height: 100%;
            overflow: visible; /* Penting agar shadow/glow tidak terpotong tepi */
        }

        /* Animasi garis berjalan (Drawing effect) */
        .chart-line {
            stroke-dasharray: 3000;
            stroke-dashoffset: 3000;
            animation: drawLine 2.5s cubic-bezier(0.25, 1, 0.5, 1) forwards;
        }

        /* Animasi area gradient memudar masuk */
        .chart-area {
            opacity: 0;
            animation: fadeIn 1.5s ease-out 0.5s forwards;
        }

        /* Interaksi pada titik data */
        .chart-point {
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            transform-origin: center;
        }

        .chart-point:hover {
            r: 8;
            stroke-width: 4;
            fill: #0ea5e9;
        }

        @keyframes drawLine {
            to {
                stroke-dashoffset: 0;
            }
        }

        @keyframes fadeIn {
            to {
                opacity: 1;
            }
        }
    </style>
</x-app-layout>