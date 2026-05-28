<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Shop;

class UserController extends Controller
{
    public function settings()
    {
        $ownerId = Auth::id();

        // Ambil data toko milik Owner (buat baru jika belum ada, meski seeder sudah handle)
        $shop = Shop::firstOrCreate(
            ['user_id' => $ownerId],
            [
                'shop_name' => 'Toko Laundry (Default)',
                'delivery_fee_per_km' => 0,
                'free_delivery_km' => 0,
                'is_delivery_active' => false,
                'is_estimation_active' => false,
                'express_extra_price' => 0,
                'kilat_extra_price' => 0,
            ]
        );

        $staffs = User::where('owner_id', $ownerId)->get();

        return view('settings', compact('staffs', 'shop'));
    }

    public function updateShopSettings(Request $request)
    {
        $request->validate([
            'shop_name' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'delivery_fee_per_km' => 'nullable|numeric|min:0',
            'free_delivery_km' => 'nullable|numeric|min:0',
            'is_delivery_active' => 'nullable|boolean',
            'is_estimation_active' => 'nullable|boolean',
            'express_extra_price' => 'nullable|numeric|min:0',
            'kilat_extra_price' => 'nullable|numeric|min:0',
        ]);

        $shop = Shop::where('user_id', Auth::id())->first();
        if ($shop) {
            $shop->update([
                'shop_name' => $request->shop_name,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'delivery_fee_per_km' => $request->delivery_fee_per_km ?? 0,
                'free_delivery_km' => $request->free_delivery_km ?? 0,
                'is_delivery_active' => $request->has('is_delivery_active'),
                'is_estimation_active' => $request->has('is_estimation_active'),
                'express_extra_price' => $request->express_extra_price ?? 0,
                'kilat_extra_price' => $request->kilat_extra_price ?? 0,
            ]);
        }

        return redirect()->back()->with('success', 'Pengaturan Toko berhasil diperbarui!');
    }
}
