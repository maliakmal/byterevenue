<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\SimcardStoreRequest;
use App\Http\Requests\SimcardUpdateRequest;
use App\Models\SimCard;
use App\Services\Simcard\SimcardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SimcardController extends Controller
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

    public function show(SimCard $contact)
    {
        //TODO: missed variable
        return view('simcards.show', compact('simcards'));
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
     * @return RedirectResponse
     */
    public function update(SimcardUpdateRequest $request, SimCard $simcard)
    {
        $this->simcardService->update($request->validated(), $simcard->id);

        return redirect()->route('simcards.index')->with('success', 'simcards updated successfully.');
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
}
