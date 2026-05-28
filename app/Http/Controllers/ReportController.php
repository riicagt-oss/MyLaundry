<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order; // Sesuaikan dengan model yang Anda gunakan di API
use Carbon\Carbon;
use App\Exports\ReportExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // Default tanggal: awal bulan ini sampai hari ini
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->format('Y-m-d'));
        $deliveryType = $request->get('delivery_type', 'all');
        $paymentMethod = $request->get('payment_method', 'all');

        // Filter data yang statusnya 'DIAMBIL' sesuai alur aplikasi mobile
        $query = Order::with('items')->whereBetween('updated_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where('status', 'DIAMBIL');

        if ($deliveryType !== 'all') {
            if ($deliveryType === 'none') {
                $query->where(function($q) {
                    $q->where('delivery_type', 'none')->orWhereNull('delivery_type');
                });
            } else {
                $query->where('delivery_type', $deliveryType);
            }
        }

        if ($paymentMethod !== 'all') {
            $query->where('payment_method', $paymentMethod);
        }

        $reports = $query->orderBy('updated_at', 'desc')->get();
        $totalRevenue = $query->sum('total_price');
        $totalDeliveryFee = $query->sum('delivery_fee');
        $totalLaundryRevenue = $totalRevenue - $totalDeliveryFee;
        $totalOrders = $query->count();

        return view('reports', compact('reports', 'totalRevenue', 'totalDeliveryFee', 'totalLaundryRevenue', 'totalOrders', 'startDate', 'endDate', 'deliveryType', 'paymentMethod'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required',
            'phone'         => 'required',
            'service_name'  => 'required',
            'total_price'   => 'required|integer',
            'payment_method' => 'nullable|string', // Pastikan App mengirim ini
            'cash_received' => 'nullable|integer', // Pastikan App mengirim ini
        ]);

        // Hitung kembalian
        $total = $validated['total_price'];
        $bayar = $request->cash_received ?? 0;
        $kembali = ($bayar > $total) ? ($bayar - $total) : 0;

        $ownerId = auth()->check() ? (auth()->user()->getOwnerId() ?? auth()->id()) : null;
        $orderCount = $ownerId ? Order::where('user_id', $ownerId)->count() : Order::count();
        $prefix = $ownerId ? 'ORD-' . date('Ymd') . '-' . $ownerId . '-' : 'ORD-' . date('Ymd') . '-';

        $order = Order::create([
            'order_number'  => $prefix . str_pad($orderCount + 1, 3, '0', STR_PAD_LEFT),
            'user_id'       => $ownerId,
            'customer_name' => $validated['customer_name'],
            'phone'         => $validated['phone'],
            'service_name'  => $validated['service_name'],
            'total_price'   => $total,
            'status'        => 'ANTRIAN',
            'payment_method' => strtoupper($request->payment_method ?? 'CASH'),
            'cash_received' => $bayar,
            'cash_change'   => $kembali,
        ]);


        return response()->json(['status' => 'success', 'data' => $order]);
    }

    public function export(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $deliveryType = $request->get('delivery_type', 'all');
        $paymentMethod = $request->get('payment_method', 'all');

        $paymentLabel = $paymentMethod !== 'all' ? '-' . strtoupper($paymentMethod) : '';
        if ($paymentLabel === '-CASH') $paymentLabel = '-TUNAI';

        return Excel::download(new ReportExport($startDate, $endDate, $deliveryType, $paymentMethod), "laporan-laundry{$paymentLabel}-{$startDate}-to-{$endDate}.xlsx");
    }

    public function exportBulk(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:orders,id'
        ]);

        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->format('Y-m-d'));
        $deliveryType = $request->get('delivery_type', 'all');
        $paymentMethod = $request->get('payment_method', 'all');

        $paymentLabel = $paymentMethod !== 'all' ? '-' . strtoupper($paymentMethod) : '';
        if ($paymentLabel === '-CASH') $paymentLabel = '-TUNAI';

        return Excel::download(new ReportExport($startDate, $endDate, $deliveryType, $paymentMethod, $request->ids), "laporan-laundry-terpilih{$paymentLabel}.xlsx");
    }

    // --- FUNGSI BARU UNTUK HAPUS BANYAK DATA SEKALIGUS ---
    public function bulkDelete(Request $request)
    {
        // 1. Validasi apakah ada array 'ids' yang dikirim dari checkbox
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:orders,id' 
        ]);

        try {
            // 2. Eksekusi penghapusan data
            Order::whereIn('id', $request->ids)->delete();

            // 3. Kembalikan ke halaman sebelumnya dengan pesan sukses
            return redirect()->back()->with('success', count($request->ids) . ' data laporan berhasil dihapus.');
        } catch (\Exception $e) {
            // Jika terjadi error (misalnya masalah database)
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}