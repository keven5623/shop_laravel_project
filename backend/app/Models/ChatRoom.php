<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatRoom extends Model
{
    protected $fillable = ['name'];

    // 多對多關聯 users
    public function users()
    {
        return $this->belongsToMany(User::class, 'chat_room_user');
    }

    // 一對多關聯訊息
    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'room_id');
    }
}
