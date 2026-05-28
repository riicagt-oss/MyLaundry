<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Mengambil data 7 hari terakhir
        $chartData = [];
        $days = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $days[] = $date->translatedFormat('D d M'); // Contoh: Sen 12 Mei

            // Hitung jumlah order pada tanggal tersebut
            $chartData[] = \App\Models\Order::whereDate('created_at', $date->toDateString())->count();
        }

        // Mencari nilai tertinggi untuk skala grafik (minimal 10 agar tidak mentok ke atas)
        $maxVal = max($chartData) > 0 ? max($chartData) : 10;

        // Konversi nilai data ke koordinat SVG (Tinggi SVG kita 200)
        // Rumus: 200 - (nilai / maxVal * 150) -> 150 agar ada sisa ruang di atas
        $points = "";
        foreach ($chartData as $index => $value) {
            $x = $index * 166.6;
            $y = 200 - ($value / $maxVal * 150);
            $points .= "$x,$y ";
        }

        return view('dashboard', [
            'total' => \App\Models\Order::where('status', '!=', 'DIAMBIL')->count(),
            'antrian' => \App\Models\Order::where('status', 'ANTRIAN')->count(),
            'proses' => \App\Models\Order::where('status', 'PROSES')->count(),
            'selesai' => \App\Models\Order::where('status', 'SELESAI')->count(),
            'latest' => \App\Models\Order::latest()->take(5)->get(),
            'chartPoints' => trim($points),
            'days' => $days,
            'chartData' => $chartData
        ]);
    }
}
