<?php

namespace App\Http\Controllers\Swagger;

class SimcardSwaggerController extends SwaggerController
{
    /**
     * @OA\Get(
     *     path="/simcards",
     *     summary="Get a list of simcards",
     *     tags={"Simcards"},
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
    public function index() {}

    /**
     * @OA\Post(
     *     path="/simcards",
     *     summary="Store a new simcard",
     *     tags={"Simcards"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Simcard Name"),
     *             @OA\Property(property="number", type="string", example="1234567890")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Simcard created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="number", type="string")
     *         )
     *     )
     * )
     */
    public function store() {}

    /**
     * @OA\Get(
     *     path="/simcards/{id}",
     *     summary="Get a simcard",
     *     tags={"Simcards"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Simcard ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="number", type="string")
     *         )
     *     )
     * )
     */
    public function show() {}

    /**
     * @OA\Put(
     *     path="/simcards/{id}",
     *     summary="Update a simcard",
     *     tags={"Simcards"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Simcard ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Simcard Name"),
     *             @OA\Property(property="number", type="string", example="0987654321")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Simcard updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="number", type="string")
     *         )
     *     )
     * )
     */
    public function update() {}

    /**
     * @OA\Delete(
     *     path="/simcards/{id}",
     *     summary="Delete a simcard",
     *     tags={"Simcards"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Simcard ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Simcard deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Simcard deleted successfully.")
     *         )
     *     )
     * )
     */
    public function destroy() {}
}
