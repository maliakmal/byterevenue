<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Models\Notify;
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

    public function update(Request $request, $id): JsonResponse
    {
        $user = auth()->user();

        $notification = Notify::where('user_id', $user->id)->find(intval($id));

        if (!$notification) {
            return $this->responseError(message: 'Notification not found', status: 404);
        }

        $notification->markAsRead();

        return $this->responseSuccess(message: 'Notification has been marked as read');
    }
}
