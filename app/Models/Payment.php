<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasUuids;

    protected $fillable = [
        'order_id','payment_number','gateway','gateway_transaction_id',
        'method','amount','currency','status','gateway_response',
        'paid_at','expired_at',
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'paid_at'          => 'datetime',
        'expired_at'       => 'datetime',
    ];

    public function order() { return $this->belongsTo(Order::class); }
}