<?php
namespace App\Services;

use App\Repositories\CartRepository;

class CartService
{
    protected $cartRepo;

    public function __construct(CartRepository $cartRepo)
    {
        $this->cartRepo = $cartRepo;
    }

    public function getCart(int $userId): array
    {
        $cart = $this->cartRepo->getCartByUserId($userId);

        if (!$cart) return ['items' => []];

        return [
            'items' => $cart->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'price' => $item->product->price,
                    ],
                    'quantity' => $item->quantity
                ];
            })->toArray()
        ];
    }

    public function addItem(int $userId, int $productId, int $quantity)
    {
        $cart = $this->cartRepo->createCartIfNotExist($userId);
        $this->cartRepo->addItem($cart->id, $productId, $quantity, $cart->user_id);
    }

    public function removeItem(int $userId, int $itemId)
    {
        $item = $this->cartRepo->getItemById($itemId);
        if (!$item || $item->cart->user_id !== $userId) {
            throw new \Exception('無權限或項目不存在');
        }
        $this->cartRepo->removeItem($item);
    }
}
