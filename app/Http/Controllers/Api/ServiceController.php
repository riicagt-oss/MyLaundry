<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        try {
            // Berkat Global Scope di Model, ini otomatis hanya mengambil 
            // jasa aktif MILIK owner yang sedang login (via token API)
            $services = Service::where('is_active', 1)->get();

            return response()->json([
                'success' => true,
                'message' => 'Daftar Jasa Aktif Berhasil Dimuat',
                'data'    => $services
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}