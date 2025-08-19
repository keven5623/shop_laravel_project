<?php
namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderRepository
{
    public function getOrdersByUser(int $userId)
    {
        return Order::with('items.product')->where('user_id', $userId)->get();
    }

    public function getProductsForOrder(array $productIds)
    {
        return Product::whereIn('id', $productIds)
                      ->lockForUpdate()
                      ->get()
                      ->keyBy('id');
    }

    public function createOrder(int $userId, float $total = 0, string $status = 'pending'): Order
    {
        return Order::create([
            'user_id' => $userId,
            'total' => $total,
            'status' => $status,
        ]);
    }

    public function createOrderItem(int $orderId, int $productId, int $quantity, float $price)
    {
        return OrderItem::create([
            'order_id' => $orderId,
            'product_id' => $productId,
            'quantity' => $quantity,
            'price' => $price,
        ]);
    }

    public function updateOrderTotal(Order $order, float $total)
    {
        $order->total = $total;
        $order->save();
    }

    public function decreaseProductStock($product, int $quantity)
    {
        $product->stock -= $quantity;
        $product->save();
    }
}
