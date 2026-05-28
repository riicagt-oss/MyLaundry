<x-app-layout>
    <div class="w-full mx-auto">
        @if(session('success'))
            <div id="success-notification" 
                 class="fixed top-5 right-5 z-[100] min-w-[300px] max-w-md transform transition-all duration-500 ease-in-out translate-x-full">
                <div class="bg-white dark:bg-slate-900 border-l-4 border-emerald-500 shadow-2xl rounded-xl p-4 flex items-center justify-between gap-4 border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center gap-3">
                        <div class="bg-emerald-100 dark:bg-emerald-500/20 p-2 rounded-full text-emerald-600 dark:text-emerald-400">
                            <span class="material-symbols-outlined text-xl">check_circle</span>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Berhasil</p>
                            <p class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ session('success') }}</p>
                        </div>
                    </div>
                    <button onclick="hideNotification()" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                </div>
            </div>
        @endif

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                {{-- Judul: normal-case (Bukan kapital semua) --}}
                <h2 class="text-lg md:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Kelola Satuan Laundry</h2>
                <p class="text-slate-500 mt-1 text-xs">Atur daftar harga dan kategori layanan laundry Anda.</p>
            </div>
            <div>
                {{-- Tombol: Ukuran lebih kecil (px-4 py-2, text-xs) --}}
                <a href="{{ route('laundry.create') }}" class="inline-flex items-center gap-1.5 bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg font-bold text-xs transition-all shadow-md shadow-primary/20">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Tambah Layanan Baru
                </a>
            </div>
        </div>

        <div class="mt-8 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="p-4 md:p-5 border-b border-slate-100 dark:border-slate-800 flex flex-col md:flex-row md:items-center gap-4">
                <div class="relative flex-1">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-[20px]">search</span>
                    <input type="text" id="searchInput" onkeyup="filterTable()" 
                           class="w-full bg-slate-50 dark:bg-slate-800/50 border-transparent focus:border-primary focus:bg-white dark:focus:bg-slate-900 rounded-xl py-2.5 pl-12 pr-4 text-sm focus:ring-4 focus:ring-primary/10 transition-all text-slate-900 dark:text-slate-100 placeholder:text-slate-400" 
                           placeholder="Cari layanan...">
                </div>
                <button class="p-2.5 bg-slate-50 dark:bg-slate-800/50 text-slate-600 dark:text-slate-400 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition-all border border-transparent hover:border-slate-200 dark:hover:border-slate-700">
                    <span class="material-symbols-outlined text-[22px]">filter_list</span>
                </button>
            </div>

            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left min-w-[600px]" id="laundryTable">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-slate-800/30">
                            <th class="px-4 sm:px-8 py-4 sm:py-5 text-[11px] font-bold text-slate-500 uppercase tracking-[0.1em]">Nama Layanan</th>
                            <th class="px-4 sm:px-8 py-4 sm:py-5 text-[11px] font-bold text-slate-500 uppercase tracking-[0.1em]">Satuan</th>
                            <th class="px-4 sm:px-8 py-4 sm:py-5 text-[11px] font-bold text-slate-500 uppercase tracking-[0.1em]">Harga</th>
                            <th class="px-4 sm:px-8 py-4 sm:py-5 text-[11px] font-bold text-slate-500 uppercase tracking-[0.1em]">Status</th>
                            <th class="px-4 sm:px-8 py-4 sm:py-5 text-[11px] font-bold text-slate-500 uppercase tracking-[0.1em] text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                        @forelse($services as $item)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors group">
                            <td class="px-4 sm:px-8 py-4 sm:py-6">
                                <div class="font-bold text-slate-900 dark:text-white group-hover:text-primary transition-colors text-xs sm:text-sm">{{ $item->name }}</div>
                            </td>
                            <td class="px-4 sm:px-8 py-4 sm:py-6">
                                <span class="{{ $item->unit == 'Pcs' ? 'bg-primary/10 text-primary border border-primary/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300' }} px-3 py-1 rounded-lg text-[10px] font-black uppercase">
                                    {{ $item->unit }}
                                </span>
                            </td>
                            <td class="px-4 sm:px-8 py-4 sm:py-6">
                                <span class="text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-200">Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                                <span class="text-[10px] text-slate-400 font-medium ml-0.5 sm:ml-1">/{{ strtolower($item->unit) }}</span>
                            </td>
                            <td class="px-4 sm:px-8 py-4 sm:py-6">
                                <form action="{{ route('laundry.updateStatus', $item->id) }}" method="POST">
                                    @csrf
                                    <label class="switch">
                                        <input type="checkbox" onchange="this.form.submit()" {{ $item->is_active ? 'checked' : '' }}>
                                        <span class="slider"></span>
                                    </label>
                                </form>
                            </td>
                            <td class="px-4 sm:px-8 py-4 sm:py-6 text-right">
                                {{-- PERBAIKAN: Menghapus opacity-0 dan group-hover:opacity-100 agar selalu tampil --}}
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('laundry.edit', $item->id) }}" class="p-2 hover:bg-primary/10 rounded-lg text-primary transition-colors">
                                        <span class="material-symbols-outlined text-[20px]">edit</span>
                                    </a>
                                    <form action="{{ route('laundry.destroy', $item->id) }}" method="POST" id="delete-form-{{ $item->id }}">
                                        @csrf @method('DELETE')
                                        <button type="button" onclick="confirmDelete('{{ $item->id }}', '{{ $item->name }}')" class="p-2 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg text-rose-500 transition-colors">
                                            <span class="material-symbols-outlined text-[20px]">delete</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 sm:px-8 py-8 sm:py-12 text-center text-slate-500 text-sm">Belum ada layanan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile Card Layout --}}
            <div class="block md:hidden divide-y divide-slate-100 dark:divide-slate-800/50" id="laundryCardList">
                @forelse($services as $item)
                <div class="p-4 hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors flex justify-between items-center laundry-card">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1.5">
                            <h3 class="font-bold text-slate-900 dark:text-white text-sm laundry-name">{{ $item->name }}</h3>
                            <span class="{{ $item->unit == 'Pcs' ? 'bg-primary/10 text-primary border border-primary/20' : 'bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300' }} px-2 py-0.5 rounded text-[9px] font-black uppercase">
                                {{ $item->unit }}
                            </span>
                        </div>
                        <div class="flex items-center gap-1 mb-3">
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-200">Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                            <span class="text-[10px] text-slate-400 font-medium">/{{ strtolower($item->unit) }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('laundry.edit', $item->id) }}" class="flex items-center gap-1 px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg text-xs font-bold transition-colors">
                                <span class="material-symbols-outlined text-[14px]">edit</span> Edit
                            </a>
                            <form action="{{ route('laundry.destroy', $item->id) }}" method="POST" id="delete-form-mobile-{{ $item->id }}">
                                @csrf @method('DELETE')
                                <button type="button" onclick="confirmDeleteMobile('{{ $item->id }}', '{{ $item->name }}')" class="flex items-center gap-1 px-3 py-1.5 bg-rose-50 text-rose-500 rounded-lg text-xs font-bold transition-colors">
                                    <span class="material-symbols-outlined text-[14px]">delete</span> Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                    <div>
                        <form action="{{ route('laundry.updateStatus', $item->id) }}" method="POST">
                            @csrf
                            <label class="switch">
                                <input type="checkbox" onchange="this.form.submit()" {{ $item->is_active ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </form>
                    </div>
                </div>
                @empty
                <div class="p-8 text-center text-slate-500 text-sm">Belum ada layanan.</div>
                @endforelse
            </div>
            <div class="px-4 sm:px-8 py-4 sm:py-6 bg-slate-50/50 dark:bg-slate-800/30 border-t border-slate-100 dark:border-slate-800">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                    Total: <span class="text-slate-900 dark:text-white">{{ $services->count() }}</span> Layanan
                </p>
            </div>
        </div>
    </div>

    <style>
        .switch { position: relative; display: inline-block; width: 44px; height: 22px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #e2e8f0; transition: .4s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        input:checked + .slider { background-color: #13a4ec; }
        input:checked + .slider:before { transform: translateX(22px); }
        .dark .slider { background-color: #334155; }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDelete(id, name) {
            Swal.fire({
                title: 'HAPUS LAYANAN?',
                text: `Yakin ingin menghapus "${name}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#13a4ec',
                cancelButtonColor: '#f43f5e',
                confirmButtonText: 'YA, HAPUS',
                cancelButtonText: 'BATAL',
                borderRadius: '1.5rem'
            }).then((result) => {
                if (result.isConfirmed) document.getElementById(`delete-form-${id}`).submit();
            });
        }

        function confirmDeleteMobile(id, name) {
            Swal.fire({
                title: 'HAPUS LAYANAN?',
                text: `Yakin ingin menghapus "${name}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#13a4ec',
                cancelButtonColor: '#f43f5e',
                confirmButtonText: 'YA, HAPUS',
                cancelButtonText: 'BATAL',
                borderRadius: '1.5rem'
            }).then((result) => {
                if (result.isConfirmed) document.getElementById(`delete-form-mobile-${id}`).submit();
            });
        }

        function filterTable() {
            let input = document.getElementById("searchInput").value.toUpperCase();
            
            // Filter Desktop Table
            let tr = document.getElementById("laundryTable").getElementsByTagName("tr");
            for (let i = 1; i < tr.length; i++) {
                let td = tr[i].getElementsByTagName("td")[0];
                if (td) {
                    let txtValue = td.textContent || td.innerText;
                    tr[i].style.display = txtValue.toUpperCase().indexOf(input) > -1 ? "" : "none";
                }
            }

            // Filter Mobile Cards
            let cards = document.getElementsByClassName("laundry-card");
            for (let i = 0; i < cards.length; i++) {
                let nameEl = cards[i].querySelector(".laundry-name");
                if (nameEl) {
                    let txtValue = nameEl.textContent || nameEl.innerText;
                    cards[i].style.display = txtValue.toUpperCase().indexOf(input) > -1 ? "" : "none";
                }
            }
        }

        const notification = document.getElementById('success-notification');
        function hideNotification() {
            if (notification) {
                notification.classList.add('translate-x-full');
                setTimeout(() => notification.remove(), 500);
            }
        }
        if (notification) {
            setTimeout(() => notification.classList.remove('translate-x-full'), 100);
            setTimeout(() => hideNotification(), 4000);
        }
    </script>
</x-app-layout>