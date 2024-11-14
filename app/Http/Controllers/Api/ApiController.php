<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    /**
     * @param $data
     * @param string $message
     * @param int $status
     *
     * @return JsonResponse
     */
    protected function responseSuccess($data, $message = '', $status = 200): JsonResponse
    {
        return response()->json([
            'status'  => 'success',
            'data'    => $data,
            'message' => $message
        ], $status);
    }

    /**
     * @param $data
     * @param string $message
     * @param int $status
     *
     * @return JsonResponse
     */
    protected function responseError($data, $message = '', $status = 400): JsonResponse
    {
        return response()->json([
            'status'  => 'error',
            'data'    => $data,
            'message' => $message
        ], $status);
    }
}
