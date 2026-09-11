<?php

namespace App\Services\Shipping\Contracts;

use App\Services\Shipping\DTOs\ShippingRateData;

interface ShippingGatewayContract
{
    /**
     * @return array<ShippingRateData>
     */
    public function calculateRates(
        string $originCityId,
        string $destinationCityId,
        int $totalWeightInGrams,
        string $courierCode
    ): array;

    public function getDriverName(): string;
}