<?php

namespace App\Http\Controllers;
use App\Models\RecipientsList;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class RecipientsListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $recipient_lists = auth()->user()->recipientLists()->latest()->paginate(5);

        return view('recipient_lists.index', compact('recipient_lists'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('recipient_lists.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $recipientsList = auth()->user()->recipientLists()->create([
            'name' => $request->name,
        ]);

        if($request->entry_type =='file'){

            $file = $request->file('csv_file');
            $data = Excel::toArray(function ($row) {
                $row = array_map('trim', $row);
                return [
                    'name' => $row[0],
                    'phone' => $row[1],
                    'email' => $row[2],
                ];
            }, $file);

        }else{
            $data = explode(',', $request->numbers);

        }
        DB::beginTransaction();

        try {
            foreach ($data as $row) {
                if(is_array($row)):
                    $contact = Contact::firstOrCreate([
                        'phone' => $row['phone'],
                    ], [
                        'name' => $row['name'],
                        'email' => $row['email'],
                    ]);

                else:

                    $contact = Contact::firstOrCreate([
                        'phone' => $row,
                    ], ['name' => $row]);
                    
                endif;

                $recipientsList->contacts()->attach($contact->id);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Contacts imported successfully.');
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()->withErrors(['error' => 'An error occurred while importing contacts.']);
        }



        return redirect()->route('recipient_lists.index')->with('success', 'Recipients List created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(RecipientsList $recipientsList)
    {
        return view('recipient_lists.show', compact('recipientsList'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RecipientsList $recipientsList)
    {
        return view('recipient_lists.edit', compact('recipientsList'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RecipientsList $recipientsList)
    {
        $request->validate([
            'name' => 'string|max:255',
            'email' => 'string|email|max:255'.$client->id,
        ]);

        $recipientsList->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return redirect()->route('recipient_lists.index')->with('success', 'Client updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RecipientsList $recipientsList)
    {
        $recipientsList->delete();

        return redirect()->route('recipient_lists.index')->with('success', 'Client deleted successfully.');
    }
}
