<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserApiController extends ApiController
{
    public function update(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:180',
            'email' => 'required|string|email|max:180|unique:users,email,' . auth()->id(),
        ]);

        $user = auth()->user();

        $user->update($validated);

        return $this->responseSuccess($user, 'User updated successfully');
    }

    public function password(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string|min:4',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return $this->responseError('Current password is incorrect', 422);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        $user->tokens()->delete();

        return $this->responseSuccess(data: [
            'token' => $user->createToken('api')->plainTextToken,
        ], message: 'Password updated successfully');
    }
}
