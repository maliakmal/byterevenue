<?php

namespace App\Http\Controllers\Swagger;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RecipientsListSwaggerController extends SwaggerController
{
    /**
     * @OA\Get(
     *     path="/api/recipient_lists",
     *     summary="Get a list of recipient lists",
     *     tags={"Recipient Lists"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(ref="#/components/schemas/Pagination")
     *     )
     * )
     */
    public function index() {}

    /**
     * @OA\Post(
     *     path="/api/recipient_lists",
     *     summary="Store a new recipient list",
     *     tags={"Recipient Lists"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *     name="name",
     *     in="query",
     *     required=true,
     *     @OA\Schema(type="string"),
     *         description="Recipient List Name"
     *     ),
     *     @OA\Parameter(
     *     name="csv_file",
     *     in="query",
     *     required=true,
     *     @OA\Schema(type="string", format="binary"),
     *         description="CSV file with contacts"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Recipient list created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Response")
     *     )
     * )
     */
    public function store() {}

    /**
     * @OA\Get(
     *     path="/api/recipient_lists/{id}",
     *     summary="Get a recipient list",
     *     tags={"Recipient Lists"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Recipient List ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="recipientList", type="object"),
     *             @OA\Property(property="contacts", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function show() {}

    /**
     * @OA\Put(
     *     path="/api/recipient_lists/{id}",
     *     summary="Update a recipient list",
     *     tags={"Recipient Lists"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Recipient List ID"
     *     ),
     *     @OA\Parameter(
     *     name="name",
     *     in="query",
     *     required=true,
     *     @OA\Schema(type="string"),
     *         description="Recipient List Name"
     *     ),
     *     @OA\Parameter(
     *     name="source",
     *     in="query",
     *     required=false,
     *     @OA\Schema(type="string"),
     *         description="Recipient List Source"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Recipient list updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Response")
     *     )
     * )
     */
    public function update() {}

    /**
     * @OA\Delete(
     *     path="/api/recipient_lists/{id}",
     *     summary="Delete a recipient list",
     *     tags={"Recipient Lists"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Recipient List ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Recipient list deleted successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Response")
     *     )
     * )
     */
    public function destroy() {}
}
