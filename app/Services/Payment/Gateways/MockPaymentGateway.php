<?php

namespace App\Services\Payment\Gateways;

use App\Models\Order;
use App\Services\Payment\Contracts\PaymentGatewayContract;
use App\Services\Payment\DTOs\PaymentResult;
use App\Services\Payment\DTOs\WebhookResult;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MockPaymentGateway implements PaymentGatewayContract
{
    public function createPayment(Order $order): PaymentResult
    {
        return new PaymentResult(
            transactionId : 'MOCK-' . strtoupper(Str::random(12)),
            redirectUrl   : route('mock.payment.show', $order->id),
            expiredAt     : now()->addMinutes(15),
            method        : 'mock',
        );
    }

    public function handleWebhook(Request $request): WebhookResult
    {
        return new WebhookResult(
            orderId: $request->input('order_id'),
            status : $request->input('status'), // success | failed
        );
    }

    public function getDriverName(): string
    {
        return 'mock';
    }
}