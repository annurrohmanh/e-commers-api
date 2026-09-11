<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'order_id','catalogue_id','title','price','quantity','subtotal',
    ];

    public function order()     { return $this->belongsTo(Order::class); }
    public function catalogue() { return $this->belongsTo(Catalogue::class); }
}