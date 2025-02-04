<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotifyApiController extends ApiController
{
    /**
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();

        $perPage = $request->per_page;

        $notifications = $user->notifications()->paginate($request->get($perPage, 5));

        return $this->responseSuccess(data: $notifications);
    }
}
