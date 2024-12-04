<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class ApiController extends Controller
{
    /**
     * @param $data
     * @param string $message
     * @param int $status
     *
     * @return JsonResponse
     */
    protected function responseSuccess($data = [], $message = '', $status = 200, $options = []): JsonResponse
    {
        return response()->json([
            'success' => true,
            'status'  => 'success',
            'data'    => $data,
            'message' => $message,
        ] + $options, $status);
    }

    /**
     * @param $data
     * @param string $message
     * @param int $status
     *
     * @return JsonResponse
     */
    protected function responseError($data = [], $message = '', $status = 400, $options = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'status'  => 'error',
            'data'    => $data,
            'message' => $message,
        ] + $options, $status);
    }
}
