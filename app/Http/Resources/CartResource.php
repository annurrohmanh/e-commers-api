<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'     => $this->id,
            'status' => $this->status,
            'items'  => CartItemResource::collection($this->whenLoaded('items')),
            'total'  => $this->whenLoaded('items', fn() =>
                $this->items->sum(fn($item) => $item->price_snapshot * $item->quantity)
            ),
        ];
    }
}