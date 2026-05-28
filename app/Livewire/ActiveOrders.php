<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Order;

class ActiveOrders extends Component
{
    public function render()
    {
        return view('livewire.active-orders', [
            'latest' => Order::with('items')
                ->where('status', '!=', 'DIAMBIL')
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }
}
