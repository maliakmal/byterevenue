<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\SimcardStoreRequest;
use App\Http\Requests\SimcardUpdateRequest;
use App\Services\Simcard\SimcardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\SimCard;

class SimcardController extends ApiController
{
    public $simcardService;

    /**
     * @param SimcardService $simcardService
     */
    public function __construct(SimcardService $simcardService)
    {
        $this->simcardService = $simcardService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $simcards = SimCard::latest()->paginate(10);

        return view('simcards.index', compact('simcards'));
    }

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
    public function indexApi()
    {
        return $this->responseSuccess(SimCard::latest()->paginate(10));
    }

    /**
     * @return View
     */
    public function create()
    {
        return view('simcards.create');
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function store(SimcardStoreRequest $request)
    {
        $this->simcardService->store($request->validated());

        return redirect()->route('simcards.index')->with('success', 'Simcard created successfully.');
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
    public function storeApi(SimcardStoreRequest $request)
    {dd($request->validated());
        $simcard = $this->simcardService->store($request->validated());

        return $this->responseSuccess($simcard, 'Simcard created successfully.');
    }

    public function show(SimCard $contact)
    {
        //TODO: missed variable
        return view('simcards.show', compact('simcards'));
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
    public function showApi(int $id)
    {
        return $this->responseSuccess(SimCard::findOrFail($id));
    }

    public function edit(SimCard $contact)
    {
        //TODO: missed variable
        return view('simcards.edit', compact('simcards'));
    }

    /**
     * @param SimcardUpdateRequest $request
     * @param SimCard $simcard
     *
     *
     * @return RedirectResponse
     */
    public function update(SimcardUpdateRequest $request, SimCard $simcard)
    {
        $this->simcardService->update($request->validated(), $simcard->id);

        return redirect()->route('simcards.index')->with('success', 'simcards updated successfully.');
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
    public function updateApi(int $id, SimcardUpdateRequest $request)
    {
        $simcard = $this->simcardService->update($request->validated(), $id);

        return $this->responseSuccess($simcard, 'Simcard updated successfully.');
    }

    /**
     * @param SimCard $simcard
     *
     * @return RedirectResponse
     */
    public function destroy(SimCard $simcard)
    {
        $simcard->delete();

        return redirect()->route('simcards.index')->with('success', 'simcards deleted successfully.');
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
    public function destroyApi(int $id)
    {
        $simcard = SimCard::findOrFail($id);
        $simcard->delete();

        return $this->responseSuccess([], 'Simcard deleted successfully.');
    }
}
