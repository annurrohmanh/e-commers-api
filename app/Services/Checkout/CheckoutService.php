<?php

namespace App\Services\Checkout;

use App\Jobs\ExpirePaymentJob;
use App\Models\Cart;
use App\Models\Catalogue;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payment\Contracts\PaymentGatewayContract;
use App\Services\Shipping\Contracts\ShippingGatewayContract;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class CheckoutService
{
    public const PAYMENT_TTL = 15 * 60; // 15 menit

    public function __construct(
        private PaymentGatewayContract $gateway,
        private ShippingGatewayContract $shippingGateway,
        private StockService $stock,
    ) {}

    /**
     * @param array{
     *     user_id: string,
     *     catalogue_id?: string,
     *     quantity?: int,
     *     cart_id?: string,
     *     destination_city_id: string,
     *     courier_code: string,
     *     service_code: string
     * } $payload
     */
    public function checkout(array $payload): array
    {
        return DB::transaction(function () use ($payload) {
            // 1. Resolve Items (Baik dari Cart maupun Single Catalogue)
            [$itemsData, $cartModel] = $this->resolveCheckoutItems($payload);

            // 2. Lock & Reserve Stock
            $subtotal = 0;
            $totalWeight = 0;
            $reserveBatchPayload = [];

            foreach ($itemsData as $item) {
                // Lock catalogue row
                $catalogue = Catalogue::lockForUpdate()->findOrFail($item['catalogue_id']);

                $subtotal += $item['price'] * $item['quantity'];
                $totalWeight += ($item['weight'] ?? 1000) * $item['quantity']; // Fallback 1000gr jika null

                $reserveBatchPayload[] = [
                    'catalogue' => $catalogue,
                    'qty'       => $item['quantity'],
                ];
            }

            // Reserve stok di DB secara atomic
            $this->stock->reserveBatch($reserveBatchPayload);

            // 3. Hitung Ongkir dari Shipping Gateway
            $shippingCost = $this->calculateShippingCost(
                destinationCityId: $payload['destination_city_id'],
                totalWeight: $totalWeight,
                courierCode: $payload['courier_code'],
                serviceCode: $payload['service_code']
            );

            $grandTotal = $subtotal + $shippingCost;

            // 4. Buat Order Header
            $order = Order::create([
                'id'                  => Str::uuid(),
                'user_id'             => $payload['user_id'],
                'order_number'        => $this->generateOrderNumber(),
                'subtotal'            => $subtotal,
                'shipping_cost'       => $shippingCost,
                'courier_code'        => $payload['courier_code'],
                'courier_service'     => $payload['service_code'],
                'destination_city_id' => $payload['destination_city_id'],
                'tax'                 => 0,
                'total'               => $grandTotal,
                'status'              => 'pending',
            ]);

            // 5. Buat Order Items
            foreach ($itemsData as $item) {
                $order->items()->create([
                    'id'           => Str::uuid(),
                    'catalogue_id' => $item['catalogue_id'],
                    'title'        => $item['title'],
                    'price'        => $item['price'],
                    'quantity'     => $item['quantity'],
                    'subtotal'     => $item['price'] * $item['quantity'],
                ]);
            }

            // 6. Hapus Cart jika checkout menggunakan Cart
            if ($cartModel) {
                $cartModel->items()->delete();
                $cartModel->delete();
            }

            // 7. Hit payment gateway
            $paymentResult = $this->gateway->createPayment($order);

            // 8. Simpan record Payment
            $payment = Payment::create([
                'id'                     => Str::uuid(),
                'order_id'               => $order->id,
                'payment_number'         => 'PAY-' . strtoupper(Str::random(10)),
                'gateway'                => $this->gateway->getDriverName(),
                'gateway_transaction_id' => $paymentResult->transactionId,
                'method'                 => $paymentResult->method,
                'amount'                 => $order->total,
                'status'                 => 'pending',
                'expired_at'             => $paymentResult->expiredAt,
            ]);

            // 9. Simpan TTL di Redis
            $this->storePaymentTtl($order->id, $payment->id);

            // 10. Dispatch job expire
            ExpirePaymentJob::dispatch($order->id)
                ->delay(now()->addSeconds(self::PAYMENT_TTL));

            return [
                'redirect_url' => $paymentResult->redirectUrl,
                'order'        => $order,
                'payment'      => $payment,
                'expired_at'   => $paymentResult->expiredAt,
            ];
        });
    }

    /**
     * Memproses item baik dari Cart maupun Catalogue Direct
     */
    private function resolveCheckoutItems(array $payload): array
    {
        $itemsData = [];
        $cartModel = null;

        if (! empty($payload['cart_id'])) {
            $cartModel = Cart::with('items.catalogue')
                ->where('id', $payload['cart_id'])
                ->where('user_id', $payload['user_id'])
                ->firstOrFail();

            if ($cartModel->items->isEmpty()) {
                throw new \RuntimeException('Keranjang belanja Anda kosong.');
            }

            foreach ($cartModel->items as $cartItem) {
                $itemsData[] = [
                    'catalogue_id' => $cartItem->catalogue_id,
                    'title'        => $cartItem->catalogue->title,
                    'price'        => $cartItem->catalogue->price,
                    'weight'       => $cartItem->catalogue->weight ?? 1000,
                    'quantity'     => $cartItem->quantity,
                ];
            }
        } elseif (! empty($payload['catalogue_id'])) {
            $catalogue = Catalogue::findOrFail($payload['catalogue_id']);

            $itemsData[] = [
                'catalogue_id' => $catalogue->id,
                'title'        => $catalogue->title,
                'price'        => $catalogue->price,
                'weight'       => $catalogue->weight ?? 1000,
                'quantity'     => $payload['quantity'] ?? 1,
            ];
        } else {
            throw new \InvalidArgumentException('Harap sertakan catalogue_id atau cart_id.');
        }

        return [$itemsData, $cartModel];
    }

    private function calculateShippingCost(
        string $destinationCityId,
        int $totalWeight,
        string $courierCode,
        string $serviceCode
    ): int {
        $originCityId = '39'; // ID Kota asal pengiriman (bisa ditaruh di config)

        $rates = $this->shippingGateway->calculateRates(
            originCityId: $originCityId,
            destinationCityId: $destinationCityId,
            totalWeightInGrams: $totalWeight,
            courierCode: $courierCode
        );

        foreach ($rates as $rate) {
            if (strtolower($rate->serviceCode) === strtolower($serviceCode)) {
                return $rate->cost;
            }
        }

        throw new \RuntimeException("Layanan ekspedisi [{$serviceCode}] tidak ditemukan.");
    }

    private function storePaymentTtl(string $orderId, string $paymentId): void
    {
        $key = "payment:pending:{$orderId}";

        // Cukup simpan metadata dasar payment session
        Redis::setex($key, self::PAYMENT_TTL, json_encode([
            'order_id'   => $orderId,
            'payment_id' => $paymentId,
            'expired_at' => now()->addSeconds(self::PAYMENT_TTL)->toISOString(),
        ]));
    }

    private function generateOrderNumber(): string
    {
        return 'INV-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
    }
}