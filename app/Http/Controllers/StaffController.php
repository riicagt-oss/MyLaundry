<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class StaffController extends Controller
{
    public function create()
    {
        return view('staff.create');
    }

    public function store(Request $request)
    {
        // 1. Validasi data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|min:8',
            'role' => 'required|in:staf,driver',
        ]);

        // 2. Simpan ke database
        \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'role' => $request->role,
            // TAMBAHKAN BARIS DI BAWAH INI:
            'owner_id' => Auth::id(),
        ]);

        // 3. Arahkan kembali
        return redirect()->route('settings')->with('success', 'Akun ' . $request->role . ' berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $staff = \App\Models\User::where('owner_id', Auth::id())->findOrFail($id);
        return view('staff.edit', compact('staff'));
    }

    public function update(Request $request, $id)
    {
        $staff = \App\Models\User::where('owner_id', Auth::id())->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $staff->id,
            'password' => 'nullable|min:8',
            'role' => 'required|in:staf,driver',
        ]);

        $staff->name = $request->name;
        $staff->email = $request->email;
        $staff->role = $request->role;

        if ($request->filled('password')) {
            $staff->password = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        $staff->save();

        return redirect()->route('settings')->with('success', 'Akun berhasil diperbarui!');
    }

    public function destroy($id)
    {
        // Tambahkan filter owner_id agar Owner A tidak bisa menghapus staf Owner B lewat URL
        $staff = \App\Models\User::where('owner_id', Auth::id())->findOrFail($id);

        if ($staff->id === Auth::id()) {
            return redirect()->back()->with('error', 'Anda tidak bisa menghapus akun sendiri!');
        }

        $staff->delete();

        return redirect()->route('settings')->with('success', 'Akun berhasil dihapus.');
    }
}
