<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Order;

class DashboardStats extends Component
{
    public function render()
    {
        return view('livewire.dashboard-stats', [
            'total' => Order::where('status', '!=', 'DIAMBIL')->count(),
            'antrian' => Order::where('status', 'ANTRIAN')->count(),
            'proses' => Order::where('status', 'PROSES')->count(),
            'selesai' => Order::where('status', 'SELESAI')->count(),
        ]);
    }
}
