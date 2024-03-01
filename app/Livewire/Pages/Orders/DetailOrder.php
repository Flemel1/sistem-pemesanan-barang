<?php

namespace App\Livewire\Pages\Orders;

use App\Models\Order;
use Livewire\Component;

class DetailOrder extends Component
{

    public Order $order;

    public function mount(Order $order)
    {
        $customerOrder = Order::with(['customer', 'details'])->where('id', '=', $order->id)->get()->first();
        $this->order = $customerOrder;
    }

    public function render()
    {
        return view('livewire.pages.orders.detail-order');
    }
}
