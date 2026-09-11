<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasUuids;

    protected $fillable = ['cart_id', 'catalogue_id', 'quantity', 'price_snapshot'];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function catalogue()
    {
        return $this->belongsTo(Catalogue::class);
    }
}
