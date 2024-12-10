<?php

namespace App\Http\Controllers\Swagger;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountsSwaggerController extends SwaggerController
{
    /**
     * @OA\Schema(
     *     schema="Response",
     *     title="Response",
     *     description="Response block",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="status", type="string"),
     *             @OA\Property(property="data (array)", example="[string: 'string', string: array, string: object]"),
     *             @OA\Property(property="message", type="string")
     * )
     * @OA\Schema(
     *     schema="User",
     *     title="User",
     *     description="User model",
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="name", type="string", example="John Doe"),
     *     @OA\Property(property="email", type="string", example="example@mail.com"),
     *     @OA\Property(property="current_team_id", type="integer|null"),
     *     @OA\Property(property="profile_photo_path", type="string|null"),
     *     @OA\Property(property="created_at", type="datetime"),
     *     @OA\Property(property="updated_at", type="datetime")
     * )
     *
     * @OA\Get(
     *     path="/api/accounts",
     *     summary="Get all accounts",
     *     tags={"Accounts"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function index() {}

    /**
     * @OA\Get(
     *     path="/api/accounts/{id}",
     *     summary="Get account transactions",
     *     tags={"Accounts"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string"),
     *         description="Account ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(type="object")
     *         )
     *     )
     * )
     */
    public function show($id) {}

    /**
     * @OA\Get(
     *     path="/api/tokens",
     *     summary="Get tokens for the current user",
     *     tags={"Accounts"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(type="object")
     *         )
     *     )
     * )
     */
    public function showTokens() {}

    public function storeTokens(Request $request) {}
}
