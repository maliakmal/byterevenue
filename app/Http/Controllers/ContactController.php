<?php

namespace App\Http\Controllers;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function __construct()
    {
        //$this->authorizeResource(Contact::class);
    }

    public function index()
    {
        if(auth()->user()->hasRole('admin')):
            $contacts = Contact::select()->orderby('id', 'desc')->paginate(50);
        else:
            $contacts = auth()->user()->contacts()->latest()->paginate(50);
        endif;

        return view('contacts.index', compact('contacts'));
    }

    public function create()
    {
        return view('contacts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'string|max:255',
            'phone' => 'required|string|max:255',
        ]);

        auth()->user()->contacts()->create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        return redirect()->route('data-source.index')->with('success', 'Contact created successfully.');
    }

    public function show(Contact $dataSource)
    {
        return view('contacts.show', compact('dataSource'));
    }

    public function edit(Contact $dataSource)
    {
        return view('contacts.edit', compact('dataSource'));
    }

    public function update(Request $request, Contact $dataSource)
    {
        $request->validate([
            'name' => 'string|max:255',
            'phone' => 'required|string|max:255',
        ]);

        $dataSource->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        return redirect()->route('data-source.index')->with('success', 'Contact updated successfully.');
    }

    public function destroy(Contact $dataSource)
    {
        $dataSource->delete();

        return redirect()->route('data-source.index')->with('success', 'Contact deleted successfully.');
    }
}
