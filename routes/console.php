<?php

use App\Services\MidtransPaymentService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('midtrans:sync {orderId}', function (string $orderId) {
    try {
        $order = app(MidtransPaymentService::class)->syncByOrderId($orderId);
    } catch (\Throwable $exception) {
        $this->error($exception->getMessage());

        return 1;
    }

    if (!$order) {
        $this->warn('Order Midtrans tidak ditemukan.');

        return 1;
    }

    $this->info(sprintf(
        'Order %s disinkronkan. Status: %s',
        $order->order_code,
        $order->payment_status
    ));

    return 0;
})->purpose('Sync status pembayaran Midtrans secara manual berdasarkan order ID');
