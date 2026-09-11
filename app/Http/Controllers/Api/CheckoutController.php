<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Http\Responses\ApiResponse;
use App\Services\Checkout\CheckoutService;
use Illuminate\Http\JsonResponse;

class CheckoutController extends Controller
{
    public function __construct(private readonly CheckoutService $checkoutService) {}

    public function store(CheckoutRequest $request): JsonResponse
    {
        try {
            $result = $this->checkoutService->checkout(array_merge(
                $request->validated(),
                ['user_id' => auth()->id()]
            ));

            return ApiResponse::success([
                'redirect_url'   => $result['redirect_url'],
                'order_number'   => $result['order']->order_number,
                'payment_number' => $result['payment']->payment_number,
                'expired_at'     => $result['expired_at'],
            ], 'Checkout berhasil, silahkan selesaikan pembayaran.', 201);

        } catch (\InvalidArgumentException|\RuntimeException $e) {
            return ApiResponse::error($e->getMessage(), 422);
        }
    }
}