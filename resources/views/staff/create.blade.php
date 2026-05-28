<x-app-layout>
    <div class="w-full mx-auto p-4 md:p-0">
        <div class="mb-6 flex items-center gap-4">
            <a href="{{ route('settings') }}" class="group flex items-center justify-center size-9 rounded-lg bg-white dark:bg-[#1a262e] border border-slate-200 dark:border-gray-800 shadow-sm hover:border-primary transition-all">
                <span class="material-symbols-outlined text-slate-600 dark:text-slate-400 group-hover:text-primary text-xl transition-colors">
                    arrow_back
                </span>
            </a>
            <div>
                <h1 class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">Tambah Pengguna Baru</h1>
                <p class="text-[10px] text-slate-500 font-medium uppercase tracking-wider">Manajemen Akses Aplikasi</p>
            </div>
        </div>

        <div class="grid grid-cols-1">
            <section class="bg-white dark:bg-[#1a262e] rounded-xl border border-slate-100 dark:border-gray-800 shadow-sm overflow-hidden">
                <div class="p-6 md:p-8">
                    <form action="{{ route('staff.store') }}" method="POST" class="w-full space-y-5" autocomplete="off">
                        @csrf
                        
                        <div class="space-y-1.5">
                            <label class="text-sm font-bold text-slate-700 dark:text-slate-200">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ old('name') }}" 
                                class="w-full h-11 px-4 rounded-xl border-slate-200 dark:border-gray-700 bg-white dark:bg-[#101c22] focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-sm dark:text-white placeholder:text-slate-400" 
                                placeholder="Masukkan nama lengkap pengguna" required>
                            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-sm font-bold text-slate-700 dark:text-slate-200">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}"
                                class="w-full h-11 px-4 rounded-xl border-slate-200 dark:border-gray-700 bg-white dark:bg-[#101c22] focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-sm dark:text-white placeholder:text-slate-400" 
                                placeholder="staf@gmail.com" required>
                            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-sm font-bold text-slate-700 dark:text-slate-200">Kata Sandi</label>
                            <input type="password" name="password"
                                class="w-full h-11 px-4 rounded-xl border-slate-200 dark:border-gray-700 bg-white dark:bg-[#101c22] focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-sm dark:text-white placeholder:text-slate-400" 
                                placeholder="........" required>
                            @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-sm font-bold text-slate-700 dark:text-slate-200">Peran / Role</label>
                            <select name="role" required class="w-full h-11 px-4 rounded-xl border-slate-200 dark:border-gray-700 bg-white dark:bg-[#101c22] focus:ring-4 focus:ring-primary/10 focus:border-primary transition-all text-sm dark:text-white">
                                <option value="staf" {{ old('role') == 'staf' ? 'selected' : '' }}>Staf</option>
                                <option value="driver" {{ old('role') == 'driver' ? 'selected' : '' }}>Driver</option>
                            </select>
                            @error('role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="pt-4 flex items-center gap-3">
                            <a href="{{ route('settings') }}" class="h-11 px-8 flex items-center justify-center text-sm font-bold text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-gray-700 rounded-xl hover:bg-slate-50 dark:hover:bg-gray-800 transition-all">
                                Batal
                            </a>
                            <button type="submit" class="h-11 px-8 bg-[#14b0f0] hover:bg-[#0e98d1] text-white font-bold rounded-xl transition-all shadow-md shadow-blue-500/10 text-sm">
                                Simpan Pengguna
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>