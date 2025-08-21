<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('logs in successfully with valid credentials', function () {
    User::factory()->create([
        'email' => 'keven5623@gmail.com',
        'password' => bcrypt('keven3210')
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'keven5623@gmail.com',
        'password' => 'keven3210',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'user' => [
            'id',
            'name',
        ],
        'token',
        'token_type',
        'expires_at'
    ]);
});

it('fails to login with invalid credentials', function () {
    User::factory()->create([
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
});
