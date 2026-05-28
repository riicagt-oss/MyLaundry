<!DOCTYPE html>
<html class="light" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Login - Laundry System</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.material.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />

    <script>
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
                        "display": ["Inter", "sans-serif"]
                    }
                },
            },
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
    </style>
</head>

<body class="bg-background-light dark:bg-background-dark min-h-screen flex flex-col font-display">

    <main class="flex-1 flex flex-col items-center justify-center p-6 sm:p-12 relative overflow-hidden">
        <div class="absolute inset-0 z-0 opacity-10 pointer-events-none overflow-hidden">
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-primary rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-primary rounded-full blur-3xl"></div>
        </div>

        <div class="z-10 w-full max-w-[480px] bg-white dark:bg-[#1a262e] rounded-xl shadow-xl border border-gray-100 dark:border-gray-800 p-8 sm:p-10">
            <div class="mb-8">
                <h1 class="text-[#111618] dark:text-white tracking-tight text-[32px] font-bold leading-tight text-center pb-2">Login</h1>
                <p class="text-[#617c89] dark:text-gray-400 text-sm font-normal leading-normal text-center">Kelola pesanan, layanan, dan laporan Anda dengan efisien.</p>
            </div>

            @if (session('status'))
            <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400 text-center">
                {{ session('status') }}
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div class="flex flex-col gap-2">
                    <label class="text-[#111618] dark:text-gray-200 text-base font-medium leading-normal">Alamat Email</label>
                    <input name="email" value="{{ old('email') }}" required autofocus
                        class="w-full rounded-lg text-[#111618] dark:text-white focus:outline-0 focus:ring-2 focus:ring-primary/20 border border-[#dbe2e6] dark:border-gray-700 bg-white dark:bg-background-dark focus:border-primary h-14 placeholder:text-[#617c89] p-[15px] text-base font-normal leading-normal"
                        placeholder="admin@gmail.com" type="email" />
                    @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-col gap-2">
                    <label class="text-[#111618] dark:text-gray-200 text-base font-medium leading-normal">Kata Sandi</label>
                    <div class="flex w-full items-stretch rounded-lg group">
                        <input name="password" id="password" required
                            class="w-full rounded-l-lg text-[#111618] dark:text-white focus:outline-0 focus:ring-2 focus:ring-primary/20 border border-[#dbe2e6] dark:border-gray-700 bg-white dark:bg-background-dark focus:border-primary h-14 placeholder:text-[#617c89] p-[15px] border-r-0 pr-2 text-base font-normal leading-normal"
                            placeholder="Masukkan kata sandi Anda" type="password" />

                        <div id="togglePassword" class="text-[#617c89] flex border border-[#dbe2e6] dark:border-gray-700 bg-white dark:bg-background-dark items-center justify-center pr-[15px] rounded-r-lg border-l-0 cursor-pointer hover:text-primary transition-colors">
                            <span class="material-symbols-outlined" id="eyeIcon">visibility</span>
                        </div>
                    </div>
                    @error('password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between py-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input name="remember" id="remember_me" type="checkbox"
                            class="rounded text-primary focus:ring-primary border-gray-300 dark:border-gray-700 bg-white dark:bg-background-dark" />
                        <span class="text-sm text-[#617c89] dark:text-gray-400 font-normal">Ingat Saya</span>
                    </label>

                    @if (Route::has('password.request'))
                    <a class="text-sm text-primary font-semibold hover:underline" href="{{ route('password.request') }}">Lupa Kata Sandi?</a>
                    @endif
                </div>

                <button type="submit" class="w-full flex items-center justify-center rounded-lg h-14 bg-primary text-white text-base font-bold leading-normal tracking-[0.015em] hover:bg-primary/90 transition-all shadow-md active:scale-[0.98]">
                    Masuk
                </button>

                <div class="mt-6 text-center border-t border-gray-100 dark:border-gray-800 pt-6">
                    <p class="text-sm text-[#617c89] dark:text-gray-400">
                        Belum punya akun?
                        <a href="{{ route('register') }}" class="text-primary font-bold hover:underline italic">
                            Daftar Sekarang
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </main>
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        const eyeIcon = document.querySelector('#eyeIcon');

        togglePassword.addEventListener('click', function(e) {
            // Toggle tipe input
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);

            // Toggle ikon (visibility vs visibility_off)
            eyeIcon.textContent = type === 'password' ? 'visibility' : 'visibility_off';
        });
    </script>
</body>

</html>