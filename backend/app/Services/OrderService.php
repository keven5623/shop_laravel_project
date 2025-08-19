<?php
namespace App\Services;

use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\DB;
use Exception;

class OrderService
{
    protected $orderRepo;

    public function __construct(OrderRepository $orderRepo)
    {
        $this->orderRepo = $orderRepo;
    }

    public function getUserOrders(int $userId)
    {
        return $this->orderRepo->getOrdersByUser($userId);
    }

    public function createOrder(int $userId, array $items)
    {
        DB::beginTransaction();
        try {
            $productIds = collect($items)->pluck('product_id')->toArray();
            $products = $this->orderRepo->getProductsForOrder($productIds);

            $total = 0;
            $order = $this->orderRepo->createOrder($userId);

            foreach ($items as $item) {
                $product = $products[$item['product_id']];

                if ($product->stock < $item['quantity']) {
                    DB::rollBack();
                    throw new Exception("商品 {$product->name} 庫存不足");
                }

                $this->orderRepo->decreaseProductStock($product, $item['quantity']);

                $price = $product->price;
                $total += $price * $item['quantity'];

                $this->orderRepo->createOrderItem($order->id, $product->id, $item['quantity'], $price);
            }

            $this->orderRepo->updateOrderTotal($order, $total);

            DB::commit();

            return $order->load('items.product');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
