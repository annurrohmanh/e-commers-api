<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'catalogue_id'   => $this->catalogue_id,
            'catalogue_title'=> $this->whenLoaded('catalogue', fn() => $this->catalogue->title),
            'quantity'       => $this->quantity,
            'price_snapshot' => $this->price_snapshot,
            'subtotal'       => $this->price_snapshot * $this->quantity,
        ];
    }
}