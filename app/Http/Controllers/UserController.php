<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ApiController;
use App\Services\User\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends ApiController
{
    public function __construct(private UserService $userService) {}

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function edit(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email',
        ]);

        return $this->responseSuccess(
            $this->userService->editInfo($request->all())
        );
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        $result = $this->userService->changePassword($request->all());

        if (isset($result['message'])) {
            return $this->responseError($result['message']);
        }

        return $this->responseSuccess($result);
    }
}
