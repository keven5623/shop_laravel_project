<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;

/**
 * @OA\Tag(
 *     name="Category",
 *     description="商品分類相關 API"
 * )
 */
class CategoryController extends Controller
{
    /**
     * 取得所有分類
     * 
     * @OA\Get(
     *     path="/api/categories",
     *     summary="取得所有分類",
     *     tags={"Category"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="成功取得分類",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="沒有分類資料",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function index()
    {
        $cats = Category::all();
        if ($cats->isEmpty()) {
            return response()->json(['message' => '沒有分類資料'], 404);
        }
        return response()->json($cats);
    }
}
