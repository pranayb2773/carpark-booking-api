<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1;

use App\Actions\LoginUserAction;
use App\Actions\RegisterUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\v1\LoginRequest;
use App\Http\Requests\v1\RegisterRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class AuthController extends Controller
{
    public function login(LoginRequest $request, LoginUserAction $loginUserAction): JsonResponse
    {
        $result = $loginUserAction->handle($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Login Successful',
            'data' => [
                'type' => 'Bearer',
                'token' => $result['token'],
                'user' => UserResource::make($result['user']),
            ],
        ], Response::HTTP_OK);
    }

    public function register(RegisterRequest $request, RegisterUserAction $registerUserAction): JsonResponse
    {
        $result = $registerUserAction->handle($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'type' => 'Bearer',
                'token' => $result['token'],
                'user' => UserResource::make($result['user']),
            ],
        ], Response::HTTP_CREATED);
    }

    public function logout(): JsonResponse
    {
        Auth::user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'User logged out successfully',
        ], Response::HTTP_OK);
    }

    public function profile(): JsonResponse
    {
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => UserResource::make($user),
            ],
        ], Response::HTTP_OK);
    }
}
