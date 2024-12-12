<?php

namespace App\Http\Controllers\Swagger;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;

/**
 * @OA\Info(
 *     title="API Documentation",
 *     version="1.0.0",
 *     description="Additional documentation for the application:"
 * )
 *
 * @OA\SecurityScheme(
 *     type="http",
 *     securityScheme="bearerAuth",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 *
 * @OA\Schema(
 *     schema="Response",
 *     title="Response",
 *     description="Response block",
 *             @OA\Property(property="success", type="boolean", example="true|false"),
 *             @OA\Property(property="status", type="string", example="'success'|'error'"),
 *             @OA\Property(property="data (array)", example="[string: 'string', string: array, string: object, ...]"),
 *             @OA\Property(property="message", type="string")
 * )
 *
 * @OA\Schema(
 *     schema="User",
 *     title="User",
 *     description="User model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", example="example@mail.com"),
 *     @OA\Property(property="current_team_id", type="integer|null"),
 *     @OA\Property(property="profile_photo_path", type="string|null"),
 *     @OA\Property(property="created_at", type="datetime", example="2021-01-01 00:00:00"),
 *     @OA\Property(property="updated_at", type="datetime", example="2021-01-01 00:00:00")
 * )
 *
 * @OA\Schema(
 *     schema="Pagination",
 *     title="Standart Response with Pagination",
 *     description="Response block with data pagination",
 *     @OA\Property(property="success", type="boolean"),
 *     @OA\Property(property="status", type="string"),
 *     @OA\Property(property="data", type="array", @OA\Items(
 *         @OA\Property(property="current_page", type="integer"),
 *         @OA\Property(property="data", type="object",
 *             @OA\Property(property="[collection of models]", type="object"),
 *         ),
 *         @OA\Property(property="first_page_url", type="string"),
 *         @OA\Property(property="from", type="integer"),
 *         @OA\Property(property="last_page", type="integer"),
 *         @OA\Property(property="last_page_url", type="string"),
 *         @OA\Property(property="links", type="array", @OA\Items(
 *             @OA\Property(property="url", type="string"),
 *             @OA\Property(property="label", type="string"),
 *             @OA\Property(property="active", type="boolean"),
 *         )),
 *         @OA\Property(property="next_page_url", type="string"),
 *         @OA\Property(property="path", type="string"),
 *         @OA\Property(property="per_page", type="integer"),
 *         @OA\Property(property="prev_page_url", type="string"),
 *         @OA\Property(property="to", type="integer"),
 *         @OA\Property(property="total", type="integer"),
 *     )),
 *     @OA\Property(property="Message", type="string"),
 * )
 *
 * @OA\Schema (
 *     schema="Contact",
 *     title="Contact",
 *     description="Contact model",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="user_id", type="integer"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="email", type="string"),
 *     @OA\Property(property="phone", type="string"),
 *     @OA\Property(property="created_at", type="datetime"),
 *     @OA\Property(property="updated_at", type="datetime"),
 *     @OA\Property(property="recipients_list_id", type="integer"),
 *     @OA\Property(property="file_tag", type="string")
 * )
 *
 * @OA\ExternalDocumentation(
 *     description="Alternative viewer for the API documentation",
 *     url="/api-docs/repidoc"
 * )
 */
abstract class SwaggerController extends Controller
{
    use AuthorizesRequests;
}
