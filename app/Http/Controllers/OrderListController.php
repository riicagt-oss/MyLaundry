<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderListController extends Controller
{
    public function showByStatus(Request $request, $status) // Tambahkan Request $request
    {
        // 1. Validasi & Normalisasi Status
        $statusUpper = strtoupper($status);
        $validStatuses = ['ANTRIAN', 'PROSES', 'SELESAI'];

        if (!in_array($statusUpper, $validStatuses)) {
            abort(404);
        }

        // 2. Mulai Query data dari tabel 'orders' berdasarkan status
        $query = Order::with('items')->where('status', $statusUpper);

        // 3. Logika Pencarian (Jika ada input 'search')
        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('customer_name', 'like', '%' . $searchTerm . '%')
                  ->orWhere('order_number', 'like', '%' . $searchTerm . '%');
            });
        }

        // 4. Ambil data dengan Pagination & urutan terbaru
        // appends() digunakan agar saat pindah halaman, hasil search tidak hilang
        $orders = $query->latest()
                        ->paginate(10)
                        ->appends(['search' => $request->search]);

        // 5. Kirim ke view
        return view('orders.status_list', compact('orders', 'statusUpper'));
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->selected_ids;

        if (!$ids || count($ids) === 0) {
            return redirect()->back()->with('error', 'Pilih minimal satu pesanan untuk dihapus.');
        }

        try {
            Order::whereIn('id', $ids)->delete();
            return redirect()->back()->with('success', count($ids) . ' pesanan berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data.');
        }
    }
}