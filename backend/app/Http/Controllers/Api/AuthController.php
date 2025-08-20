<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="電商平台 Auth API",
 *     description="認證相關 API 文件範例"
 * )
 *
 * @OA\Tag(
 *     name="Auth",
 *     description="使用者認證相關操作"
 * )
 */
class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="註冊新使用者",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="註冊成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string")
     *         )
     *     )
     * )
     */
    public function register(RegisterRequest $request)
    {
        $result = $this->authService->register($request->all());
        return response()->json($result, 201);
    }

    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="使用者登入",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="登入成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="bearer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="帳號或密碼錯誤"
     *     )
     * )
     */
    public function login(LoginRequest $request)
    {
        $result = $this->authService->login($request->email, $request->password);

        if (!$result) {
            return response()->json(['message' => '帳號或密碼錯誤'], 401);
        }

        return response()->json($result);
    }

    /**
     * @OA\Get(
     *     path="/api/me",
     *     summary="取得當前使用者資訊",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string")
     *         )
     *     )
     * )
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="使用者登出",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="已登出",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="已登出")
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $this->authService->logout($request->user());
        return response()->json(['message' => '已登出']);
    }
}
