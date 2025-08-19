<?php
namespace App\Repositories;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\DB;

class CartRepository
{
    public function getCartByUserId(int $userId): ?Cart
    {
        return Cart::with('items.product')->where('user_id', $userId)->first();
    }

    public function createCartIfNotExist(int $userId): Cart
    {
        return Cart::firstOrCreate(['user_id' => $userId]);
    }

    public function addItem(int $cartId, int $productId, int $quantity): CartItem
    {
        return CartItem::updateOrCreate(
            ['cart_id' => $cartId, 'product_id' => $productId],
            ['quantity' => DB::raw('quantity + ' . $quantity)]
        );
    }

    public function getItemById(int $itemId): ?CartItem
    {
        return CartItem::find($itemId);
    }

    public function removeItem(CartItem $item): void
    {
        $item->delete();
    }
}
