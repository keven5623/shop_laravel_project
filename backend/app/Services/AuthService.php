<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthService
{
    protected UserRepository $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function register(array $data): array
    {
        $user = $this->userRepo->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'message' => '註冊成功',
            'user' => $user,
            'token' => $token,
        ];
    }

    public function login(string $email, string $password): ?array
    {
        if (!Auth::attempt(compact('email', 'password'))) {
            return null;
        }

        $user = Auth::user();

        $minutes = (int) env('TOKEN_EXPIRY_MINUTES', 60);

        $token = $user->createToken('auth_token');
        $token->accessToken->expires_at = now()->addMinutes($minutes);
        $token->accessToken->save();

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
            ],
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $token->accessToken->expires_at->toDateTimeString(),
        ];
    }

    public function logout(User $user): void
    {
        $user->tokens()->delete();
    }
}
