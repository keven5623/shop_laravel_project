<?php 

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    private ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    // 取得其他會員列表
    public function getUsers()
    {
        return $this->chatService->getUsers(Auth::id());
    }

    // 取得或建立私聊聊天室
    public function getOrCreateRoom(Request $request)
    {
        return $this->chatService->getOrCreateRoom(Auth::id(), $request->input('user_id'));
    }

    // 取得聊天室歷史訊息
    public function getMessages($roomId)
    {
        return $this->chatService->getMessages(Auth::id(), $roomId);
    }

    // 發送訊息
    public function sendMessage(Request $request)
    {
        $request->validate([
            'room_id' => 'required|exists:chat_rooms,id',
            'message' => 'required|string',
        ]);

        return $this->chatService->sendMessage(Auth::id(), $request->room_id, $request->message);
    }

    // 標記已讀
    public function markAsRead($roomId)
    {
        return $this->chatService->markAsRead(Auth::id(), $roomId);
    }

    // 取得未讀數
    public function unreadCounts()
    {
        return $this->chatService->unreadCounts(Auth::id());
    }
}
