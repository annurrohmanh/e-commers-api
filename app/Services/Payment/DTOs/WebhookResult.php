<?php

namespace App\Services\Payment\DTOs;

class WebhookResult
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $status, // success | failed | expired
    ) {}
}