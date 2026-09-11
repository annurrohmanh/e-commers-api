<?php

namespace App\Services\Payment\Contracts;

use App\Models\Order;
use App\Services\Payment\DTOs\PaymentResult;
use App\Services\Payment\DTOs\WebhookResult;
use Illuminate\Http\Request;

interface PaymentGatewayContract
{
    public function createPayment(Order $order): PaymentResult;
    public function handleWebhook(Request $request): WebhookResult;
    public function getDriverName(): string;
}