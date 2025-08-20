<?php
namespace App\Repositories;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class CartRepository
{
    public function getCartByUserId(int $userId): ?Cart
    {
        $cacheKey = 'cart_user_' . $userId;

        return Cache::remember($cacheKey, 600, function () use ($userId) {
            return Cart::with('items.product')->where('user_id', $userId)->first();
        });
    }

    public function createCartIfNotExist(int $userId): Cart
    {
        return Cart::firstOrCreate(['user_id' => $userId]);
    }

    public function addItem(int $cartId, int $productId, int $quantity, int $userId): CartItem
    {
        $item = CartItem::updateOrCreate(
            ['cart_id' => $cartId, 'product_id' => $productId],
            ['quantity' => DB::raw('quantity + ' . $quantity)]
        );

        // 清除使用者購物車快取
        Cache::forget('cart_user_' . $userId);

        return $item;
    }

    public function getItemById(int $itemId): ?CartItem
    {
        return CartItem::find($itemId);
    }

    public function removeItem(CartItem $item): void
    {
        $item->delete();
        Cache::forget('cart_user_' . $item->cart->user_id);
    }
}
