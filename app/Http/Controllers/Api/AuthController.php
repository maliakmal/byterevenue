<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends ApiController
{
    public function login(Request $request): JsonResponse
    {
        $loginUserData = $request->validate([
            'email'    => 'required|string|email',
            'password' => 'required|min:6'
        ]);

        $user = User::where('email', $loginUserData['email'])->first();

        if (!$user || !Hash::check($loginUserData['password'], $user->password)) {
            return $this->responseError([], 'The provided credentials are incorrect', 401);
        }

        $token = $user->createToken($user->name .'-AuthToken')->plainTextToken;

        return $this->responseSuccess(compact('token'));
    }

    public function register(): JsonResponse
    {
        return response()->json(['message' => 'register']);
    }

    public function logout(): JsonResponse
    {
        $user = auth()->user();

        $user->tokens()->delete();

        return $this->responseSuccess([], 'Logged out successfully');
    }

    public function refresh(): JsonResponse
    {
        $user = auth()->user();

        if (!$user) {
            return $this->responseError([], 'User not found', 404);
        }

        $user->tokens()->delete();

        $token = $user->createToken($user->name .'-AuthToken')->plainTextToken;

        return $this->responseSuccess(compact('token'));
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->responseSuccess(compact('user'));
    }
}
