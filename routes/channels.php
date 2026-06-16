<?php

use Illuminate\Support\Facades\Broadcast;

/**
 * Channel "orders" — public channel, semua kasir & admin yang login bisa subscribe.
 * Pelanggan tidak perlu subscribe; mereka hanya submit order.
 */
Broadcast::channel('orders', function ($user) {
    return $user && $user->hasRole(['admin', 'cashier']);
});
