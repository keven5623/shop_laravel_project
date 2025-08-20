<?php 

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Chat",
 *     description="聊天室相關 API"
 * )
 */
class ChatController extends Controller
{
    private ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * 取得其他會員列表
     * 
     * @OA\Get(
     *     path="/api/chat/users",
     *     summary="取得其他會員列表",
     *     tags={"Chat"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="成功取得會員列表",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="name", type="string")
     *             )
     *         )
     *     )
     * )
     */
    public function getUsers()
    {
        return $this->chatService->getUsers(Auth::id());
    }

    /**
     * 取得或建立私聊聊天室
     * 
     * @OA\Post(
     *     path="/api/chat/room",
     *     summary="取得或建立聊天室",
     *     tags={"Chat"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id"},
     *             @OA\Property(property="user_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功取得或建立聊天室",
     *         @OA\JsonContent(
     *             @OA\Property(property="room_id", type="integer"),
     *             @OA\Property(property="users", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function getOrCreateRoom(Request $request)
    {
        return $this->chatService->getOrCreateRoom(Auth::id(), $request->input('user_id'));
    }

    /**
     * 取得聊天室歷史訊息
     * 
     * @OA\Get(
     *     path="/api/chat/rooms/{roomId}/messages",
     *     summary="取得聊天室歷史訊息",
     *     tags={"Chat"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="roomId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功取得訊息",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="user_id", type="integer"),
     *                 @OA\Property(property="message", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time")
     *             )
     *         )
     *     )
     * )
     */
    public function getMessages($roomId)
    {
        return $this->chatService->getMessages(Auth::id(), $roomId);
    }

    /**
     * 發送訊息
     * 
     * @OA\Post(
     *     path="/api/chat/messages",
     *     summary="發送訊息",
     *     tags={"Chat"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"room_id","message"},
     *             @OA\Property(property="room_id", type="integer"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功發送訊息",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="room_id", type="integer"),
     *             @OA\Property(property="user_id", type="integer"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="created_at", type="string", format="date-time")
     *         )
     *     )
     * )
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:chat_rooms,id',
            'message' => 'required|string',
        ]);

        return $this->chatService->sendMessage(Auth::id(), $request->room_id, $request->message);
    }

    /**
     * 標記已讀
     * 
     * @OA\Post(
     *     path="/api/chat/rooms/{roomId}/read",
     *     summary="標記已讀",
     *     tags={"Chat"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="roomId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="成功標記已讀"
     *     )
     * )
     */
    public function markAsRead($roomId)
    {
        return $this->chatService->markAsRead(Auth::id(), $roomId);
    }

    /**
     * 取得未讀數
     * 
     * @OA\Get(
     *     path="/api/chat/unread-counts",
     *     summary="取得未讀數",
     *     tags={"Chat"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="成功取得未讀數",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="room_id", type="integer"),
     *             @OA\Property(property="count", type="integer")
     *         )
     *     )
     * )
     */
    public function unreadCounts()
    {
        return $this->chatService->unreadCounts(Auth::id());
    }
}
