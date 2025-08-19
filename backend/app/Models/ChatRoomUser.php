<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ChatRoomUser extends Pivot
{
    protected $table = 'chat_room_user';
    protected $fillable = ['chat_room_id', 'user_id', 'last_read_at'];
    public $timestamps = false;
}
