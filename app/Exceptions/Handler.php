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
     * @throws Throwable
     */
    public function handle(Request $request, Throwable $exception)
    {
        if (app()->hasDebugModeEnabled()) {

            switch ($exception) {
                case $exception instanceof \Illuminate\Validation\ValidationException:
                    return response()->json([
                        'error' => 'Validation Error',
                        'message' => $exception->getMessage(),
                        'errors' => $exception->errors(),
                    ], 422);
                case $exception instanceof \Illuminate\Auth\AuthenticationException:
                    return response()->json([
                        'error' => 'Unauthenticated',
                        'message' => $exception->getMessage(),
                    ], 401);
                case $exception instanceof \Illuminate\Auth\Access\AuthorizationException:
                    return response()->json([
                        'error' => 'Unauthorized',
                        'message' => $exception->getMessage(),
                    ], 403);
                case $exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException:
                    return response()->json([
                        'error' => 'Not Found',
                        'message' => $exception->getMessage(),
                    ], 404);
                case $exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException:
                    return response()->json([
                        'error' => 'Not Found',
                        'message' => $exception->getMessage(),
                    ], 404);
                case $exception instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException:
                    return response()->json([
                        'error' => 'Method Not Allowed',
                        'message' => $exception->getMessage(),
                    ], 405);
                case $exception instanceof \Illuminate\Database\QueryException:
                    return response()->json([
                        'error' => 'Query Error',
                        'message' => $exception->getMessage(),
                    ], 500);
                case $exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException:
                    return response()->json([
                        'error' => 'Http Error',
                        'message' => $exception->getMessage(),
                    ], $exception->getStatusCode());
                default:
                    return response()->json([
                        'error' => 'Internal Server Error',
                        'message' => $exception->getMessage(),
                        'trace' => $exception->getTrace(),
                    ], 500);
            }
        }

        throw $exception;
    }
}
