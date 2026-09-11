<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CatalogueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'user_id'     => $this->user_id,
            'category_id' => $this->category_id,
            'title'       => $this->title,
            'slug'        => $this->slug,
            'weight'      => $this->weight,
            'description' => $this->description,
            'image'       => $this->image,
            'price'       => (float) $this->price,
            'stock'       => $this->stock,
            'status'      => $this->status,
            'category'    => new CategoryResource($this->whenLoaded('category')),
            'user'        => new UserResource($this->whenLoaded('user')),
        ];
    }
}