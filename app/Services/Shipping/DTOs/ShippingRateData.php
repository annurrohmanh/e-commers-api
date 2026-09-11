<?php

namespace App\Services\Shipping\DTOs;

class ShippingRateData
{
    public function __construct(
        public string $courierCode,      // e.g., 'jne', 'jnt', 'sicepat'
        public string $courierName,      // e.g., 'JNE Express'
        public string $serviceCode,      // e.g., 'REG', 'YES'
        public string $serviceName,      // e.g., 'Reguler Service'
        public int $cost,                // Ongkir dalam IDR
        public string $estimatedDays,    // e.g., '2-3 Hari'
    ) {}
}