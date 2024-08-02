<?php

namespace App\Http\Controllers;
use App\Models\RecipientsList;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ContactsImport;
use League\Csv\Reader;
use App\Jobs\ProcessRecipientsImportCsvChunk;
use Illuminate\Support\Facades\Redis;


class RecipientsListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if(auth()->user()->hasRole('admin')):
            $recipient_lists = RecipientsList::select()->orderby('id', 'desc')->paginate(10);
        else:
            $recipient_lists = auth()->user()->recipientLists()->latest()->paginate(10);
        endif;


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

        if($request->entry_type =='file'){
            $recipientsList = auth()->user()->recipientLists()->create([
                'name' => $request->name,
            ]);
            $data = [];
            $csv = Reader::createFromPath($request->csv_file->getRealPath(), 'r');
            //$csv->setHeaderOffset(0); // Assuming the first row contains headers
            $csv->setDelimiter(',');
            // Get the records in chunks
            $chunkSize = 100; // Adjust chunk size as needed
            $ii = 0;

            $records = $csv->getRecords();
            $chunk = [];
            $rowCount = 0;
    
            foreach ($records as $record) {
                $chunk[] = $record;
                $rowCount++;
    
                // If the chunk size is reached, dispatch a job and reset the chunk
                if ($rowCount % $chunkSize === 0) {
                    $_params = [];
                    $_params['user_id'] = auth()->user()->id;
                    $_params['recipient_list'] = $recipientsList;
                    $_params['rows'] = $chunk;
                    ProcessRecipientsImportCsvChunk::dispatch($_params);
                    $chunk = [];
                }
            }

            // Dispatch remaining records if any
            if (!empty($chunk)) {
                $_params = [];
                $_params['user_id'] = auth()->user()->id;
                $_params['recipient_list'] = $recipientsList;
                $_params['rows'] = $chunk;
                $_params['is_import'] = true;
                ProcessRecipientsImportCsvChunk::dispatch($_params);
            }else{
                $_params = [];
                $_params['user_id'] = auth()->user()->id;
                $_params['recipient_list'] = $recipientsList;
                $_params['rows'] = [];
                $_params['is_import'] = true;
                ProcessRecipientsImportCsvChunk::dispatch($_params);

            }

            return redirect()->route('recipient_lists.index')->with('success', 'Contacts are being imported.');


        }else{
            $data = explode(',', $request->numbers);
        }

        if(count($data)==0){
            return redirect()->back()->withErrors(['error' => 'Cannot create an empty recipient list.']);
        }

        DB::beginTransaction();
        $recipientsList = auth()->user()->recipientLists()->create([
            'name' => $request->name,
        ]);

        try {
            $insertables = [];
            $now = now()->toDateTimeString();
            $existing_phones_for_user = Contact::select()->where(['user_id'=>auth()->user()->id])->pluck('phone', 'id')->toArray();
            foreach ($data as $row) {
                if(is_array($row)):

                    if(!in_array($row['phone'], $existing_phones_for_user)):
                        $insertables[] =[
                            'phone' => $row['phone'],
                            'user_id'=>auth()->user()->id,
                            'name' => $row['name'],
                            'email' => $row['email'],
                            'created_at'=>$now,
                            'updated_at'=>$now,
                            ];
                        else:
                            $attachable_id = array_search($row['phone'], $existing_phones_for_user);
                            $recipient_list->contacts()->attach($attachable_id, ['user_id'=>$user_id]);
                        endif;

                else:

                    $insertables[] =[
                        'phone' => $row,
                        'user_id'=>auth()->user()->id,
                        'name' => $row,
                        'email' => '',
                        'created_at'=>$now,
                        'updated_at'=>$now,
                    ];
                    
                endif;
            }

            foreach($insertables as $insertable){
                $contact = Contact::create($insertable);
                $recipientsList->contacts()->attach($contact->id, ['user_id'=>auth()->id()]);
            }

            $recipientsList->is_imported = true;
            $recipientsList->save();


            DB::commit();
            return redirect()->route('recipient_lists.index')->with('success', 'Contacts imported successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['error' => 'An error occurred while importing contacts.']);
        }



        return redirect()->route('recipient_lists.index')->with('success', 'Recipients List created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {

        $recipientsList = RecipientsList::findOrFail($id);
        $contacts = $recipientsList->contacts()->paginate(10);

        return view('recipient_lists.show', compact('recipientsList', 'contacts'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $recipientsList = RecipientsList::findOrFail($id);
        return view('recipient_lists.edit', compact('recipientsList'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'string|max:255',
        ]);

        $recipientsList = RecipientsList::findOrFail($id);

        $recipientsList->update([
            'name' => $request->name,
        ]);

        return redirect()->route('recipient_lists.index')->with('success', 'List updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $item = RecipientsList::findOrFail($id);
        if($item->campaigns->count()>0){
            return redirect()->back()->withErrors(['error' => 'List is associated with a campaign - this cannot be deleted.']);
        }

        $item->delete();

        return redirect()->route('recipient_lists.index')->with('success', 'List deleted successfully.');
    }
}
