<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service; 

class LaundryController extends Controller
{
    /**
     * Menampilkan daftar layanan (Halaman Utama)
     */
    public function index()
    {
        $services = Service::all();
        return view('laundry', compact('services'));
    }

    /**
     * Menampilkan halaman form tambah layanan baru
     */
    public function create()
    {
        return view('laundry.create');
    }

    /**
     * Menyimpan layanan baru ke database
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'price' => 'required|numeric',
            'unit'  => 'required|string',
        ]);

        Service::create([
            'name'      => $request->name,
            'price'     => $request->price,
            'unit'      => $request->unit,
            'is_active' => true,
        ]);

        return redirect()->route('laundry')->with('success', 'Layanan berhasil ditambahkan!');
    }

    /**
     * --- FUNGSI EDIT (YANG TADI HILANG) ---
     * Menampilkan halaman form edit layanan
     */
    public function edit($id)
    {
        $service = Service::findOrFail($id);
        return view('laundry.edit', compact('service'));
    }

    /**
     * --- FUNGSI UPDATE (UNTUK MENYIMPAN PERUBAHAN) ---
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'price' => 'required|numeric',
            'unit'  => 'required|string',
        ]);

        $service = Service::findOrFail($id);
        $service->update([
            'name'  => $request->name,
            'price' => $request->price,
            'unit'  => $request->unit,
        ]);

        return redirect()->route('laundry')->with('success', 'Layanan berhasil diperbarui!');
    }

    /**
     * Mengubah status aktif/non-aktif (Toggle)
     */
    public function updateStatus($id)
    {
        $service = Service::findOrFail($id);
        $service->is_active = !$service->is_active;
        $service->save();

        return back()->with('success', 'Status layanan berhasil diperbarui!');
    }

    /**
     * Menghapus layanan
     */
    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();

        return back()->with('success', 'Layanan berhasil dihapus!');
    }
}