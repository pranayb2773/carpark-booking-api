<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final readonly class RegisterUserAction
{
    public function handle(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            // Create user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'date_of_birth' => $data['date_of_birth'],
                'role' => UserRole::CUSTOMER,
            ]);

            // Log the user in
            Auth::login($user);

            // Create access token
            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'user' => $user,
                'token' => $token,
            ];
        });
    }
}
