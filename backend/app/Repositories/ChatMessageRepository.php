<?php

namespace App\Repositories;

use App\Models\ChatMessage;
use Illuminate\Support\Facades\DB;

class ChatMessageRepository
{
    public function getMessagesByRoom(int $roomId)
    {
        return ChatMessage::where('room_id', $roomId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($msg) => [
                'id' => $msg->id,
                'message' => $msg->message,
                'room_id' => $msg->room_id,
                'is_read' => $msg->is_read ?? false,
                'created_at' => $msg->created_at ? $msg->created_at->format('Y-m-d H:i:s') : null,
                'user' => [
                    'id' => $msg->user_id,
                    'name' => $msg->user->name ?? '未知',
                ],
            ]);
    }

    public function createMessage(int $roomId, int $userId, string $message): ChatMessage
    {
        $msg = ChatMessage::create([
            'room_id' => $roomId,
            'user_id' => $userId,
            'message' => $message,
        ]);

        $msg->load('user'); // 預加載 user 關聯
        return $msg;
    }

    public function markAsRead(int $roomId, int $userId)
    {
        return DB::table('chat_messages')
            ->where('room_id', $roomId)
            ->where('user_id', '!=', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    public function getUnreadCounts(int $userId)
    {
        $counts = DB::table('chat_messages')
            ->select('chat_rooms.id as room_id', DB::raw('COUNT(*) as unread_count'), 'chat_messages.user_id as sender_id')
            ->join('chat_rooms', 'chat_messages.room_id', '=', 'chat_rooms.id')
            ->where('chat_messages.user_id', '!=', $userId)
            ->where('chat_messages.is_read', false)
            ->groupBy('chat_messages.user_id', 'chat_rooms.id')
            ->get();

        $result = [];
        foreach ($counts as $c) {
            $result[$c->sender_id] = $c->unread_count;
        }
        return $result;
    }
}
