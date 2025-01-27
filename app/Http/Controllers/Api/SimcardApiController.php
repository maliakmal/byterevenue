<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Middleware\CheckAdminRole;
use App\Http\Requests\SimcardStoreRequest;
use App\Http\Requests\SimcardUpdateRequest;
use App\Models\SimCard;
use App\Services\Simcard\SimcardService;
use Illuminate\Http\JsonResponse;

class SimcardApiController extends ApiController
{
    /**
     * @param SimcardService $simcardService
     */
    public function __construct(
        public SimcardService $simcardService
    ) {
        $this->middleware(['auth:sanctum', CheckAdminRole::class]);
    }

    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $simcards = SimCard::latest()->paginate(10);

        return $this->responseSuccess($simcards);
    }

    /**
     * @param SimcardStoreRequest $request
     * @return JsonResponse
     */
    public function store(SimcardStoreRequest $request): JsonResponse
    {
        $simcard = $this->simcardService->store($request->validated());

        return $this->responseSuccess($simcard, 'Simcard created successfully.');
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $simcard = SimCard::findOrFail($id);

        return $this->responseSuccess($simcard);
    }

    /**
     * @param int $id
     * @param SimcardUpdateRequest $request
     * @return JsonResponse
     */
    public function update(int $id, SimcardUpdateRequest $request): JsonResponse
    {
        $simcard = $this->simcardService->update($request->validated(), $id);

        return $this->responseSuccess($simcard, 'Simcard updated successfully.');
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        if (SimCard::findOrFail($id)->delete()) {
            return $this->responseSuccess([], 'Simcard deleted successfully.');
        }

        return $this->responseError([], 'Simcard not found.', 404);
    }
}
