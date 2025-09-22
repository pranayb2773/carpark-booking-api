<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('a user can register for an account', function () {
    $response = $this->postJson(route('api.v1.register'), [
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'date_of_birth' => '1990-01-01',
    ]);

    $response->assertStatus(201)->assertJsonStructure([
        'success',
        'message',
        'data' => [
            'token',
            'user' => [],
        ],
    ]);
    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
});

test('a user can login for an account', function () {
    $user = User::factory()->create(['password' => Hash::make('password')]);

    $response = $this->postJson(route('api.v1.login'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertStatus(200)->assertJsonStructure([
        'success',
        'message',
        'data' => [
            'token',
        ],
    ]);
});

test('an authenticated user can logout', function () {
    $user = User::factory()->create();

    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
        ->postJson(route('api.v1.logout'));

    $response->assertStatus(200);
    $this->assertCount(0, $user->tokens);
});
