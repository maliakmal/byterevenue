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

        $notifications = $user->notifications()->paginate($request->get('per_page', 5));

        return $this->responseSuccess(data: $notifications);
    }
}
