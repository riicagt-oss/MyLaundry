<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // TAMBAHKAN INI
use Symfony\Component\HttpFoundation\Response;

class OnlyOwner
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Gunakan Auth:: daripada fungsi global auth()
        if (Auth::check() && Auth::user()->role !== 'owner') {
            Auth::logout();

            // Hancurkan session agar benar-benar bersih
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/login')->with('error', 'Akses Ditolak! Hanya Owner yang boleh masuk.');
        }

        return $next($request);
    }
}