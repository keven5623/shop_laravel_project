<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function getOtherUsers(int $myId)
    {
        return User::where('id', '!=', $myId)->get(['id', 'name']);
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }
}
