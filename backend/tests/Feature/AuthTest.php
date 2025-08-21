<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can login with correct credentials', function () {
    $user = User::factory()->create([
        'email' => 'keven5623@gmail.com',
        'password' => bcrypt('keven3210')
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'keven5623@gmail.com',
        'password' => 'keven3210',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'token',
        'token_type',
        'expires_in'
    ]);
});

it('cannot login with wrong credentials', function () {
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
});
