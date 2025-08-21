<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function login_with_correct_credentials()
    {
        // 建立使用者
        $user = User::factory()->create([
            'email' => 'keven5623@gmail.com',
            'password' => bcrypt('keven3210')
        ]);

        // 送 POST 請求
        $response = $this->postJson('/api/login', [
            'email' => 'keven5623@gmail.com',
            'password' => 'keven3210',
        ]);

        // 驗證 HTTP 狀態碼
        $response->assertStatus(200);

        // 驗證回傳 JSON 包含 token
        $response->assertJsonStructure([
            'token',
            'token_type',
            'expires_in'
        ]);
    }

    /** @test */
    public function login_with_wrong_credentials()
    {
        $user = User::factory()->create([
            'email' => 'keven5623@gmail.com',
            'password' => bcrypt('keven3210')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'keven5623@gmail.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthorized'
        ]);
    }
}
