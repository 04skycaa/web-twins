<?php

namespace App\Http\Controllers;

use App\Services\MidtransPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class MidtransWebhookController extends Controller
{
    public function __construct(private readonly MidtransPaymentService $midtransPaymentService)
    {
    }

    public function handle(Request $request)
    {
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

        try {
            $order = $this->midtransPaymentService->syncWebhook($validated);
        } catch (InvalidArgumentException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 403);
        } catch (\Throwable $exception) {
            Log::error('Gagal memproses webhook Midtrans.', [
                'exception' => $exception,
                'payload' => $validated,
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses webhook Midtrans.',
            ], 500);
        }

        if (!$order) {
            return response()->json([
                'message' => 'Order tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'message' => 'Webhook Midtrans diproses.',
            'order_code' => $order->order_code,
            'payment_status' => $order->payment_status,
        ]);
    }
}
