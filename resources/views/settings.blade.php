<x-app-layout>
    <div class="w-full mx-auto">
        <!-- Leaflet CSS & JS -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
        <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
        <style>
            #map { height: 300px; width: 100%; border-radius: 0.5rem; z-index: 10; }
        </style>
        
        @if(session('success'))
            <div id="success-notification" 
                 class="fixed top-5 right-5 z-[100] min-w-[300px] max-w-md transform transition-all duration-500 ease-in-out translate-x-full">
                <div class="bg-white dark:bg-[#1e293b] border-l-4 border-emerald-500 shadow-2xl rounded-xl p-4 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="bg-emerald-100 dark:bg-emerald-500/20 p-2 rounded-full text-emerald-600 dark:text-emerald-400">
                            <span class="material-symbols-outlined text-xl">check_circle</span>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Berhasil</p>
                            <p class="text-sm font-medium text-slate-700 dark:text-slate-200">{{ session('success') }}</p>
                        </div>
                    </div>
                    <button onclick="hideNotification()" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                        <span class="material-symbols-outlined text-sm">close</span>
                    </button>
                </div>
            </div>
        @endif

        {{-- Header Section --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h2 class="text-lg md:text-2xl font-bold tracking-tight text-slate-900 dark:text-white">Pengaturan Sistem</h2>
                <p class="text-slate-500 mt-1 text-xs">Kelola profil toko, lokasi peta, dan daftar akun staf Anda.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-5">
            {{-- PENGATURAN TOKO SECTION --}}
            <section class="bg-white dark:bg-[#1a262e] rounded-xl border border-slate-200 dark:border-gray-800 shadow-sm overflow-hidden">
                <div class="px-4 md:px-6 py-4 border-b border-slate-100 dark:border-gray-800 flex items-center gap-3">
                    <div class="size-8 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <span class="material-symbols-outlined text-xl">storefront</span>
                    </div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Pengaturan Toko</h2>
                </div>
                
                <form action="{{ route('shop.settings.update') }}" method="POST" class="p-4 md:p-6 space-y-6">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Kiri: Info Dasar & Lokasi --}}
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Nama Usaha</label>
                                <input type="text" name="shop_name" value="{{ old('shop_name', $shop->shop_name ?? '') }}" 
                                       class="w-full rounded-lg border-slate-200 dark:border-gray-700 bg-white dark:bg-[#1e293b] text-slate-900 dark:text-white focus:ring-primary focus:border-primary text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Lokasi Toko di Peta</label>
                                <div id="map" class="border border-slate-200 dark:border-gray-700 shadow-sm"></div>
                                <p class="text-xs text-slate-500 mt-2">Geser penanda di peta untuk mengatur titik lokasi toko Anda.</p>
                                
                                <div class="grid grid-cols-2 gap-4 mt-3">
                                    <div>
                                        <label class="block text-xs font-medium text-slate-500 mb-1">Latitude</label>
                                        <input type="text" id="latitude" name="latitude" value="{{ old('latitude', $shop->latitude ?? '') }}" readonly
                                               class="w-full rounded-md border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 text-slate-500 text-xs">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-500 mb-1">Longitude</label>
                                        <input type="text" id="longitude" name="longitude" value="{{ old('longitude', $shop->longitude ?? '') }}" readonly
                                               class="w-full rounded-md border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 text-slate-500 text-xs">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Kanan: Pengaturan Ongkir & Estimasi --}}
                        <div class="space-y-6">
                            <div class="bg-slate-50 dark:bg-gray-800/50 p-4 rounded-xl border border-slate-100 dark:border-gray-800">
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Pengaturan Ongkos Kirim</h3>
                                        <p class="text-xs text-slate-500">Aktifkan untuk menerapkan tarif berbayar.</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer mr-2">
                                        <input type="checkbox" name="is_delivery_active" id="is_delivery_active" value="1" {{ ($shop->is_delivery_active ?? false) ? 'checked' : '' }} class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary shadow-inner"></div>
                                    </label>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Tarif per KM (Rp)</label>
                                        <input type="number" name="delivery_fee_per_km" value="{{ old('delivery_fee_per_km', $shop->delivery_fee_per_km ?? 0) }}" 
                                               class="w-full rounded-md border-slate-200 dark:border-gray-700 bg-white dark:bg-[#1e293b] text-slate-900 dark:text-white focus:ring-primary focus:border-primary text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Gratis Ongkir (KM)</label>
                                        <input type="number" name="free_delivery_km" value="{{ old('free_delivery_km', $shop->free_delivery_km ?? 0) }}" 
                                               class="w-full rounded-md border-slate-200 dark:border-gray-700 bg-white dark:bg-[#1e293b] text-slate-900 dark:text-white focus:ring-primary focus:border-primary text-sm">
                                    </div>
                                </div>
                            </div>

                            <div class="bg-slate-50 dark:bg-gray-800/50 p-4 rounded-xl border border-slate-100 dark:border-gray-800">
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <h3 class="text-sm font-bold text-slate-900 dark:text-white">Estimasi Waktu & Layanan</h3>
                                        <p class="text-xs text-slate-500">Aktifkan layanan cuci Express dan Kilat.</p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer mr-2">
                                        <input type="checkbox" name="is_estimation_active" id="is_estimation_active" value="1" {{ ($shop->is_estimation_active ?? false) ? 'checked' : '' }} class="sr-only peer">
                                        <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary shadow-inner"></div>
                                    </label>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Ekstra Express (+Rp)</label>
                                        <input type="number" name="express_extra_price" value="{{ old('express_extra_price', $shop->express_extra_price ?? 0) }}" 
                                               class="w-full rounded-md border-slate-200 dark:border-gray-700 bg-white dark:bg-[#1e293b] text-slate-900 dark:text-white focus:ring-primary focus:border-primary text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Ekstra Kilat (+Rp)</label>
                                        <input type="number" name="kilat_extra_price" value="{{ old('kilat_extra_price', $shop->kilat_extra_price ?? 0) }}" 
                                               class="w-full rounded-md border-slate-200 dark:border-gray-700 bg-white dark:bg-[#1e293b] text-slate-900 dark:text-white focus:ring-primary focus:border-primary text-sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-slate-100 dark:border-gray-800">
                        <button type="submit" class="h-10 px-6 bg-primary hover:bg-primary/90 text-white font-bold rounded-lg transition-all shadow-sm text-sm">
                            Simpan Pengaturan Toko
                        </button>
                    </div>
                </form>
            </section>

            <section class="bg-white dark:bg-[#1a262e] rounded-xl border border-slate-200 dark:border-gray-800 shadow-sm overflow-hidden">
                <div class="px-4 md:px-6 py-4 border-b border-slate-100 dark:border-gray-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="size-8 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                            <span class="material-symbols-outlined text-xl">badge</span>
                        </div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">Manajemen Akun Pengguna</h2>
                    </div>
                    <a href="{{ route('staff.create') }}" class="h-8 px-3 bg-primary hover:bg-primary/90 text-white font-bold rounded-lg transition-all flex items-center gap-1.5 text-xs shadow-sm shadow-primary/20">
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        Tambah Akun
                    </a>
                </div>

                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[500px]">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-gray-800/50">
                                <th class="px-4 sm:px-6 py-3 sm:py-4 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Nama</th>
                                <th class="px-4 sm:px-6 py-3 sm:py-4 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Peran</th>
                                <th class="px-4 sm:px-6 py-3 sm:py-4 text-[11px] font-bold text-slate-500 uppercase tracking-wider">Email</th>
                                <th class="px-4 sm:px-6 py-3 sm:py-4 text-[11px] font-bold text-slate-500 uppercase tracking-wider text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-gray-800">
                            @forelse($staffs as $staff)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="px-4 sm:px-6 py-3 sm:py-4">
                                    <p class="text-xs sm:text-sm font-semibold text-slate-900 dark:text-white">{{ $staff->name }}</p>
                                </td>
                                <td class="px-4 sm:px-6 py-3 sm:py-4">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 uppercase">
                                        {{ $staff->role }}
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-3 sm:py-4 text-xs sm:text-sm text-slate-600 dark:text-slate-400">{{ $staff->email }}</td>
                                <td class="px-4 sm:px-6 py-3 sm:py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('staff.edit', $staff->id) }}" class="p-2 text-slate-400 hover:text-blue-500 transition-colors">
                                            <span class="material-symbols-outlined text-[20px]">edit</span>
                                        </a>
                                        <form action="{{ route('staff.destroy', $staff->id) }}" method="POST" id="delete-form-{{ $staff->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="confirmDelete('{{ $staff->id }}', '{{ $staff->name }}')" class="p-2 text-slate-400 hover:text-red-500 transition-colors">
                                                <span class="material-symbols-outlined text-[20px]">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 sm:px-6 py-8 sm:py-12 text-center">
                                    <div class="flex flex-col items-center justify-center text-slate-400">
                                        <span class="material-symbols-outlined text-5xl mb-3">group_off</span>
                                        <p class="text-base font-medium">Belum ada akun pengguna yang terdaftar.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Mobile Card Layout --}}
                <div class="block md:hidden divide-y divide-slate-100 dark:divide-gray-800">
                    @forelse($staffs as $staff)
                    <div class="p-4 hover:bg-slate-50/50 dark:hover:bg-gray-800/30 transition-colors">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $staff->name }}</p>
                                <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">{{ $staff->email }}</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 uppercase">
                                {{ $staff->role }}
                            </span>
                        </div>
                        <div class="flex items-center justify-end gap-2 mt-3 pt-3 border-t border-slate-100 dark:border-gray-800">
                            <a href="{{ route('staff.edit', $staff->id) }}" class="flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded-lg text-xs font-bold transition-colors">
                                <span class="material-symbols-outlined text-[16px]">edit</span> Edit
                            </a>
                            <form action="{{ route('staff.destroy', $staff->id) }}" method="POST" id="delete-form-mobile-{{ $staff->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="confirmDeleteMobile('{{ $staff->id }}', '{{ $staff->name }}')" class="flex items-center gap-1.5 px-3 py-1.5 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-lg text-xs font-bold transition-colors">
                                    <span class="material-symbols-outlined text-[16px]">delete</span> Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="p-8 text-center text-slate-400">
                        <span class="material-symbols-outlined text-4xl mb-2">group_off</span>
                        <p class="text-sm font-medium">Belum ada akun pengguna yang terdaftar.</p>
                    </div>
                    @endforelse
                </div>
            </section>

            <section class="bg-white dark:bg-[#1a262e] rounded-xl border border-red-100 dark:border-red-900/30 shadow-sm overflow-hidden mb-6">
                <div class="p-4 md:p-6 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="size-12 rounded-full bg-red-50 dark:bg-red-900/20 flex items-center justify-center">
                            <span class="material-symbols-outlined text-2xl text-red-600">logout</span>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">Keluar Akun</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Selesaikan sesi aktif Anda di perangkat ini.</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="w-full md:w-auto">
                        @csrf
                        <button type="submit" class="w-full md:w-auto h-11 px-8 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg transition-all shadow-sm text-xs uppercase tracking-widest">
                            Keluar Sekarang
                        </button>
                    </form>
                </div>
            </section>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDelete(id, name) {
            Swal.fire({
                title: 'Hapus Akun?',
                text: `Apakah Anda yakin ingin menghapus "${name}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                borderRadius: '12px'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(`delete-form-${id}`).submit();
                }
            })
        }

        function confirmDeleteMobile(id, name) {
            Swal.fire({
                title: 'Hapus Akun?',
                text: `Apakah Anda yakin ingin menghapus "${name}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                borderRadius: '12px'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(`delete-form-mobile-${id}`).submit();
                }
            })
        }

        // Logic Notifikasi Mengapung
        const notification = document.getElementById('success-notification');
        
        function hideNotification() {
            if (notification) {
                notification.classList.add('translate-x-full');
                setTimeout(() => notification.remove(), 500);
            }
        }

        if (notification) {
            // Munculkan (Slide In)
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);

            // Sembunyikan otomatis setelah 4 detik
            setTimeout(() => {
                hideNotification();
            }, 4000);
        }

        // --- LEAFLET MAP LOGIC ---
        document.addEventListener("DOMContentLoaded", function () {
            let latInput = document.getElementById("latitude");
            let lngInput = document.getElementById("longitude");
            
            // Default center: Indonesia (or shop location if exists)
            let defaultLat = parseFloat(latInput.value) || -6.200000;
            let defaultLng = parseFloat(lngInput.value) || 106.816666;
            let zoomLevel = latInput.value ? 15 : 5; // Zoom in if location exists

            let map = L.map('map').setView([defaultLat, defaultLng], zoomLevel);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            let marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

            // Coba ambil lokasi saat ini (GPS Web) jika belum ada lokasi toko yang tersimpan
            if (!latInput.value && navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    let currentLat = position.coords.latitude;
                    let currentLng = position.coords.longitude;
                    
                    map.setView([currentLat, currentLng], 15); // Auto-zoom ke lokasi user
                    marker.setLatLng([currentLat, currentLng]).addTo(map);
                    
                    // Opsional: otomatis isi input (bisa dimatikan jika tidak ingin auto-save)
                    // updateInputs(currentLat, currentLng);
                }, function (error) {
                    console.log("Geolocation error: ", error.message);
                });
            }

            // Tambahkan fitur Pencarian Alamat (Geocoder)
            L.Control.geocoder({
                defaultMarkGeocode: false,
                placeholder: "Cari nama jalan/kota...",
            })
            .on('markgeocode', function(e) {
                let latlng = e.geocode.center;
                map.setView(latlng, 16);
                if (!map.hasLayer(marker)) {
                    marker.addTo(map);
                }
                marker.setLatLng(latlng);
                updateInputs(latlng.lat, latlng.lng);
            })
            .addTo(map);

            // Jika belum ada lokasi tersimpan, sembunyikan marker sampai user klik
            if (!latInput.value) {
                map.removeLayer(marker);
            }

            function updateInputs(lat, lng) {
                latInput.value = lat.toFixed(8);
                lngInput.value = lng.toFixed(8);
            }

            // Click pada peta untuk memindahkan/menambahkan marker
            map.on('click', function (e) {
                let lat = e.latlng.lat;
                let lng = e.latlng.lng;
                
                if (!map.hasLayer(marker)) {
                    marker.addTo(map);
                }
                marker.setLatLng([lat, lng]);
                updateInputs(lat, lng);
            });

            // Drag marker
            marker.on('dragend', function (e) {
                let position = marker.getLatLng();
                updateInputs(position.lat, position.lng);
            });
        });
    </script>
</x-app-layout>