<?php

namespace App\Services;

use App\Repositories\ChatMessageRepository;
use App\Repositories\ChatRoomRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use App\Events\NewChatMessage;
use Illuminate\Support\Facades\Log;

class ChatService
{
    private ChatMessageRepository $chatMessageRepo;
    private ChatRoomRepository $chatRoomRepo;
    private UserRepository $userRepo;

    public function __construct(
        ChatMessageRepository $chatMessageRepo,
        ChatRoomRepository $chatRoomRepo,
        UserRepository $userRepo
    ) {
        $this->chatMessageRepo = $chatMessageRepo;
        $this->chatRoomRepo = $chatRoomRepo;
        $this->userRepo = $userRepo;
    }

    public function getUsers(int $myId)
    {
        return $this->userRepo->getOtherUsers($myId);
    }

    public function getOrCreateRoom(int $myId, int $otherId)
    {
        $room = $this->chatRoomRepo->findPrivateRoom($myId, $otherId);
        if (!$room) {
            $room = $this->chatRoomRepo->createPrivateRoom([$myId, $otherId]);
        }
        return ['room_id' => $room->id];
    }

    public function getMessages(int $userId, int $roomId)
    {
        try {
            $messages = $this->chatMessageRepo->getMessagesByRoom($roomId);
            $this->chatRoomRepo->updateLastReadAt($roomId, $userId);
            
            return response()->json($messages->map(function($msg) {
                return [
                    'id' => $msg['id'],
                    'message' => $msg['message'],
                    'room_id' => $msg['room_id'],
                    'is_read' => $msg['is_read'],
                    'created_at' => $msg['created_at'] ?? null,
                    'user' => [
                        'id' => $msg['user']['id'],
                        'name' => $msg['user']['name'],
                    ]
                ];
            }));

        } catch (\Throwable $e) {
            // 記錄錯誤到 log
            Log::error('getMessages error: '.$e->getMessage().' '.$e->getTraceAsString());

            // 回傳 JSON 錯誤訊息，避免 500 空白頁
            return response()->json([
                'error' => 'Server error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function sendMessage(int $userId, int $roomId, string $message)
    {
        $msg = $this->chatMessageRepo->createMessage($roomId, $userId, $message);

        broadcast(new NewChatMessage($msg))->toOthers();

        return [
            'id' => $msg->id,
            'message' => $msg->message,
            'room_id' => $msg->room_id,
            'is_read' => false,
            'created_at' => $msg->created_at->format('Y-m-d H:i:s'),
            'user' => [
                'id' => $msg->user->id,
                'name' => $msg->user->name,
            ]
        ];
    }

    public function markAsRead(int $userId, int $roomId)
    {
        $this->chatMessageRepo->markAsRead($roomId, $userId);
        return ['status' => 'ok'];
    }

    public function unreadCounts(int $userId)
    {
        return $this->chatMessageRepo->getUnreadCounts($userId);
    }
}
