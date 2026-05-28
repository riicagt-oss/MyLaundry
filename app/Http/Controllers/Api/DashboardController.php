<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Exception;

class DashboardController extends Controller
{
    public function getStats()
    {
        try {
            $stats = [
                'antrian' => (int) Order::where('status', 'ANTRIAN')->count(),
                'proses'  => (int) Order::where('status', 'PROSES')->count(),
                'selesai' => (int) Order::where('status', 'SELESAI')->count(),
            ];

            return response()->json([
                'success' => true,
                'data'    => $stats
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil stats: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getActivities()
    {
        try {
            // PERBAIKAN: Tambahkan estimation_time, created_at di dalam select
            $activities = Order::with('items')->select('id', 'order_number', 'customer_name', 'phone', 'status', 'updated_at', 'delivery_fee', 'total_price', 'delivery_type', 'payment_method', 'cash_received', 'estimation_time', 'created_at')
                ->orderBy('updated_at', 'desc')
                ->limit(50)
                ->get()
                ->map(function ($order) {
                    $services = $order->items->map(function ($item) {
                        $qty = fmod($item->qty_or_weight, 1) == 0 ? (int)$item->qty_or_weight : $item->qty_or_weight;
                        return $item->service_name . ' (' . $qty . ' ' . $item->unit . ')';
                    })->implode(' + ');

                    $subtitle = $services;
                    if ($order->delivery_fee > 0) {
                        $ongkir = number_format($order->delivery_fee, 0, ',', '.');
                        $subtitle .= ($subtitle ? " + " : "") . "Ongkir: Rp $ongkir";
                    }
                    $isPaid = strtoupper($order->payment_method) !== 'BAYAR NANTI' || $order->cash_received > 0;
                    // Calculate estimation type
                    $tipeEstimasi = "Reguler";
                    if ($order->estimation_time && $order->created_at) {
                        $diffInHours = $order->created_at->diffInHours($order->estimation_time);
                        if ($diffInHours <= 6) {
                            $tipeEstimasi = "Kilat";
                        } elseif ($diffInHours <= 24) {
                            $tipeEstimasi = "Ekspres";
                        }
                    }

                    return [
                        'id'             => $order->id,
                        'order_number'   => $order->order_number,
                        'title'          => (string) $order->customer_name,
                        'subtitle'       => $subtitle,
                        'phone'          => $order->phone ?? "-",
                        'time'           => $order->updated_at ? $order->updated_at->diffForHumans() : '-',
                        'status'         => (string) $order->status,
                        'total'          => $order->total_price,
                        'delivery_type'  => $order->delivery_type ?? 'none',
                        'payment_status' => $isPaid ? 'PAID' : 'UNPAID',
                        'payment_method' => $order->payment_method,
                        'cash_received'  => $order->cash_received,
                        'estimation_time'=> $order->estimation_time ? $order->estimation_time->toDateTimeString() : '',
                        'estimation_type'=> $tipeEstimasi,
                    ];
                });

            return response()->json([
                'success' => true,
                'data'    => $activities
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil aktivitas: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getOrdersByStatus($status)
    {
        try {
            $orders = Order::with('items')->where('status', strtoupper($status))
                ->latest()
                ->get()
                ->map(function ($order) {
                    $rawService = $order->items->map(function ($item) {
                        return trim($item->service_name);
                    })->implode(', ');

                    $berat = $order->total_weight ?? '0';
                    $jasa = $rawService;

                    // --- PERBAIKAN DI SINI ---
                    // Ambil data langsung dari kolom estimation_time di database
                    // Jika null, kirim string kosong agar ditangani Flutter sebagai "Belum diatur"
                    $estimationTime = $order->estimation_time ? $order->estimation_time->toDateTimeString() : '';

                    $tipeEstimasi = "Reguler";

                    // 1. Deteksi Tipe Estimasi berdasarkan selisih waktu
                    if ($order->estimation_time && $order->created_at) {
                        $diffInHours = $order->created_at->diffInHours($order->estimation_time);
                        if ($diffInHours <= 6) {
                            $tipeEstimasi = "Kilat";
                        } elseif ($diffInHours <= 24) {
                            $tipeEstimasi = "Ekspres";
                        }
                    }

                    $isPaid = strtoupper($order->payment_method) !== 'BAYAR NANTI' || $order->cash_received > 0;
                    return [
                        'id'              => $order->id,
                        'order_number'    => $order->order_number,
                        'customer_name'   => $order->customer_name,
                        'phone'           => $order->phone ?? "-",
                        'status'          => $order->status,
                        'weight'          => $berat,
                        'service_name'    => $jasa,
                        'estimation_type' => $tipeEstimasi,
                        'estimation_time' => $estimationTime,
                        'total_price'     => number_format($order->total_price ?? 0, 0, ',', '.'),
                        'delivery_fee'    => $order->delivery_fee ?? 0,
                        'delivery_type'   => $order->delivery_type ?? 'none',
                        'payment_status'  => $isPaid ? 'PAID' : 'UNPAID',
                        'payment_method'  => $order->payment_method,
                        'cash_received'   => $order->cash_received,
                    ];
                });

            return response()->json(['success' => true, 'data' => $orders->values()->all()], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
