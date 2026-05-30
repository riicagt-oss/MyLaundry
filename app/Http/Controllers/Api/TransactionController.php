<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http; 
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    // 1. Endpoint untuk Staf: Membuat Jadwal Jemput (Order Awal)
    public function requestPickup(Request $request)
    {
        $validated = $request->validate([
            'customer_name' => 'required|string',
            'phone'         => 'required|string',
            'address'       => 'nullable|string',
            'latitude'      => 'nullable',
            'longitude'     => 'nullable',
        ]);

        $user = Auth::user();
        $ownerId = $user->getOwnerId();

        $today = Carbon::today();
        $prefix = 'ORD-' . $today->format('Ymd') . '-' . $ownerId . '-';
        $lastOrder = Order::withoutGlobalScopes()
            ->where('user_id', $ownerId)
            ->where('order_number', 'LIKE', $prefix . '%')
            ->orderBy('order_number', 'desc')
            ->first();
        
        $nextNum = 1;
        if ($lastOrder) {
            $lastNum = (int) substr($lastOrder->order_number, -3);
            $nextNum = $lastNum + 1;
        }
        $orderNumber = $prefix . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

        $order = Order::create([
            'order_number'  => $orderNumber,
            'user_id'       => $ownerId,
            'customer_name' => $validated['customer_name'],
            'phone'         => $validated['phone'],
            'address'       => $validated['address'],
            'latitude'      => $validated['latitude'],
            'longitude'     => $validated['longitude'],
            'status'        => 'PICKUP', // Status awal
            'total_price'   => 0,
            'total_weight'  => 0,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Jadwal jemput berhasil dibuat',
            'data' => $order
        ], 201);
    }

    // 2. Endpoint untuk Driver: Mengambil daftar tugas (Jemput & Antar)
    public function getDriverTasks()
    {
        $user = Auth::user();
        $ownerId = $user->getOwnerId();

        $tasks = Order::with('items')->where('user_id', $ownerId)
            ->where(function($query) {
                $query->whereIn('status', ['PICKUP', 'MENUNGGU JEMPUT', 'DI DRIVER', 'DELIVERY'])
                      ->orWhere(function($q) {
                          // Untuk kompatibilitas mundur jika ada pesanan lama
                          $q->where('status', 'SELESAI')
                            ->whereIn('delivery_type', ['delivery', 'both']);
                      });
            })
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function($order) {
                $tipeEstimasi = "Reguler";
                if ($order->estimation_time && $order->created_at) {
                    $diffInHours = $order->created_at->diffInHours($order->estimation_time);
                    if ($diffInHours <= 6) $tipeEstimasi = "Kilat";
                    elseif ($diffInHours <= 24) $tipeEstimasi = "Ekspres";
                } else {
                    $tipeEstimasi = "-";
                }

                $serviceNameStr = $order->items->map(function($item) {
                    $qtyStr = $item->unit === 'PCS' ? round($item->qty_or_weight) . ' PCS' : number_format($item->qty_or_weight, 3, '.', '') . ' KG';
                    return "{$item->service_name} ({$qtyStr})||" . $item->subtotal;
                })->implode("\n");

                return [
                    'id'            => $order->id,
                    'order_number'  => $order->order_number,
                    'type_id'       => (in_array($order->status, ['PICKUP', 'MENUNGGU JEMPUT']) ? 'P' : 'O') . $order->id,
                    'task_type'     => (in_array($order->status, ['PICKUP', 'MENUNGGU JEMPUT']) ? 'jemput' : 'antar'),
                    'customer_name' => $order->customer_name,
                    'phone'         => $order->phone,
                    'address'       => $order->address,
                    'latitude'      => $order->latitude,
                    'longitude'     => $order->longitude,
                    'status'        => $order->status,
                    'service_name'  => $serviceNameStr,
                    'weight'        => $order->total_weight,
                    'delivery_type' => $order->delivery_type,
                    'delivery_fee'  => $order->delivery_fee,
                    'total_price'   => $order->total_price,
                    'payment_method'=> $order->payment_method,
                    'cash_received' => $order->cash_received,
                    'estimation_type'=> $tipeEstimasi,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $tasks
        ], 200);
    }

    public function store(Request $request)
    {
        if (!$request->expectsJson()) {
            return response()->json(['message' => 'Harus menggunakan header Accept application/json'], 406);
        }

        $validated = $request->validate([
            'customer_name'  => 'required|string',
            'phone'          => 'required|string',
            'items'          => 'required|array',
            'items.*.service_id' => 'nullable',
            'items.*.service_name' => 'required|string',
            'items.*.price' => 'required|numeric',
            'items.*.qty_or_weight' => 'required|numeric',
            'items.*.unit' => 'required|string',
            'items.*.subtotal' => 'required|numeric',
            'total_price'    => 'required',
            'total_weight'   => 'required',
            'payment_method' => 'nullable|string',
            'cash_received'  => 'nullable',
            'address'        => 'nullable|string',
            'latitude'       => 'nullable',
            'longitude'      => 'nullable',
            'delivery_type'  => 'nullable|string',
        ]);

        $user = Auth::user();
        $ownerId = $user->getOwnerId();
        $shop = $user->getOwnerShop();
        
        if (!$shop) {
            // Jika shop belum ada, buat default (agar tidak error)
            $shop = Shop::create(['user_id' => $ownerId]);
        }

        $deliveryFee = 0;
        $deliveryDistance = 0;
        $deliveryType = $request->delivery_type ?? 'none';
        
        if ($deliveryType !== 'none' && $shop->is_delivery_active && 
            $request->latitude && $request->longitude && $shop->latitude && $shop->longitude) {
            
            try {
                $url = "http://router.project-osrm.org/route/v1/driving/{$shop->longitude},{$shop->latitude};{$request->longitude},{$request->latitude}?overview=false";
                $osrmResponse = Http::timeout(5)->get($url);
                
                if ($osrmResponse->successful()) {
                    $osrmData = $osrmResponse->json();
                    if (isset($osrmData['routes'][0]['distance'])) {
                        $deliveryDistance = round($osrmData['routes'][0]['distance'] / 1000, 2);
                        
                        // Logika Gratis KM dinamis dari shop setting
                        $freeDeliveryKm = $shop->free_delivery_km ?? 0;
                        if ($deliveryDistance <= $freeDeliveryKm) {
                            $deliveryFee = 0;
                        } else {
                            $chargeableDistance = $deliveryDistance - $freeDeliveryKm;
                            $baseFee = $chargeableDistance * $shop->delivery_fee_per_km;
                            $multiplier = ($deliveryType === 'both') ? 2 : 1;
                            $rawFee = $baseFee * $multiplier;
                            $deliveryFee = (int) $rawFee;
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::error("OSRM Error: " . $e->getMessage());
            }
        }

        $today = Carbon::today();
        $isUpdate = ($request->has('order_id') && !empty($request->order_id));
        $orderNumber = "";

        // Fungsi untuk generate nomor urut berikutnya yang aman per owner
        $getNextOrderNumber = function() use ($today, $ownerId) {
            $prefix = 'ORD-' . $today->format('Ymd') . '-' . $ownerId . '-';
            $lastOrder = Order::withoutGlobalScopes()
                ->where('user_id', $ownerId)
                ->where('order_number', 'LIKE', $prefix . '%')
                ->orderBy('order_number', 'desc')
                ->first();
            
            $nextNum = 1;
            if ($lastOrder) {
                $lastNum = (int) substr($lastOrder->order_number, -3);
                $nextNum = $lastNum + 1;
            }
            return $prefix . str_pad($nextNum, 3, '0', STR_PAD_LEFT);
        };
        
        if (!$isUpdate) {
            $orderNumber = $getNextOrderNumber();
        }

        $estType = strtolower($request->estimation_type ?? '');
        if (str_contains($estType, 'none')) {
            $estimationTime = Carbon::now()->addDays(3);
        } elseif (str_contains($estType, 'kilat')) {
            $estimationTime = Carbon::now()->addHours(6);
        } elseif (str_contains($estType, 'ekspres') || str_contains($estType, 'express')) {
            $estimationTime = Carbon::now()->addDays(1);
        } else {
            $estimationTime = Carbon::now()->addDays(3);
        }

        $cashReceived = intval($request->cash_received ?? 0);
        $totalPrice = intval($validated['total_price']) + $deliveryFee;
        $totalPrice = (int) (ceil($totalPrice / 500) * 500); // Bulatkan ke atas kelipatan 500
        $cashChange = $cashReceived - $totalPrice;

        try {
            if ($isUpdate) {
                $order = Order::find($request->order_id);
                if ($order) {
                    $updateData = [
                        'total_price'    => $totalPrice,
                        'total_weight'   => $validated['total_weight'],
                        'status'         => 'ANTRIAN',
                        'estimation_time' => $estimationTime,
                        'payment_method' => strtoupper($request->payment_method ?? 'CASH'),
                        'cash_received'  => $cashReceived,
                        'cash_change'    => $cashChange < 0 ? 0 : $cashChange,
                        'delivery_type'   => $deliveryType,
                        'delivery_fee'    => $deliveryFee,
                        'delivery_distance' => $deliveryDistance,
                    ];
                    
                    // Jika sebelumnya masih format PCK, ubah ke ORD
                    if (str_starts_with($order->order_number, 'PCK')) {
                        $updateData['order_number'] = $getNextOrderNumber();
                    }

                    $order->update($updateData);

                    // Recreate items
                    $order->items()->delete();
                    foreach ($validated['items'] as $item) {
                        $order->items()->create([
                            'service_id' => $item['service_id'] ?? null,
                            'service_name' => $item['service_name'],
                            'price' => $item['price'],
                            'qty_or_weight' => $item['qty_or_weight'],
                            'unit' => $item['unit'],
                            'subtotal' => $item['subtotal'],
                            'estimation_name' => $item['estimation_name'] ?? null,
                        ]);
                    }
                } else {
                    return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
                }
            } else {
                $order = Order::create([
                    'order_number'   => $orderNumber,
                    'user_id'        => $ownerId,
                    'customer_name'  => $validated['customer_name'],
                    'phone'          => $validated['phone'],
                    'total_price'    => $totalPrice,
                    'total_weight'   => $validated['total_weight'],
                    'status'         => 'ANTRIAN',
                    'estimation_time' => $estimationTime,
                    'payment_method' => strtoupper($request->payment_method ?? 'CASH'),
                    'cash_received'  => $cashReceived,
                    'cash_change'    => $cashChange < 0 ? 0 : $cashChange,
                    'address'        => $request->address,
                    'latitude'       => $request->latitude,
                    'longitude'      => $request->longitude,
                    'delivery_type'   => $deliveryType,
                    'delivery_fee'    => $deliveryFee,
                    'delivery_distance' => $deliveryDistance,
                ]);

                foreach ($validated['items'] as $item) {
                    $order->items()->create([
                        'service_id' => $item['service_id'] ?? null,
                        'service_name' => $item['service_name'],
                        'price' => $item['price'],
                        'qty_or_weight' => $item['qty_or_weight'],
                        'unit' => $item['unit'],
                        'subtotal' => $item['subtotal'],
                        'estimation_name' => $item['estimation_name'] ?? null,
                    ]);
                }
            }
        } catch (\Exception $e) {
            \Log::error("Save Order Error: " . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan ke database: ' . $e->getMessage()], 500);
        }

        try {
            $shopName = $shop->shop_name ?? 'My Laundry';
            $estimasiStr = $order->estimation_time ? $order->estimation_time->format('d/m/Y H:i') : '';
            
            $tipeEstimasiLabel = "Reguler";
            if ($order->estimation_time && $order->created_at) {
                $diffInHours = $order->created_at->diffInHours($order->estimation_time);
                if ($diffInHours <= 6) $tipeEstimasiLabel = "Kilat";
                elseif ($diffInHours <= 24) $tipeEstimasiLabel = "Ekspres";
            }

            $metodeBayar = $order->payment_method;
            $hargaStr = number_format($order->total_price, 0, ',', '.');
            $ongkirStr = number_format($order->delivery_fee, 0, ',', '.');
            $uangMasukStr = number_format($order->cash_received, 0, ',', '.');
            $kembalianStr = number_format($order->cash_change, 0, ',', '.');

            // Format berat menjadi 1 angka di belakang koma (misal 3.12 -> 3.1)
            $weightFormatted = number_format((float)$order->total_weight, 1, '.', '');

            // Format Layanan secara dinamis (Selalu Baris Baru & Rata Kiri)
            $serviceInfo = "👕 *Layanan:*\n";
            
            $i = 1;
            foreach ($order->items as $item) {
                $qtyStr = $item->unit === 'PCS' ? round($item->qty_or_weight) . ' PCS' : number_format($item->qty_or_weight, 3, '.', '') . ' KG';
                $priceStr = number_format($item->price, 0, ',', '.');
                $serviceInfo .= $i . ". {$item->service_name} ({$qtyStr}) - Rp $priceStr\n";
                $i++;
            }

            $pesanBaru = "*NOTA $shopName*\n"
                . "--------------------------------------\n"
                . "Halo, ini konfirmasi atas pesanan bernama *{$order->customer_name}*.\n"
                . "Pesanan laundry Anda telah kami terima! 🙏\n\n"
                . "🧾 *No Order:* {$order->order_number}\n"
                . $serviceInfo;

            if ($order->estimation_time) {
                if (strtolower($request->estimation_type ?? '') === 'none') {
                    $pesanBaru .= "⏱️ *Estimasi Selesai:* $estimasiStr\n";
                } else {
                    $pesanBaru .= "⏱️ *Estimasi Selesai:* $tipeEstimasiLabel ($estimasiStr)\n";
                }
            }
            $pesanBaru .= "--------------------------------------\n";

            // DETAIL PENGIRIMAN (Jika menggunakan jasa Driver)
            if ($order->delivery_type !== 'none') {
                $typeLabel = $this->getDeliveryTypeLabel($order->delivery_type);

                $pesanBaru .= "*DETAIL PENGIRIMAN*\n"
                            . "🛵 *Layanan:* $typeLabel\n";
                
                if ($shop && $shop->is_delivery_active) {
                    $pesanBaru .= "📍 *Jarak:* {$order->delivery_distance} Km\n";
                }
                
                if ($order->delivery_fee > 0) {
                    $pesanBaru .= "🚚 *Ongkos Kirim:* Rp $ongkirStr\n";
                } else {
                    $pesanBaru .= "🚚 *Ongkos Kirim:* Gratis\n";
                }
                $pesanBaru .= "--------------------------------------\n";
            }

            $pesanBaru .= "*RINCIAN PEMBAYARAN*\n"
                . "💳 *Metode:* $metodeBayar\n"
                . "💰 *Total Tagihan:* *Rp $hargaStr*\n";

            // Hanya paparkan Uang Masuk & Kembalian jika pelanggan membayar secara Tunai (CASH)
            if ($metodeBayar === 'CASH') {
                $pesanBaru .= "💵 *Uang Tunai:* Rp $uangMasukStr\n"
                            . "🔄 *Kembalian:* Rp $kembalianStr\n";
            }

            $pesanBaru .= "--------------------------------------\n";
            if ($metodeBayar === 'BAYAR NANTI') {
                $pesanBaru .= "Status Pembayaran: *BELUM LUNAS*\n"
                            . "Silakan melakukan pelunasan saat cucian diambil.\n";
            } else {
                $pesanBaru .= "Status Pembayaran: *LUNAS*\n";
            }

            $pesanBaru .= "--------------------------------------\n"
                . "Status saat ini: *DALAM ANTRIAN*\n"
                . "Kami akan menginfokan kembali jika pesanan sudah diproses. Terima kasih!";

            $this->sendWhatsAppMessage($order->phone, $pesanBaru);
        } catch (\Exception $e) {}

        return response()->json([
            'status' => 'success',
            'message' => 'Pesanan berhasil masuk',
            'data'   => $order
        ], 201);
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) return response()->json(['message' => 'Data tidak ditemukan'], 404);

        $currentStatus = strtoupper($order->status);
        $nextStatus = $currentStatus;

        if ($currentStatus === 'PICKUP' || $currentStatus === 'MENUNGGU JEMPUT') $nextStatus = 'TIBA DI TOKO';
        elseif ($currentStatus === 'TIBA DI TOKO') $nextStatus = 'ANTRIAN';
        elseif ($currentStatus === 'ANTRIAN') $nextStatus = 'PROSES';
        elseif ($currentStatus === 'PROSES') $nextStatus = 'SELESAI';
        elseif ($currentStatus === 'SELESAI') {
            if (in_array(strtolower($order->delivery_type), ['delivery', 'both'])) {
                $nextStatus = 'DELIVERY';
            } else {
                $nextStatus = 'DIAMBIL';
            }
        }
        elseif ($currentStatus === 'DELIVERY' || $currentStatus === 'DI DRIVER') $nextStatus = 'DIAMBIL';

        $order->status = $nextStatus;
        
        // Cek jika ada data pembayaran tambahan (saat pelunasan)
        if ($request->has('payment_method')) {
            $order->payment_method = $request->payment_method;
            $order->cash_received = $request->cash_received;
            $order->cash_change = ($request->cash_received - $order->total_price);
        }

        $order->save();

        // --- KIRIM WA OTOMATIS SAAT STATUS BERUBAH ---
        try {
            $shop = Auth::user()->getOwnerShop();
            $shopName = $shop->shop_name ?? 'My Laundry';
            $namaPelanggan = $order->customer_name;
            $pesanUpdate = "*UPDATE $shopName*\n\nHalo, ini update atas pesanan bernama *{$namaPelanggan}*.\n";

            if ($nextStatus === 'PROSES') {
                $pesanUpdate .= "Pesanan (No: *{$order->order_number}*) saat ini sedang *DIPROSES/DICUCI* 🫧\nKami akan infokan kembali jika sudah selesai.";
            } elseif ($nextStatus === 'SELESAI') {
                $pesanUpdate .= "Pesanan (No: *{$order->order_number}*) sudah *SELESAI*! 🎉\n\n";
                
                // Jika belum dibayar lunas
                if (strtoupper($order->payment_method) === 'BAYAR NANTI' && $order->cash_received == 0) {
                    $hargaStr = number_format($order->total_price, 0, ',', '.');
                    $pesanUpdate .= "Tagihan sebesar *Rp $hargaStr* dapat dilunasi saat pengambilan ya.\n\n";
                }
                
                $pesanUpdate .= "Ditunggu kedatangannya!";
            } elseif ($nextStatus === 'DI DRIVER' || $nextStatus === 'DELIVERY') {
                $pesanUpdate .= "Pesanan (No: *{$order->order_number}*) sudah selesai dicuci dan sedang *MENUNGGU DRIVER* untuk diantarkan ke lokasi Anda. 🚚\n\n";
                if (strtoupper($order->payment_method) === 'BAYAR NANTI' && $order->cash_received == 0) {
                    $hargaStr = number_format($order->total_price, 0, ',', '.');
                    $pesanUpdate .= "Tagihan sebesar *Rp $hargaStr* dapat dilunasi kepada Driver kami saat pesanan tiba ya.\n\n";
                }
                $pesanUpdate .= "Mohon ditunggu, terima kasih!";
            } elseif ($nextStatus === 'TIBA DI TOKO') {
                $pesanUpdate = "Halo *$namaPelanggan*,\n\n"
                             . "Pesanan Anda (No: *{$order->order_number}*) sudah sampai di toko kami dan akan segera kami input. ✅\n\n"
                             . "Terima kasih! 🙏";
            } elseif ($nextStatus === 'DIAMBIL') {
                if ($order->address) {
                    $pesanUpdate .= "Pesanan (No: *{$order->order_number}*) telah *DIKIRIM* ke alamat Anda. 🚚\n";
                } else {
                    $pesanUpdate .= "Pesanan (No: *{$order->order_number}*) telah *DIAMBIL*. ✅\n";
                }
                $pesanUpdate .= "Terima kasih telah mempercayakan cucian Anda di $shopName! 🙏";
            }

            if ($nextStatus !== $currentStatus) {
                $this->sendWhatsAppMessage($order->phone, $pesanUpdate);
            }
        } catch (\Exception $e) {
            \Log::error("WhatsApp Update Error: " . $e->getMessage());
        }
        // --------------------------------------------- 

        return response()->json(['status' => 'success', 'message' => 'Status diperbarui']);
    }

    public function getOrdersByStatus($status)
    {
        $user = Auth::user();
        $ownerId = $user->getOwnerId();

        $orders = Order::with('items')->where('user_id', $ownerId)
            ->where('status', strtoupper($status))
            ->get()
            ->map(function ($order) {
                $tipeEstimasi = "Reguler";
                if ($order->estimation_time && $order->created_at) {
                    $diffInHours = $order->created_at->diffInHours($order->estimation_time);
                    if ($diffInHours <= 6) $tipeEstimasi = "Kilat";
                    elseif ($diffInHours <= 24) $tipeEstimasi = "Ekspres";
                } else {
                    $tipeEstimasi = "-";
                }
                
                $order->estimation_type = $tipeEstimasi;
                $order->weight = $order->total_weight;
                $order->service_name = $order->items->map(function($item) {
                    $qtyStr = $item->unit === 'PCS' ? round($item->qty_or_weight) . ' PCS' : number_format($item->qty_or_weight, 3, '.', '') . ' KG';
                    return "{$item->service_name} ({$qtyStr}) - Rp " . number_format($item->price, 0, ',', '.');
                })->implode("\n");

                return $order;
            });

        return response()->json(['status' => 'success', 'data' => $orders], 200);
    }

    public function checkDeliveryFee(Request $request)
    {
        $user = Auth::user();
        $shop = $user->getOwnerShop();

        if (!$shop || !$shop->is_delivery_active) {
            return response()->json(['delivery_fee' => 0, 'distance' => 0]);
        }

        $lat = $request->latitude;
        $lng = $request->longitude;
        $deliveryType = $request->delivery_type; // 'pickup', 'delivery', 'both'

        if (!$lat || !$lng || !$shop->latitude || !$shop->longitude) {
            return response()->json(['delivery_fee' => 0, 'distance' => 0]);
        }

        try {
            $url = "http://router.project-osrm.org/route/v1/driving/{$shop->longitude},{$shop->latitude};{$lng},{$lat}?overview=false";
            $response = Http::timeout(5)->get($url);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['routes'][0]['distance'])) {
                    $distance = round($data['routes'][0]['distance'] / 1000, 2);
                    
                    // Logika Gratis KM dinamis dari shop setting
                    $freeDeliveryKm = $shop->free_delivery_km ?? 0;
                    if ($distance <= $freeDeliveryKm) {
                        $finalFee = 0;
                    } else {
                        $chargeableDistance = $distance - $freeDeliveryKm;
                        $baseFee = $chargeableDistance * $shop->delivery_fee_per_km;
                        
                        $multiplier = ($deliveryType === 'both') ? 2 : 1;
                        $rawFee = $baseFee * $multiplier;
                        
                        // Tanpa pembulatan
                        $finalFee = (int) $rawFee;
                    }

                    return response()->json([
                        'delivery_fee' => $finalFee,
                        'distance' => $distance
                    ]);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['delivery_fee' => 0, 'distance' => 0, 'error' => $e->getMessage()], 500);
        }

        return response()->json(['delivery_fee' => 0, 'distance' => 0]);
    }

    public function sendManualWhatsApp(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'task_type' => 'required',
            'customer_name' => 'required'
        ]);

        $customerName = $request->customer_name;
        $shop = Auth::user()->getOwnerShop();
        $shopName = $shop->shop_name ?? 'My Laundry';
        $message = "";

        if ($request->task_type == 'jemput') {
            $message = "Halo *$customerName*, ini dari *$shopName*. Kami akan menjemput cucian Anda sekarang. Mohon disiapkan ya, terima kasih! 🙏";
        } else {
            $message = "Halo *$customerName*, ini dari *$shopName*. Pesanan Anda sudah selesai dan akan kami antarkan sekarang. Mohon ditunggu di lokasi ya, terima kasih! 🙏";
        }

        $success = $this->sendWhatsAppMessage($request->phone, $message);

        return response()->json(['status' => $success ? 'success' : 'error']);
    }

    /**
     * Helper: Konversi delivery_type ke label Indonesia
     */
    private function getDeliveryTypeLabel(string $type): string
    {
        return match($type) {
            'pickup'   => 'Pick-up',
            'delivery' => 'Delivery',
            'both'     => 'Pick-up & Delivery',
            default    => 'Takeaway',
        };
    }

    private function sendWhatsAppMessage($phoneTarget, $messageText)
    {
        if (empty($phoneTarget)) return false;
        $token = env('FONNTE_TOKEN'); 

        try {
            $response = Http::withHeaders(['Authorization' => $token])->post('https://api.fonnte.com/send', [
                'target' => $phoneTarget,
                'message' => $messageText,
                'countryCode' => '62',
            ]);
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
