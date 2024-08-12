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
            DB::beginTransaction();
            $recipientsList = auth()->user()->recipientLists()->create([
                'name' => $request->name,
            ]);

            $user_id = auth()->user()->id;
            $file = $request->file('csv_file');
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $newFileName = $user_id . '_' . time() . '.' . $extension;
            $filePath = $file->storeAs('uploads', $newFileName);
            $fullPath = storage_path('app/' . $filePath);

            $nameColumn = $request->input('name_column') ? ($request->input('name_column')>=0?$request->input('name_column'):null):null;
            $emailColumn = $request->input('email_column') ? ($request->input('email_column')>=0?$request->input('email_column'):null):null;
            $phoneColumn = $request->input('phone_column') ? ($request->input('phone_column')>=0?$request->input('phone_column'):null):null;
            $totalColumns = $request->input('total_columns');
            $dummyVariables = array_fill(0, $totalColumns, '@dummy');

            if($nameColumn!=null){
                $dummyVariables[$nameColumn] = 'name';
            }

            if($emailColumn!=null){
                $dummyVariables[$emailColumn] = 'email';
            }

            $dummyVariables[$phoneColumn] = 'phone';
    

            try {
                DB::statement("LOAD DATA LOCAL INFILE '$fullPath' 
                               INTO TABLE contacts 
                               FIELDS TERMINATED BY ',' 
                               ENCLOSED BY '\"' 
                               LINES TERMINATED BY '\n' 
                               IGNORE 1 ROWS 
                                (" . implode(', ', $dummyVariables) . ")
                               SET name = IFNULL(name, ''), email = IFNULL(email, ''), phone = TRIM(phone), created_at = NOW(), user_id='$user_id', file_tag='$newFileName', updated_at = NOW()");
                DB::statement(
                                "INSERT INTO contact_recipient_list (user_id, contact_id, recipients_list_id,  updated_at, created_at)
                                SELECT $user_id, id, $recipientsList->id, NOW(), NOW()
                                FROM contacts 
                                WHERE file_tag='$newFileName'"
                            );
                $recipientsList->is_imported = true;
                $recipientsList->save();
                
                            DB::commit();

                return redirect()->route('recipient_lists.index')->with('success', 'Contacts imported successfully.');
            } catch (\Exception $e) {
                DB::rollback();

                return redirect()->back()->with('error', 'Error importing CSV file: ' . $e->getMessage());
            }


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
