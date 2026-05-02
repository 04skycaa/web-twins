<?php

namespace App\Services;

use App\Models\PaymentOrder;
use App\Models\ProductStore;
use App\Models\StockCard;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Midtrans\Config;
use Midtrans\Transaction;

class MidtransPaymentService
{
    public function verifyWebhookSignature(array $payload): bool
    {
        $serverKey = (string) config('services.midtrans.server_key', '');

        if ($serverKey === '') {
            throw new InvalidArgumentException('Midtrans server key belum dikonfigurasi.');
        }

        foreach (['order_id', 'status_code', 'gross_amount', 'signature_key'] as $field) {
            if (!array_key_exists($field, $payload)) {
                throw new InvalidArgumentException('Payload Midtrans tidak lengkap.');
            }
        }

        $grossAmount = is_scalar($payload['gross_amount'])
            ? (string) $payload['gross_amount']
            : json_encode($payload['gross_amount']);

        $expectedSignature = hash(
            'sha512',
            (string) $payload['order_id'] . (string) $payload['status_code'] . $grossAmount . $serverKey
        );

        return hash_equals($expectedSignature, (string) $payload['signature_key']);
    }

    public function syncWebhook(array $payload): ?PaymentOrder
    {
        if (!$this->verifyWebhookSignature($payload)) {
            throw new InvalidArgumentException('Signature Midtrans tidak valid.');
        }

        $orderId = (string) $payload['order_id'];
        $statusResponse = $this->fetchTransactionStatus($orderId);

        return $this->syncOrderFromResponse($statusResponse, $payload);
    }

    public function syncByOrderId(string $orderId): ?PaymentOrder
    {
        return $this->syncByReference($orderId);
    }

    public function syncByReference(string $reference): ?PaymentOrder
    {
        $reference = trim($reference);

        if ($reference === '') {
            throw new InvalidArgumentException('Order Midtrans tidak valid.');
        }

        $order = PaymentOrder::where('midtrans_order_id', $reference)
            ->orWhere('order_code', $reference)
            ->first();

        $lookupId = $order?->midtrans_order_id ?: $reference;
        $statusResponse = $this->fetchTransactionStatus($lookupId);

        return $this->syncOrderFromResponse($statusResponse);
    }

    private function fetchTransactionStatus(string $orderId): object
    {
        $this->configureSdk();

        return Transaction::status($orderId);
    }

    private function syncOrderFromResponse(object $statusResponse, array $notificationPayload = []): ?PaymentOrder
    {
        $midtransOrderId = (string) ($statusResponse->order_id ?? '');

        if ($midtransOrderId === '') {
            return null;
        }

        $paymentStatus = $this->mapPaymentStatus(
            strtolower((string) ($statusResponse->transaction_status ?? '')),
            strtolower((string) ($statusResponse->fraud_status ?? ''))
        );

        return DB::transaction(function () use ($statusResponse, $notificationPayload, $midtransOrderId, $paymentStatus) {
            $order = PaymentOrder::where('midtrans_order_id', $midtransOrderId)
                ->orWhere('order_code', $midtransOrderId)
                ->lockForUpdate()
                ->first();

            if (!$order) {
                return null;
            }

            $previousStatus = strtolower((string) ($order->payment_status ?? ''));
            $statusResponseData = $this->responseToArray($statusResponse);

            $updateData = [
                'payment_status' => $paymentStatus,
                'midtrans_transaction_status' => $statusResponse->transaction_status ?? null,
                'midtrans_payment_type' => $statusResponse->payment_type ?? null,
                'midtrans_transaction_id' => $statusResponse->transaction_id ?? null,
                'midtrans_fraud_status' => $statusResponse->fraud_status ?? null,
                'midtrans_response' => $notificationPayload !== []
                    ? [
                        'notification' => $notificationPayload,
                        'status' => $statusResponseData,
                    ]
                    : [
                        'status' => $statusResponseData,
                    ],
            ];

            if ($paymentStatus === 'paid' && $order->paid_at === null) {
                $updateData['paid_at'] = now();
            }

            if ($paymentStatus === 'expired' && $order->expired_at === null) {
                $updateData['expired_at'] = now();
            }

            $shouldProcessStock = $paymentStatus === 'paid'
                && $previousStatus !== 'paid'
                && $order->stock_processed_at === null;

            if ($shouldProcessStock) {
                $this->processStockForPaidOrder($order);
                $updateData['stock_processed_at'] = now();
            }

            $order->update($updateData);

            return $order->fresh();
        });
    }

    private function processStockForPaidOrder(PaymentOrder $order): void
    {
        $order->loadMissing('items');

        $qtyByProduct = [];

        foreach ($order->items as $item) {
            $productId = trim((string) ($item->product_id ?? ''));

            if ($productId === '') {
                continue;
            }

            $qtyByProduct[$productId] = ($qtyByProduct[$productId] ?? 0) + (int) $item->quantity;
        }

        if ($qtyByProduct === []) {
            return;
        }

        foreach ($qtyByProduct as $productId => $qty) {
            if ($qty <= 0) {
                continue;
            }

            $productStore = ProductStore::where('product_id', $productId)
                ->where('store_id', (string) $order->outlet_id)
                ->lockForUpdate()
                ->first();

            if (!$productStore) {
                $productStore = ProductStore::create([
                    'product_id' => $productId,
                    'store_id' => (string) $order->outlet_id,
                    'stok' => 0,
                    'status_aktif' => true,
                ]);
            }

            $productStore->decrement('stok', $qty);

            StockCard::create([
                'product_id' => $productId,
                'store_id' => (string) $order->outlet_id,
                'jmlh' => -$qty,
                'keterangan' => 'Pengurangan stok otomatis dari pembayaran online order ' . $order->order_code,
            ]);
        }
    }

    private function mapPaymentStatus(string $transactionStatus, string $fraudStatus): string
    {
        if ($transactionStatus === 'capture') {
            if ($fraudStatus === 'challenge') {
                return 'challenge';
            }

            return 'paid';
        }

        if ($transactionStatus === 'settlement') {
            return 'paid';
        }

        if ($transactionStatus === 'pending') {
            return 'pending';
        }

        if ($transactionStatus === 'deny') {
            return 'denied';
        }

        if ($transactionStatus === 'cancel') {
            return 'canceled';
        }

        if ($transactionStatus === 'expire') {
            return 'expired';
        }

        if ($transactionStatus === 'refund' || $transactionStatus === 'partial_refund') {
            return 'refunded';
        }

        return 'failed';
    }

    private function configureSdk(): void
    {
        $serverKey = (string) config('services.midtrans.server_key', '');

        if ($serverKey === '') {
            throw new InvalidArgumentException('Midtrans server key belum dikonfigurasi.');
        }

        Config::$serverKey = $serverKey;
        Config::$isProduction = (bool) config('services.midtrans.is_production', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    private function responseToArray(object $response): array
    {
        return json_decode(json_encode($response), true) ?: [];
    }
}
