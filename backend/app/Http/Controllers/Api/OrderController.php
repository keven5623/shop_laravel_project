<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => '未登入'], 401);
        }

        $orders = $this->orderService->getUserOrders($user->id);

        return response()->json(['orders' => $orders]);
    }

    public function store(StoreOrderRequest $request)
    {
        $user = $request->user();
        try {
            $order = $this->orderService->createOrder($user->id, $request->items);

            return response()->json([
                'message' => '訂單建立成功',
                'order_id' => $order->id,
                'total' => $order->total,
                'items' => $order->items,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
