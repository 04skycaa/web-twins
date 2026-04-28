<?php

namespace App\Http\Controllers;

use App\Models\PaymentOrder;
use Illuminate\Http\Request;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();

        $validated = $request->validate([
            'order_id' => ['required', 'string'],
            'status_code' => ['required', 'string'],
            'gross_amount' => ['required'],
            'signature_key' => ['required', 'string'],
            'transaction_status' => ['required', 'string'],
            'payment_type' => ['nullable', 'string'],
            'transaction_id' => ['nullable', 'string'],
            'fraud_status' => ['nullable', 'string'],
        ]);

        $serverKey = (string) config('services.midtrans.server_key', '');
        if ($serverKey === '') {
            return response()->json([
                'message' => 'Midtrans server key belum dikonfigurasi.',
            ], 500);
        }

        $rawGrossAmount = is_scalar($validated['gross_amount'])
            ? (string) $validated['gross_amount']
            : json_encode($validated['gross_amount']);

        $expectedSignature = hash(
            'sha512',
            $validated['order_id'] . $validated['status_code'] . $rawGrossAmount . $serverKey
        );

        if (!hash_equals($expectedSignature, (string) $validated['signature_key'])) {
            return response()->json([
                'message' => 'Signature Midtrans tidak valid.',
            ], 403);
        }

        $order = PaymentOrder::where('midtrans_order_id', $validated['order_id'])
            ->orWhere('order_code', $validated['order_id'])
            ->first();

        if (!$order) {
            return response()->json([
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }

        $midtransStatus = strtolower((string) $validated['transaction_status']);
        $fraudStatus = strtolower((string) ($validated['fraud_status'] ?? ''));
        $paymentStatus = $this->mapPaymentStatus($midtransStatus, $fraudStatus);

        $updateData = [
            'payment_status' => $paymentStatus,
            'midtrans_transaction_status' => $validated['transaction_status'],
            'midtrans_payment_type' => $validated['payment_type'] ?? null,
            'midtrans_transaction_id' => $validated['transaction_id'] ?? null,
            'midtrans_fraud_status' => $validated['fraud_status'] ?? null,
            'midtrans_response' => $payload,
        ];

        if ($paymentStatus === 'paid') {
            $updateData['paid_at'] = now();
        }

        if ($paymentStatus === 'expired') {
            $updateData['expired_at'] = now();
        }

        $order->update($updateData);

        return response()->json([
            'message' => 'Webhook Midtrans diproses.',
            'order_code' => $order->order_code,
            'payment_status' => $paymentStatus,
        ]);
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
}
