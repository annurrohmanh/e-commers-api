<?php

namespace App\Services\Checkout;

use App\Models\Catalogue;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Lock & reserve stock untuk single item
     */
    public function reserve(Catalogue $catalogue, int $qty): void
    {
        $affected = DB::table('catalogue')
            ->where('id', $catalogue->id)
            ->where('status', 'active')
            ->whereRaw('(stock - COALESCE(reserved_stock, 0)) >= ?', [$qty])
            ->update([
                'reserved_stock' => DB::raw("reserved_stock + {$qty}"),
            ]);

        if (! $affected) {
            throw new \RuntimeException(
                "Stok tidak mencukupi untuk produk [{$catalogue->title}]."
            );
        }
    }

    /**
     * Reserve stock untuk multiple item (Cart Checkout)
     *
     * @param array<array{catalogue: Catalogue, qty: int}> $items
     */
    public function reserveBatch(array $items): void
    {
        foreach ($items as $item) {
            $this->reserve($item['catalogue'], $item['qty']);
        }
    }

    /**
     * Confirm sale untuk multiple items (Payment SUCCESS)
     *
     * @param array<array{catalogue: Catalogue, qty: int}> $items
     */
    public function confirmSaleBatch(array $items): void
    {
        foreach ($items as $item) {
            $this->confirmSale($item['catalogue'], $item['qty']);
        }
    }

    /**
     * Release reserved stock untuk multiple items (Payment FAILED/EXPIRED)
     *
     * @param array<array{catalogue: Catalogue, qty: int}> $items
     */
    public function releaseBatch(array $items): void
    {
        foreach ($items as $item) {
            $this->release($item['catalogue'], $item['qty']);
        }
    }

    public function confirmSale(Catalogue $catalogue, int $qty): void
    {
        DB::table('catalogue')
            ->where('id', $catalogue->id)
            ->update([
                'stock'          => DB::raw("stock - {$qty}"),
                'reserved_stock' => DB::raw("reserved_stock - {$qty}"),
            ]);
    }

    public function release(Catalogue $catalogue, int $qty): void
    {
        DB::table('catalogue')
            ->where('id', $catalogue->id)
            ->update([
                'reserved_stock' => DB::raw("reserved_stock - {$qty}"),
            ]);
    }
}