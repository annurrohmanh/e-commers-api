<?php

namespace App\Jobs;

use App\Models\Catalogue;
use App\Models\Order;
use App\Services\Checkout\StockService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class ExpirePaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private string $orderId) {}

    public function handle(StockService $stockService): void
    {
        $key  = "payment:pending:{$this->orderId}";
        $data = Redis::get($key);

        // Jika key sudah tidak ada → payment sudah diproses (success/failed)
        if (! $data) {
            return;
        }

        $payload = json_decode($data, true);

        DB::transaction(function () use ($payload, $stockService, $key) {
            $order = Order::find($payload['order_id']);

            if (! $order || $order->status !== 'pending') {
                Redis::del($key);
                return;
            }

            // Update order & payment ke expired
            $order->update(['status' => 'expired']);
            $order->payment()->update(['status' => 'expired']);

            // Release reserved stock
            $catalogue = Catalogue::find($payload['catalogue_id']);
            if ($catalogue) {
                $stockService->release($catalogue, $payload['qty']);
            }

            // Hapus key Redis
            Redis::del($key);
        });
    }
}