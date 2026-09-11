<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'catalogue_id' => $this->catalogue_id,
            'title'        => $this->title,        // snapshot saat checkout
            'price'        => $this->price,         // snapshot saat checkout
            'quantity'     => $this->quantity,
            'subtotal'     => $this->subtotal,

            // Relasi catalogue hanya jika di-load
            'catalogue'    => new CatalogueResource(
                                $this->whenLoaded('catalogue')
                              ),
        ];
    }
}