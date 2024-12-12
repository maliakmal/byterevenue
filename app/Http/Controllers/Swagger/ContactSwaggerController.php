<?php

namespace App\Http\Controllers\Swagger;

use App\Http\Requests\ContactStoreRequest;
use App\Http\Requests\ContactUpdateRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactSwaggerController extends SwaggerController
{
    /**
     * @OA\Get(
     *     path="/api/data-source",
     *     summary="All Data-Source",
     *     tags={"Data-Source (Contacts)"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer"),
     *         description="Number of contacts per page"
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Contact name"
     *     ),
     *     @OA\Parameter(
     *         name="area_code",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Area code"
     *     ),
     *     @OA\Parameter(
     *         name="phone",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Phone number"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(ref="#/components/schemas/Pagination")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request) {}

    /**
     * @OA\Post(
     *     path="/api/data-source",
     *     summary="Create a new contact",
     *     tags={"Data-Source (Contacts)"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Property(property="name", type="string", example="John Doe", description="Contact name", required=true),
     *     @OA\Property(property="email", type="string", example="example@mail.com", description="Contact email", required=true),
     *     @OA\Property(property="phone", type="string", example="1234567890", description="Contact phone", required=true),
     *     @OA\Response(
     *         response=200,
     *         description="Contact created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Contact"),
     *     )
     * )
     * @param ContactStoreRequest $request
     * @return JsonResponse
     */
    public function store(ContactStoreRequest $request) {}

    /**
     * @OA\Get(
     *     path="/api/data-source/{id}",
     *     summary="Get a contact",
     *     tags={"Data-Source (Contacts)"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Contact ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(ref="#/components/schemas/Response")
     *     )
     * )
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id) {}

    /**
     * @OA\Put(
     *     path="/api/data-source/{id}",
     *     summary="Update a contact",
     *     tags={"Data-Source (Contacts)"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Contact ID",
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         required=true,
     *         description="Contact name",
     *         @OA\Schema(
     *             type="string",
     *             format="string"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         required=true,
     *         description="Contact email",
     *         @OA\Schema(
     *             type="string",
     *             format="email"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="phone",
     *         in="query",
     *         required=true,
     *         description="Contact phone",
     *         @OA\Schema(
     *             type="string",
     *             format="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contact updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Contact")
     *     )
     * )
     */
    public function update(int $id, ContactUpdateRequest $request) {}

    /**
     * @OA\Delete(
     *     path="/api/data-source/{id}",
     *     summary="Delete a contact",
     *     tags={"Data-Source (Contacts)"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Contact ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Contact deleted successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Response")
     *     )
     * )
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id) {}
}
