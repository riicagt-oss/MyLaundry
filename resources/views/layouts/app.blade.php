<!DOCTYPE html>
<html class="light" lang="id" x-data="{ sidebarOpen: false }">

<head>
    <meta charset="utf-8" />
    <meta content="width=1280" name="viewport" id="viewport-meta" />
    <title>My Laundry - Management System</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/app_icon.png?v=2" />
    <link rel="shortcut icon" href="/favicon.ico?v=2" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    @livewireStyles
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#13a4ec",
                        "background-light": "#f6f7f8",
                        "background-dark": "#101c22",
                    },
                    fontFamily: {
                        "display": ["Inter"]
                    },
                },
            },
        }
    </script>
    <style type="text/css">
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        body {
            font-family: 'Inter', sans-serif;
        }

        .page-title {
            color: #111618;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.025em;
        }

        .dark .page-title {
            color: white;
        }

        [x-cloak] {
            display: none !important;
        }

        /* Tampilkan overlay hanya di perangkat mobile (max-device-width: 1024px) DAN saat posisi Berdiri (Portrait) */
        @media screen and (max-device-width: 1024px) and (orientation: portrait) {
            #rotate-device-overlay {
                display: flex !important;
            }
            .main-content-wrapper {
                display: none !important;
            }
        }
    </style>

</head>

<body class="bg-background-light dark:bg-background-dark font-display text-[#111618] dark:text-white">
    
    {{-- OVERLAY PUTAR LAYAR --}}
    <div id="rotate-device-overlay" class="fixed inset-0 z-[9999] bg-slate-900 hidden flex-col items-center justify-center text-center p-8 text-white">
        <span class="material-symbols-outlined text-7xl text-primary mb-6 animate-pulse" style="transform: rotate(-90deg);">screen_rotation</span>
        <h2 class="text-2xl font-bold mb-3 tracking-tight">Putar Layar HP Anda</h2>
        <p class="text-slate-400 text-sm font-medium leading-relaxed max-w-xs">
            Aplikasi Dashboard ini dirancang untuk layar lebar. Mohon aktifkan Rotasi Layar dan posisikan HP Anda secara <strong class="text-white">Horizontal (Lanskap)</strong> untuk pengalaman terbaik.
        </p>
    </div>

    <div class="flex h-screen overflow-hidden main-content-wrapper">

        <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-20 bg-black/50 lg:hidden" x-cloak></div>

        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-30 w-64 -translate-x-full bg-white dark:bg-[#1a262e] border-r border-[#dbe2e6] dark:border-[#2a3a44] transition-transform duration-300 lg:static lg:translate-x-0 flex flex-col flex-shrink-0">
            <div class="p-6 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <img src="/app_icon.png" class="w-8 h-8 rounded-lg" alt="Logo" />
                    <div>
                        <h1 class="text-[#111618] dark:text-white text-lg font-bold leading-tight uppercase italic">My Laundry</h1>
                        <p class="text-[#617c89] dark:text-[#a0b4be] text-xs font-medium">Manajemen Sistem</p>
                    </div>
                </div>
                <button @click="sidebarOpen = false" class="lg:hidden">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <nav class="flex-1 flex flex-col gap-2 px-3 overflow-y-auto">
                <a class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all duration-200 {{ request()->routeIs('dashboard') || request()->routeIs('orders.*') || request()->is('orders*') ? 'bg-primary/10 text-primary font-bold' : 'text-[#617c89] dark:text-[#a0b4be] hover:bg-gray-100 dark:hover:bg-[#2a3a44] hover:text-[#111618] dark:hover:text-white' }}"
                    href="{{ route('dashboard') }}">
                    <span class="material-symbols-outlined {{ request()->routeIs('dashboard') || request()->routeIs('orders.*') || request()->is('orders*') ? 'fill-[1]' : '' }}">home</span>
                    <span class="text-sm font-semibold">Beranda</span>
                </a>

                <a class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all duration-200 
    {{ (request()->routeIs('laundry.*') || request()->is('laundry*')) ? 'bg-primary/10 text-primary font-bold' : 'text-[#617c89] dark:text-[#a0b4be] hover:bg-gray-100 dark:hover:bg-[#2a3a44] hover:text-[#111618] dark:hover:text-white' }}"
                    href="{{ route('laundry') }}">

                    <span class="material-symbols-outlined {{ (request()->routeIs('laundry.*') || request()->is('laundry*')) ? 'fill-[1]' : '' }}">
                        local_laundry_service
                    </span>
                    <span class="text-sm font-semibold">Laundry</span>
                </a>

                <a class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all duration-200 
    {{ request()->routeIs('reports.*') ? 'bg-primary/10 text-primary font-bold' : 'text-[#617c89] dark:text-[#a0b4be] hover:bg-gray-100 dark:hover:bg-[#2a3a44]' }}"
                    href="{{ route('reports.index') }}"> {{-- Sesuaikan dengan nama rute di web.php --}}
                    <span class="material-symbols-outlined {{ request()->routeIs('reports.*') ? 'fill-[1]' : '' }}">description</span>
                    <p class="text-sm font-semibold">Laporan</p>
                </a>

                <a class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition-all duration-200 
    {{ request()->routeIs('settings') || request()->is('staff*') ? 'bg-primary/10 text-primary font-bold' : 'text-[#617c89] dark:text-[#a0b4be] hover:bg-gray-100 dark:hover:bg-[#2a3a44] hover:text-[#111618] dark:hover:text-white' }}"
                    href="{{ route('settings') }}">

                    <span class="material-symbols-outlined {{ request()->routeIs('settings') || request()->is('staff*') ? 'fill-[1]' : '' }}">
                        settings
                    </span>
                    <span class="text-sm font-semibold">Pengaturan</span>
                </a>
            </nav>
            <div class="p-4 border-t border-[#dbe2e6] dark:border-[#2a3a44] mt-auto">
                <div class="flex items-center gap-3 px-2 py-2">
                    <div class="relative flex-shrink-0">
                        <div class="size-11 rounded-full bg-slate-200 dark:bg-gray-700 flex items-center justify-center overflow-hidden border-2 border-white dark:border-[#1a262e] shadow-sm">
                            <span class="material-symbols-outlined text-slate-500 dark:text-slate-400 text-3xl translate-y-1">
                                person
                            </span>
                        </div>
                        <div class="absolute bottom-0 right-0 size-3 bg-emerald-500 border-2 border-white dark:border-[#1a262e] rounded-full"></div>
                    </div>

                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-slate-900 dark:text-white truncate">
                            {{ Auth::user()->name }}
                        </p>
                        <p class="text-[11px] font-medium text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                            <span class="size-1.5 bg-emerald-500 rounded-full"></span>
                            {{ ucfirst(Auth::user()->role) }}
                        </p>
                    </div>
                </div>
            </div>
        </aside>

        <main class="flex-1 flex flex-col overflow-y-auto min-w-0">
            <header class="flex items-center justify-between p-4 bg-white dark:bg-[#1a262e] border-b lg:hidden">
                <button @click="sidebarOpen = true">
                    <span class="material-symbols-outlined text-[#111618] dark:text-white">menu</span>
                </button>
                <div class="flex items-center gap-2">
                    <img src="/app_icon.png" class="w-6 h-6 rounded-md" alt="Logo" />
                    <span class="font-bold italic text-primary">My LAUNDRY</span>
                </div>

                <div class="w-8"></div>
            </header>

            <div class="p-3 md:p-8 space-y-4 md:space-y-8">
                {{ $slot }}
            </div>
        </main>
    </div>
    <script src="//instant.page/5.2.0" type="module" integrity="sha384-jnZyxPjiipS96atlyCDuWbbxSU7shM7E38jE8V31TJuBdrLqLdAVf0z3vE171ZNe"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @livewireScripts
</body>

</html>