<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddItemCartRequest;
use App\Http\Requests\RemoveItemCartRequest;
use App\Services\CartService;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Cart",
 *     description="購物車相關 API"
 * )
 */
class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * 取得使用者購物車
     * 
     * @OA\Get(
     *     path="/api/cart",
     *     summary="取得購物車",
     *     tags={"Cart"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="成功取得購物車",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="items", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="product", type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string"),
     *                         @OA\Property(property="price", type="number")
     *                     ),
     *                     @OA\Property(property="quantity", type="integer")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getCart(Request $request)
    {
        $data = $this->cartService->getCart($request->user()->id);
        return response()->json($data);
    }

    /**
     * 加入購物車
     * 
     * @OA\Post(
     *     path="/api/cart/add",
     *     summary="加入商品到購物車",
     *     tags={"Cart"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="product_id", type="integer"),
     *             @OA\Property(property="quantity", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功加入購物車",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function addItem(AddItemCartRequest $request)
    {
        $this->cartService->addItem($request->user()->id, $request->product_id, $request->quantity);
        return response()->json(['message' => '商品已加入購物車']);
    }

    /**
     * 從購物車移除商品
     * 
     * @OA\Post(
     *     path="/api/cart/remove",
     *     summary="從購物車移除商品",
     *     tags={"Cart"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="item_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功移除商品",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="移除失敗",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
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
