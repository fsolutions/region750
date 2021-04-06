<?php

namespace App\Observers;

use App\Models\Order;
use App\Bundles\Order\CustomSync;

class OrderCascadeDeletesObserver
{
    /**
     * Handle the Order "deleting" event.
     *
     * @param Order $order
     * @return void
     */
    public function deleting(Order $order)
    {
        $order->documents()->delete();
    }
}
