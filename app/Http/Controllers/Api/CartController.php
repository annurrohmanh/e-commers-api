<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Http\Resources\CartResource;
use App\Http\Resources\CartItemResource;
use App\Http\Responses\ApiResponse;
use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(protected CartService $cartService) {}

    /**
     * Lihat cart aktif beserta item-itemnya.
     */
    public function show(Request $request): JsonResponse
    {
        $cart = $this->cartService->getOrCreateActiveCart($request->user()->id);

        return ApiResponse::success(
            new CartResource($cart->load('items.catalogue'))
        );
    }

    /**
     * Tambah item ke cart.
     */
    public function addItem(AddCartItemRequest $request): JsonResponse
    {
        $cart = $this->cartService->getOrCreateActiveCart($request->user()->id);

        $item = $this->cartService->addItem(
            $cart,
            $request->catalogue_id,
            $request->quantity,
        );

        return ApiResponse::success(
            new CartItemResource($item->load('catalogue')),
            'Item berhasil ditambahkan ke cart.',
            201
        );
    }

    /**
     * Update quantity item di cart.
     */
    public function updateItem(UpdateCartItemRequest $request, CartItem $cartItem): JsonResponse
    {
        $this->authorizeCartItem($request, $cartItem);

        $item = $this->cartService->updateItem($cartItem, $request->quantity);

        if (!$item) {
            return ApiResponse::success(null, 'Item dihapus dari cart.');
        }

        return ApiResponse::success(
            new CartItemResource($item->load('catalogue')),
            'Item berhasil diupdate.'
        );
    }

    /**
     * Hapus satu item dari cart.
     */
    public function removeItem(Request $request, CartItem $cartItem): JsonResponse
    {
        $this->authorizeCartItem($request, $cartItem);

        $this->cartService->removeItem($cartItem);

        return ApiResponse::success(null, 'Item berhasil dihapus dari cart.');
    }

    /**
     * Kosongkan semua item di cart.
     */
    public function clear(Request $request): JsonResponse
    {
        $cart = $this->cartService->getOrCreateActiveCart($request->user()->id);

        $this->cartService->clearCart($cart);

        return ApiResponse::success(null, 'Cart berhasil dikosongkan.');
    }

    /**
     * Pastikan cart item milik user yang sedang login.
     */
    private function authorizeCartItem(Request $request, CartItem $cartItem): void
    {
        abort_if(
            $cartItem->cart->user_id !== $request->user()->id,
            403,
            'Unauthorized.'
        );
    }
}