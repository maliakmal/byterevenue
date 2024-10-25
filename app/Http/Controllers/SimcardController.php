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
     * @param SimcardStoreRequest $request
     *
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
     * @param int $id
     *
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
     * @param int $id
     * @param SimcardUpdateRequest $request
     *
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
     * @param int $id
     *
     * @return JsonResponse
     */
    public function destroyApi(int $id)
    {
        $simcard = SimCard::findOrFail($id);
        $simcard->delete();

        return $this->responseSuccess([], 'Simcard deleted successfully.');
    }
}
