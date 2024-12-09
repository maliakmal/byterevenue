<?php

namespace App\Http\Controllers\Swagger;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;

/**
 * @OA\Info(
 *     title="API Documentation",
 *     version="1.0.0"
 * )
 *
 * @OA\SecurityScheme(
 *     type="http",
 *     securityScheme="bearerAuth",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
abstract class SwaggerController extends Controller
{
    use AuthorizesRequests;
}
