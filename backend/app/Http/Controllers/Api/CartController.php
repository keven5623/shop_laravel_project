<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddItemCartRequest;
use App\Http\Requests\RemoveItemCartRequest;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function getCart(Request $request)
    {
        $data = $this->cartService->getCart($request->user()->id);
        return response()->json($data);
    }

    public function addItem(AddItemCartRequest $request)
    {
        $this->cartService->addItem($request->user()->id, $request->product_id, $request->quantity);
        return response()->json(['message' => '商品已加入購物車']);
    }

    public function removeItem(RemoveItemCartRequest $request)
    {
        try {
            $this->cartService->removeItem($request->user()->id, $request->item_id);
            return response()->json(['message' => '商品已從購物車移除']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
    }
}
