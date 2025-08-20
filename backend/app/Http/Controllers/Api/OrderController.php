<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\Request;
use App\Jobs\ProcessOrder;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * 取得使用者所有訂單
     */
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => '未登入'], 401);
        }

        // 取得訂單並格式化
        $orders = $this->orderService->getUserOrders($user->id)->map(function($order){
            return [
                'id' => $order->id,
                'status' => $order->status,
                'total' => $order->total,
                'items' => $order->items->map(function($item){
                    return [
                        'product' => [
                            'id' => $item->product->id,
                            'name' => $item->product->name,
                            'price' => $item->product->price,
                        ],
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                    ];
                }),
            ];
        });

        return response()->json(['orders' => $orders]);
    }

    /**
     * 建立訂單
     */
    public function store(StoreOrderRequest $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => '未登入'], 401);
        }

        try {
            // 1️⃣ 建立訂單
            $order = $this->orderService->createOrder($user->id, $request->items);
            $order->load('items.product'); // 確保關聯載入

            // 2️⃣ Log 確認事件觸發
            Log::info('OrderProcessed event fired', [
                'order_id' => $order->id,
                'user_id' => $user->id
            ]);

            // 3️⃣ 觸發廣播事件
            ProcessOrder::dispatch($order->id);

            // 清空購物車
            $user->cart->items()->delete();

            // 4️⃣ 格式化回傳
            $items = $order->items->map(function($item){
                return [
                    'product' => [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'price' => $item->product->price,
                    ],
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ];
            });

            return response()->json([
                'message' => '訂單建立成功，正在處理後續任務',
                'order_id' => $order->id,
                'total' => $order->total,
                'items' => $items,
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
