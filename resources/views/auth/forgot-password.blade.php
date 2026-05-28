<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Lupa Kata Sandi - Laundry System</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    
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
                    fontFamily: { "display": ["Inter", "sans-serif"] }
                },
            },
        }
    </script>
</head>
<body class="bg-background-light dark:bg-background-dark min-h-screen flex flex-col font-display">

<main class="flex-1 flex flex-col items-center justify-center p-6 sm:p-12 relative overflow-hidden">
    <div class="absolute inset-0 z-0 opacity-10 pointer-events-none overflow-hidden">
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-primary rounded-full blur-3xl"></div>
    </div>

    <div class="z-10 w-full max-w-[480px] bg-white dark:bg-[#1a262e] rounded-xl shadow-xl border border-gray-100 dark:border-gray-800 p-8 sm:p-10">
        <div class="mb-6 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-50 dark:bg-blue-900/20 rounded-full mb-4">
                <span class="text-3xl text-primary">🔑</span>
            </div>
            <h1 class="text-[#111618] dark:text-white tracking-tight text-2xl font-bold leading-tight pb-2">Lupa Kata Sandi?</h1>
            <p class="text-[#617c89] dark:text-gray-400 text-sm font-normal">
                Masukkan email Anda dan kami akan mengirimkan tautan untuk mengatur ulang kata sandi Anda.
            </p>
        </div>

        @if (session('status'))
            <div class="mb-6 p-4 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-600 dark:text-green-400 text-sm font-medium text-center">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5" autocomplete="off">
            @csrf

            <div class="flex flex-col gap-2">
                <label class="text-[#111618] dark:text-gray-200 text-base font-medium leading-normal">Alamat Email</label>
                <input name="email" value="{{ old('email') }}" required autofocus 
                    class="w-full rounded-lg text-[#111618] dark:text-white focus:outline-0 focus:ring-2 focus:ring-primary/20 border border-[#dbe2e6] dark:border-gray-700 bg-white dark:bg-background-dark focus:border-primary h-14 placeholder:text-[#617c89] p-[15px] text-base font-normal leading-normal" 
                    placeholder="Masukkan email terdaftar" type="email"/>
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full flex items-center justify-center rounded-lg h-14 bg-primary text-white text-base font-bold hover:bg-primary/90 transition-all shadow-md active:scale-[0.98]">
                Kirim Tautan Atur Ulang
            </button>

            <div class="mt-6 text-center">
                <a href="{{ route('login') }}" class="text-sm text-[#617c89] hover:text-primary transition-colors flex items-center justify-center gap-2">
                    <span>←</span> Kembali ke Halaman Login
                </a>
            </div>
        </form>
    </div>
</main>

</body>
</html>