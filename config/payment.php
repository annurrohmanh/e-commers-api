<?php

return [
    'driver' => env('PAYMENT_DRIVER', 'mock'),
    'ttl'    => env('PAYMENT_TTL', 900), // 15 menit
];