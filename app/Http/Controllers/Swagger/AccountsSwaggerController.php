<?php

namespace App\Http\Controllers\Swagger;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountsSwaggerController extends SwaggerController
{
    /**
     * @OA\Get(
     *     path="/accounts",
     *     summary="Get all accounts",
     *     tags={"Accounts"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(type="object")
     *         )
     *     )
     * )
     * @return JsonResponse
     */
    public function index() {}

    /**
     * @OA\Get(
     *     path="/accounts/{id}",
     *     summary="Get account transactions",
     *     tags={"Accounts"},
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
     * @param string $id
     * @return JsonResponse
     */
    public function show($id) {}

    /**
     * @OA\Get(
     *     path="/tokens",
     *     summary="Get tokens for the current user",
     *     tags={"Accounts"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(type="object")
     *         )
     *     )
     * )
     * @return JsonResponse
     */
    public function showTokens() {}

    /**
     * @return JsonResponse
     */
    public function storeTokens(Request $request) {}
}
