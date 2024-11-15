<?php

namespace App\Services\User;

use App\Actions\Fortify\PasswordValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    use PasswordValidationRules;

    /**
     * @param array $data
     *
     * @return User
     */
    public function editInfo(array $data)
    {
        $user = auth()->user();
        $user->name = $data['name'];
        $user->email = $data['email'];

        $user->save();

        return $user;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function changePassword(array $data)
    {
        $user = auth()->user();

        if (!Hash::check($data['current_password'], $user->password)) {
            return ['message' => 'The provided password does not match your current password.'];
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        $user->tokens()->delete();

        return ['token' => $user->createToken($user->name .'-AuthToken')->plainTextToken];
    }
}
