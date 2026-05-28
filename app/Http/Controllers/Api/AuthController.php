<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Email atau password salah.'], 401);
        }

        // --- PROTEKSI API ---
        if (!in_array($user->role, ['staf', 'driver'])) {
            return response()->json([
                'message' => 'Akses ditolak. Aplikasi ini hanya untuk staf dan driver.'
            ], 403); // 403 Forbidden
        }

        // --- PERBAIKAN 1: Hapus token lama sebelum membuat yang baru ---
        // Ini akan mencegah tabel personal_access_tokens menjadi penuh/numpuk
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
            'shop' => $user->getOwnerShop(),
        ]);
    }

    // --- PERBAIKAN 2: Tambahkan fungsi Logout ---
    public function logout(Request $request)
    {
        // Menghapus token yang sedang dipakai oleh staf saat ini
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil logout dan token telah dihapus.'
        ]);
    }
}