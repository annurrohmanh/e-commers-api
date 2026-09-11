<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'order_number' => $this->order_number,
            'status'       => $this->status,
            'subtotal'     => $this->subtotal,
            'tax'          => $this->tax,
            'total'        => $this->total,
            'created_at'   => $this->created_at->toISOString(),

            // Relasi — hanya muncul jika di-load
            'items'        => OrderItemResource::collection(
                                $this->whenLoaded('items')
                              ),
            'payment'      => new PaymentResource(
                                $this->whenLoaded('payment')
                              ),
        ];
    }
}