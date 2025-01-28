<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Http\Request;

class Handler
{
    /**
     * @param  Request $request
     * @param  Throwable $exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Throwable $exception)
    {
        if (app()->hasDebugModeEnabled()) {
            return response()->json([
                'error' => 'Internal Server Error',
                'message' => $exception->getMessage(),
                'trace' => $exception->getTrace(),
            ], 500);
        }

        return response()->json([
            'error' => 'Internal Server Error',
            'message' => 'Something went wrong',
        ], 500);
    }
}
