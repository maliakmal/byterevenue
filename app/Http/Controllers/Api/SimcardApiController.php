<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Requests\SimcardStoreRequest;
use App\Http\Requests\SimcardUpdateRequest;
use App\Models\SimCard;
use App\Services\Simcard\SimcardService;
use Illuminate\Http\JsonResponse;

class SimcardApiController extends ApiController
{
    public function __construct(
        public SimcardService $simcardService
    ) {}

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
     * @return JsonResponse
     */
    public function index()
    {
        return $this->responseSuccess(SimCard::latest()->paginate(10));
    }

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
     * @param SimcardStoreRequest $request
     * @return JsonResponse
     */
    public function store(SimcardStoreRequest $request)
    {
        $simcard = $this->simcardService->store($request->validated());

        return $this->responseSuccess($simcard, 'Simcard created successfully.');
    }

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
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id)
    {
        $simcard = SimCard::findOrFail($id);

        return $this->responseSuccess($simcard);
    }

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
     * @param int $id
     * @param SimcardUpdateRequest $request
     * @return JsonResponse
     */
    public function update(int $id, SimcardUpdateRequest $request)
    {
        $simcard = $this->simcardService->update($request->validated(), $id);

        return $this->responseSuccess($simcard, 'Simcard updated successfully.');
    }

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
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id)
    {
        $simcard = SimCard::findOrFail($id);
        $simcard->delete();

        return $this->responseSuccess(message: 'Simcard deleted successfully.');
    }
}
