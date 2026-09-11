<?php

namespace App\Services\Shipping\Drivers;

use App\Services\Shipping\Contracts\ShippingGatewayContract;
use App\Services\Shipping\DTOs\ShippingRateData;

class MockShippingDriver implements ShippingGatewayContract
{
    public function calculateRates(
        string $originCityId,
        string $destinationCityId,
        int $totalWeightInGrams,
        string $courierCode
    ): array {
        // Simulasi hitung berat (minimal 1000 gr / 1 kg)
        $weightMultiplier = (int) max(1, ceil($totalWeightInGrams / 1000));

        $mockServices = [
            'jne' => [
                new ShippingRateData('jne', 'JNE Express', 'REG', 'Reguler', 10000 * $weightMultiplier, '2-3 Hari'),
                new ShippingRateData('jne', 'JNE Express', 'YES', 'Yakin Esok Sampai', 18000 * $weightMultiplier, '1 Hari'),
            ],
            'jnt' => [
                new ShippingRateData('jnt', 'J&T Express', 'EZ', 'EZ Service', 11000 * $weightMultiplier, '2-3 Hari'),
            ],
            'sicepat' => [
                new ShippingRateData('sicepat', 'SiCepat Express', 'REG', 'Reguler', 9500 * $weightMultiplier, '1-2 Hari'),
            ],
        ];

        return $mockServices[strtolower($courierCode)] ?? [];
    }

    public function getDriverName(): string
    {
        return 'mock';
    }
}