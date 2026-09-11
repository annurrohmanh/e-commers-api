<?php

namespace App\Services\Payment\DTOs;

use Carbon\Carbon;

class PaymentResult
{
    public function __construct(
        public readonly string $transactionId,
        public readonly string $redirectUrl,
        public readonly Carbon $expiredAt,
        public readonly string $method = 'mock',
    ) {}
}