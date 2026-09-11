<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MockPaymentActionRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Order;
use App\Services\Checkout\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class MockPaymentController extends Controller
{
    public function __construct(private readonly StockService $stockService) {}

    public function show(MockPaymentActionRequest $request, string $orderId): JsonResponse
    {
        $order = Order::with(['items.catalogue', 'payment'])
            ->where('status', 'pending')
            ->findOrFail($orderId);

        $ttlKey    = "payment:pending:{$orderId}";
        $ttlData   = Redis::get($ttlKey);
        $remaining = $ttlData ? Redis::ttl($ttlKey) : 0;

        return ApiResponse::success([
            'order'             => $order,
            'payment'           => $order->payment,
            'remaining_seconds' => $remaining,
            'expired_at'        => $order->payment->expired_at,
        ]);
    }

    public function approve(MockPaymentActionRequest $request, string $orderId): JsonResponse
    {
        return $this->processPayment($orderId, 'success');
    }

    public function reject(MockPaymentActionRequest $request, string $orderId): JsonResponse
    {
        return $this->processPayment($orderId, 'failed');
    }

    private function processPayment(string $orderId, string $action): JsonResponse
    {
        $key  = "payment:pending:{$orderId}";
        $data = Redis::get($key);

        if (! $data) {
            return ApiResponse::error('Sesi pembayaran sudah berakhir atau tidak ditemukan.', 404);
        }

        DB::transaction(function () use ($orderId, $action, $key) {
            // Load order beserta relasi items dan catalogue-nya
            $order = Order::with(['items.catalogue'])
                ->lockForUpdate()
                ->where('status', 'pending')
                ->findOrFail($orderId);

            // Siapkan susunan item untuk Batch Stock Update
            $itemsPayload = [];
            foreach ($order->items as $item) {
                if ($item->catalogue) {
                    $itemsPayload[] = [
                        'catalogue' => $item->catalogue,
                        'qty'       => $item->quantity,
                    ];
                }
            }

            if ($action === 'success') {
                $order->update(['status' => 'paid']);
                $order->payment()->update([
                    'status'  => 'success',
                    'paid_at' => now(),
                ]);

                // Potong stock fisik & kurangi reserved_stock untuk seluruh item
                $this->stockService->confirmSaleBatch($itemsPayload);
            } else {
                $order->update(['status' => 'cancelled']);
                $order->payment()->update(['status' => 'failed']);

                // Kembalikan reserved_stock untuk seluruh item
                $this->stockService->releaseBatch($itemsPayload);
            }

            // Hapus Redis key
            Redis::del($key);
        });

        $message = $action === 'success'
            ? 'Pembayaran berhasil.'
            : 'Pembayaran gagal, stok dikembalikan.';

        return ApiResponse::success(null, $message);
    }
}