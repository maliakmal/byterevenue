<?php

namespace App\Http\Controllers\Api;

use App\Actions\Fortify\PasswordValidationRules;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Laravel\Jetstream\Jetstream;

class AuthController extends ApiController
{
    use PasswordValidationRules;
    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
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

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'max:255'],
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken($user->name .'-AuthToken')->plainTextToken;

        return $this->responseSuccess(compact('token'));
    }

    /**
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        $user = auth()->user();

        $user->tokens()->delete();

        return $this->responseSuccess([], 'Logged out successfully');
    }

    /**
     * @return JsonResponse
     */
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

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->responseSuccess(compact('user'));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? $this->responseSuccess([], __($status))
            : $this->responseError(['error' => $status], __($status));
    }
}
