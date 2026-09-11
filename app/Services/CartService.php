<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Catalogue;
use Illuminate\Support\Facades\DB;

class CartService
{
    /**
     * Ambil cart active user, buat jika belum ada.
     */
    public function getOrCreateActiveCart(string $userId): Cart
    {
        return Cart::firstOrCreate(
            ['user_id' => $userId, 'status' => 'active'],
        );
    }

    /**
     * Tambah item ke cart.
     * 
     * Validasi: (stock - reserved_stock) >= quantity yang diminta.
     * Cart tidak melakukan reserve — hanya "wish list" sebelum checkout.
     * Reserve terjadi di CheckoutService → StockService::reserve().
     */
    public function addItem(Cart $cart, string $catalogueId, int $quantity): CartItem
    {
        // Lock for update supaya tidak race condition saat banyak user cek stok bersamaan
        $catalogue = Catalogue::lockForUpdate()->findOrFail($catalogueId);

        $availableStock = $catalogue->stock - $catalogue->reserved_stock;

        if ($availableStock < $quantity) {
            throw new \RuntimeException(
                "Stok tidak mencukupi untuk [{$catalogue->title}]. " .
                "Tersedia: {$availableStock}."
            );
        }

        $existingItem = $cart->items()
            ->where('catalogue_id', $catalogueId)
            ->first();

        if ($existingItem) {
            // Cek juga total quantity setelah ditambah
            $newQty = $existingItem->quantity + $quantity;

            if ($availableStock < $newQty) {
                throw new \RuntimeException(
                    "Stok tidak mencukupi untuk [{$catalogue->title}]. " .
                    "Tersedia: {$availableStock}, di cart sudah ada: {$existingItem->quantity}."
                );
            }

            $existingItem->update(['quantity' => $newQty]);
            return $existingItem->fresh();
        }

        return $cart->items()->create([
            'catalogue_id'   => $catalogue->id,
            'quantity'       => $quantity,
            'price_snapshot' => $catalogue->price, // snapshot harga saat item masuk cart
        ]);
    }

    /**
     * Update quantity item di cart.
     * Jika quantity 0, hapus item.
     */
    public function updateItem(CartItem $cartItem, int $quantity): ?CartItem
    {
        if ($quantity === 0) {
            $cartItem->delete();
            return null;
        }

        // Cek ketersediaan stok untuk quantity baru
        $catalogue = Catalogue::lockForUpdate()->findOrFail($cartItem->catalogue_id);
        $availableStock = $catalogue->stock - $catalogue->reserved_stock;

        if ($availableStock < $quantity) {
            throw new \RuntimeException(
                "Stok tidak mencukupi untuk [{$catalogue->title}]. " .
                "Tersedia: {$availableStock}."
            );
        }

        $cartItem->update(['quantity' => $quantity]);
        return $cartItem->fresh();
    }

    /**
     * Hapus satu item dari cart.
     */
    public function removeItem(CartItem $cartItem): void
    {
        $cartItem->delete();
    }

    /**
     * Kosongkan semua item di cart.
     */
    public function clearCart(Cart $cart): void
    {
        $cart->items()->delete();
    }
}