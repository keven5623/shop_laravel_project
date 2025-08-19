<?php

namespace App\Repositories;

use App\Models\ChatRoom;
use App\Models\ChatRoomUser;

class ChatRoomRepository
{
    public function findPrivateRoom(int $userId, int $otherId)
    {
        return ChatRoom::whereNull('name')
            ->whereHas('users', fn($q) => $q->where('user_id', $userId))
            ->whereHas('users', fn($q) => $q->where('user_id', $otherId))
            ->first();
    }

    public function createPrivateRoom(array $userIds)
    {
        $room = ChatRoom::create(['name' => null]);
        $room->users()->attach($userIds);
        return $room;
    }

    public function updateLastReadAt(int $roomId, int $userId)
    {
        ChatRoomUser::where('chat_room_id', $roomId)
            ->where('user_id', $userId)
            ->update(['last_read_at' => now()]);
    }
}
