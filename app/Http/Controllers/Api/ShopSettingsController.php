<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopSettingsController extends Controller
{
    public function getSettings(Request $request)
    {
        $user = Auth::user();
        $shop = $user->getOwnerShop();

        if (!$shop) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'shop_name'           => null,
                    'device_id'           => null,
                    'shop_latitude'       => null,
                    'shop_longitude'      => null,
                    'delivery_fee_per_km' => 0,
                    'free_delivery_km'    => 0,
                    'is_delivery_active'  => false,
                    'is_estimation_active'=> false,
                    'express_extra_price' => 0,
                    'kilat_extra_price'   => 0,
                ]
            ], 200);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'shop_name'           => $shop->shop_name,
                'device_id'           => $shop->device_id,
                'shop_latitude'       => $shop->latitude,
                'shop_longitude'      => $shop->longitude,
                'delivery_fee_per_km' => $shop->delivery_fee_per_km,
                'free_delivery_km'    => $shop->free_delivery_km ?? 0,
                'is_delivery_active'  => $shop->is_delivery_active,
                'is_estimation_active'=> $shop->is_estimation_active ?? false,
                'express_extra_price' => $shop->express_extra_price ?? 0,
                'kilat_extra_price'   => $shop->kilat_extra_price ?? 0,
            ]
        ], 200);
    }

    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        $ownerId = $user->getOwnerId();

        $validated = $request->validate([
            'shop_name'           => 'nullable|string|max:100',
            'device_id'           => 'nullable|string|max:50',
            'shop_latitude'       => 'nullable|numeric',
            'shop_longitude'      => 'nullable|numeric',
            'delivery_fee_per_km' => 'required|integer|min:0',
            'free_delivery_km'    => 'required|integer|min:0',
            'is_delivery_active'  => 'required|boolean',
            'is_estimation_active'=> 'required|boolean',
            'express_extra_price' => 'required|integer|min:0',
            'kilat_extra_price'   => 'required|integer|min:0',
        ]);

        Shop::updateOrCreate(
            ['user_id' => $ownerId],
            [
                'shop_name'           => $validated['shop_name'] ?? null,
                'device_id'           => $validated['device_id'] ?? null,
                'latitude'            => $validated['shop_latitude'] ?? null,
                'longitude'           => $validated['shop_longitude'] ?? null,
                'delivery_fee_per_km' => $validated['delivery_fee_per_km'],
                'free_delivery_km'    => $validated['free_delivery_km'],
                'is_delivery_active'  => $validated['is_delivery_active'],
                'is_estimation_active'=> $validated['is_estimation_active'],
                'express_extra_price' => $validated['express_extra_price'],
                'kilat_extra_price'   => $validated['kilat_extra_price'],
            ]
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Pengaturan toko berhasil disimpan.',
        ], 200);
    }
}
