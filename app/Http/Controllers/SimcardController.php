<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SimCard;

class SimcardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $simcards = SimCard::latest()->paginate(10);

        return view('simcards.index', compact('simcards'));
    }

    public function create()
    {
        return view('simcards.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'number' => 'required|string|max:255',
            'sms_capacity' => 'string|email|max:255',
            'country_code' => 'string|max:255',
            'active' => 'string|max:255',
        ]);

        SimCard::create([
            'number' => $request->number,
            'sms_capacity' => $request->sms_capacity,
            'country_code' => $request->country_code,
            'active' => $request->active,
        ]);

        return redirect()->route('simcards.index')->with('success', 'Simcard created successfully.');
    }

    public function show(SimCard $contact)
    {
        return view('simcards.show', compact('simcards'));
    }

    public function edit(SimCard $contact)
    {
        return view('simcards.edit', compact('simcards'));
    }

    public function update(Request $request, SimCard $simcard)
    {
        $request->validate([
            'number' => 'required|string|max:255'.$simcard->id,
            'sms_capacity' => 'string|email|max:255',
            'country_code' => 'string|max:255',
            'active' => 'string|max:255',

        ]);

        $simcard->update([
            'number' => $request->number,
            'sms_capacity' => $request->sms_capacity,
            'country_code' => $request->country_code,
            'active' => $request->active,
        ]);

        return redirect()->route('simcards.index')->with('success', 'simcards updated successfully.');
    }

    public function destroy(SimCard $simcard)
    {
        $simcard->delete();

        return redirect()->route('simcards.index')->with('success', 'simcards deleted successfully.');
    }
}
