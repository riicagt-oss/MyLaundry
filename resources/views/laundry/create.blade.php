<x-app-layout>
    <div class="w-full mx-auto p-4 md:p-0">
        {{-- Header --}}
        <div class="mb-6 flex items-center gap-4">
            <a href="{{ route('laundry') }}" class="group flex items-center justify-center size-9 rounded-lg bg-white dark:bg-[#1a262e] border border-slate-200 dark:border-gray-800 shadow-sm hover:border-primary transition-all">
                <span class="material-symbols-outlined text-slate-600 dark:text-slate-400 group-hover:text-primary text-xl transition-colors">
                    arrow_back
                </span>
            </a>
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">Tambah Layanan Baru</h1>
                <p class="text-[10px] text-slate-500 font-medium tracking-wider">Input data jasa laundry baru</p>
            </div>
        </div>

        <div class="grid grid-cols-1">
            <section class="bg-white dark:bg-[#1a262e] rounded-xl border border-slate-100 dark:border-gray-800 shadow-sm overflow-hidden">
                <div class="p-6 md:p-8">
                    <form action="{{ route('laundry.store') }}" method="POST" class="w-full space-y-5" autocomplete="off">
                        @csrf
                        
                        {{-- Nama Layanan --}}
                        <div class="space-y-1.5">
                            <label class="text-sm font-bold text-slate-700 dark:text-slate-200">Nama Layanan</label>
                            <input type="text" name="name" value="{{ old('name') }}" 
                                class="w-full h-11 px-4 rounded-xl border-slate-200 dark:border-gray-700 bg-white dark:bg-[#101c22] focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-sm dark:text-white placeholder:text-slate-400" 
                                placeholder="Contoh: Cuci Kering Setrika" required>
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Grid untuk Satuan dan Harga --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            {{-- Satuan (Tanpa Meter) --}}
                            <div class="space-y-1.5">
                                <label class="text-sm font-bold text-slate-700 dark:text-slate-200">Satuan</label>
                                <div class="relative">
                                    {{-- PERBAIKAN: Tambahkan bg-none dan pr-10 di sini --}}
                                    <select name="unit" required 
                                        class="w-full h-11 pl-4 pr-10 appearance-none bg-none rounded-xl border-slate-200 dark:border-gray-700 bg-white dark:bg-[#101c22] focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-sm dark:text-white cursor-pointer">
                                        <option value="KG">Kilogram (KG)</option>
                                        <option value="PCS">Satuan (PCS)</option>
                                    </select>
                                    {{-- Hanya 1 Panah ke Bawah (Ikon Custom) --}}
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-400">
                                        <span class="material-symbols-outlined text-lg">keyboard_arrow_down</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Harga --}}
                            <div class="space-y-1.5">
                                <label class="text-sm font-bold text-slate-700 dark:text-slate-200">Harga (Rp)</label>
                                <div class="relative">
                                    <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-sm">Rp</div>
                                    <input type="number" name="price" value="{{ old('price') }}"
                                        class="w-full h-11 pl-12 pr-4 rounded-xl border-slate-200 dark:border-gray-700 bg-white dark:bg-[#101c22] focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-sm dark:text-white font-bold placeholder:font-normal placeholder:text-slate-400" 
                                        placeholder="0" required>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="pt-4 flex items-center justify-end gap-3">
                            <a href="{{ route('laundry') }}" class="h-11 px-6 flex items-center justify-center text-sm font-bold text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-gray-700 rounded-xl hover:bg-slate-50 dark:hover:bg-gray-800 transition-all">
                                Batal
                            </a>
                            <button type="submit" class="h-11 px-8 bg-primary hover:bg-primary-dark text-white font-bold rounded-xl transition-all shadow-md shadow-primary/10 text-sm">
                                Simpan Layanan
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>