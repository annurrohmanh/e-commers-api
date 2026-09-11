<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Redis;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Ambil sisa TTL dari Redis hanya jika payment masih pending
        $remainingSeconds = null;
        if ($this->status === 'pending') {
            $ttl = Redis::ttl("payment:pending:{$this->order_id}");
            $remainingSeconds = $ttl > 0 ? $ttl : 0;
        }

        return [
            'id'                     => $this->id,
            'payment_number'         => $this->payment_number,
            'gateway'                => $this->gateway,
            'method'                 => $this->method,
            'amount'                 => $this->amount,
            'currency'               => $this->currency,
            'status'                 => $this->status,
            'expired_at'             => $this->expired_at?->toISOString(),
            'paid_at'                => $this->paid_at?->toISOString(),

            // Hanya muncul saat status pending
            'remaining_seconds'      => $remainingSeconds,

            // Hanya expose gateway_response di non-production
            'gateway_response'       => $this->when(
                                          app()->isLocal() || app()->environment('staging'),
                                          $this->gateway_response
                                        ),
        ];
    }
}