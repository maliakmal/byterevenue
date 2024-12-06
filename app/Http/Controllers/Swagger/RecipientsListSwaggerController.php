<?php

namespace App\Http\Controllers\Swagger;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="RecipientLists",
 *     description="Operations about user"
 * )
 */
class RecipientsListSwaggerController extends SwaggerController
{
    /**
     * @OA\Get(
     *     path="/recipient_lists",
     *     summary="Get a list of recipient lists",
     *     tags={"Recipient Lists"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(type="object")
     *         )
     *     )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function index() {}

    /**
     * @OA\Post(
     *     path="/recipient_lists",
     *     summary="Store a new recipient list",
     *     tags={"Recipient Lists"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="List Name"),
     *             @OA\Property(property="csv_file", type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Recipient list created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function store() {}

    /**
     * @OA\Get(
     *     path="/recipient_lists/{id}",
     *     summary="Get a recipient list",
     *     tags={"Recipient Lists"},
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
     *     path="/recipient_lists/{id}",
     *     summary="Update a recipient list",
     *     tags={"Recipient Lists"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Recipient List ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated List Name")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Recipient list updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="recipientList", type="object")
     *         )
     *     )
     * )
     */
    public function update() {}

    /**
     * @OA\Delete(
     *     path="/recipient_lists/{id}",
     *     summary="Delete a recipient list",
     *     tags={"Recipient Lists"},
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
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="List deleted successfully.")
     *         )
     *     )
     * )
     */
    public function destroy() {}

    private function getSourceForUser() {}
}
