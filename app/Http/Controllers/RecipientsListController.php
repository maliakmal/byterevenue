<?php

namespace App\Http\Controllers;
use App\Models\RecipientsList;
use App\Models\Contact;
use App\Models\User;
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
        $per_page = 12;
        $recipient_lists = auth()->user()->hasRole('admin')
            ? $recipient_lists = RecipientsList::with('user')->withCount(['contacts', 'campaigns'])
            : $recipient_lists = auth()->user()->recipientLists()->withCount(['contacts', 'campaigns']);
        $nameFilter = request()->input('name');
        $isImportedFilter = request()->input('is_imported', '');

        if ($nameFilter) {
            $recipient_lists = $recipient_lists->whereLike('name', '%' . request()->input('name') . '%');
        }
        if (is_numeric($isImportedFilter)) {
            $recipient_lists = $recipient_lists->where('is_imported', $isImportedFilter);
        }

        $recipient_lists = $recipient_lists->orderby('id', 'desc')->paginate($per_page);

        if (request()->input('output') == 'json') {
            return response()->success(null, $recipient_lists);
        }
        return view('recipient_lists.index', compact('recipient_lists'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sources = $this->getSourceForUser(auth()->id());
        return view('recipient_lists.create', compact('sources'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'source' => 'nullable|string|min:1|max:100'
        ]);

        $user = auth()->user();
        if ($user->show_introductory_screen) {
            $user->update(['show_introductory_screen' => false]);
        }

        if ($request->entry_type == 'file') {
            DB::beginTransaction();
            $recipientsList = $user->recipientLists()->create([
                'name' => $request->name,
            ]);

            $user_id = $user->id;
            $file = $request->file('csv_file');
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $newFileName = $user_id . '_' . time() . '.' . $extension;
            $filePath = $file->storeAs('uploads', $newFileName);
            $fullPath = storage_path('app/' . $filePath);

            $nameColumn = $request->input('name_column');
            $emailColumn = $request->input('email_column');
            $phoneColumn = $request->input('phone_column');
            $totalColumns = $request->input('total_columns');
            $dummyVariables = array_fill(0, $totalColumns, '@dummy');

            var_dump(request()->all());
            var_dump($nameColumn);
            var_dump($emailColumn);
            var_dump($phoneColumn);

            $nameVar = '@dummy';
            $emailVar = '@dummy';
            if ($nameColumn != null && $nameColumn != '-1') {
                $dummyVariables[$nameColumn] = 'name';
                $nameVar = 'name';
            }

            if ($emailColumn != null && $emailColumn != '-1') {
                $dummyVariables[$emailColumn] = 'email';
                $emailVar = 'email';
            }

            $dummyVariables[$phoneColumn] = 'phone';


            try {
                DB::statement("LOAD DATA LOCAL INFILE '$fullPath'
                               INTO TABLE contacts
                               FIELDS TERMINATED BY ','
                               OPTIONALLY ENCLOSED BY '\"'

                               LINES TERMINATED BY '\n'
                               IGNORE 1 ROWS
                                (" . implode(', ', $dummyVariables) . ")
                               SET name = " . ($nameVar != '@dummy' ? 'name' : "''") . ", email =  " . ($emailVar != '@dummy' ? 'name' : "''") . ", phone = TRIM(phone), created_at = NOW(), user_id='$user_id', file_tag='$newFileName', updated_at = NOW()");
                DB::statement(
                    "INSERT INTO contact_recipient_list (user_id, contact_id, recipients_list_id,  updated_at, created_at)
                                SELECT $user_id, id, $recipientsList->id, NOW(), NOW()
                                FROM contacts
                                WHERE file_tag='$newFileName'"
                );
                var_dump("LOAD DATA LOCAL INFILE '$fullPath'
                               INTO TABLE contacts
                               FIELDS TERMINATED BY ','
                               OPTIONALLY ENCLOSED BY '\"'
                               LINES TERMINATED BY '\n'
                               IGNORE 1 ROWS
                                (" . implode(', ', $dummyVariables) . ")
                               SET name = " . ($nameVar != '@dummy' ? 'name' : "''") . ", email =  " . ($emailVar != '@dummy' ? 'name' : "''") . ", phone = TRIM(phone), created_at = NOW(), user_id='$user_id', file_tag='$newFileName', updated_at = NOW()");

                var_dump("INSERT INTO contact_recipient_list (user_id, contact_id, recipients_list_id,  updated_at, created_at)
                                 SELECT $user_id, id, $recipientsList->id, NOW(), NOW()
                                 FROM contacts
                                 WHERE file_tag='$newFileName'"
                );

                $recipientsList->is_imported = true;
                $recipientsList->source = $request->source;
                $recipientsList->save();

                DB::commit();

                return redirect()->route('recipient_lists.index')->with('success', 'Contacts imported successfully.');
            } catch (\Exception $e) {
                DB::rollback();

                return redirect()->back()->with('error', 'Error importing CSV file: ' . $e->getMessage());
            }


        } else {
            $data = explode(',', $request->numbers);
        }

        if (count($data) == 0) {
            return redirect()->back()->withErrors(['error' => 'Cannot create an empty recipient list.']);
        }

        DB::beginTransaction();
        $recipientsList = $user->recipientLists()->create([
            'name' => $request->name,
        ]);

        try {
            $insertables = [];
            $now = now()->toDateTimeString();
            $existing_phones_for_user = Contact::where(['user_id' => $user->id])->pluck('phone', 'id')->toArray();
            foreach ($data as $row) {
                if (is_array($row)):

                    if (!in_array($row['phone'], $existing_phones_for_user)):
                        $insertables[] = [
                            'phone' => $row['phone'],
                            'user_id' => $user->id,
                            'name' => $row['name'],
                            'email' => $row['email'],
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    else:
                        $attachable_id = array_search($row['phone'], $existing_phones_for_user);
                        $recipientsList->contacts()->attach($attachable_id, ['user_id' => $user->id]);
                    endif;

                else:

                    $insertables[] = [
                        'phone' => $row,
                        'user_id' => $user->id,
                        'name' => $row,
                        'email' => '',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                endif;
            }

            foreach ($insertables as $insertable) {
                $contact = Contact::create($insertable);
                $recipientsList->contacts()->attach($contact->id, ['user_id' => auth()->id()]);
            }

            $recipientsList->is_imported = true;
            $recipientsList->save();


            DB::commit();
            return redirect()->route('recipient_lists.index')->with('success', 'Contacts imported successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withErrors(['error' => 'An error occurred while importing contacts.']);
        }
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
        $sources = $this->getSourceForUser(auth()->id());
        $recipientsList = RecipientsList::findOrFail($id);
        return view('recipient_lists.edit', compact('recipientsList', 'sources'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'string|max:255',
            'source' => 'nullable|string|max:255',
        ]);

        $recipientsList = RecipientsList::findOrFail($id);

        $recipientsList->update([
            'name' => $request->name,
            'source' => $request->source,
        ]);

        return redirect()->route('recipient_lists.index')->with('success', 'List updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $item = RecipientsList::withCount('campaigns')->findOrFail($id);
        if ($item->campaigns_count > 0) {
            return redirect()->back()->withErrors(['error' => 'List is associated with a campaign - this cannot be deleted.']);
        }

        $item->delete();

        return redirect()->route('recipient_lists.index')->with('success', 'List deleted successfully.');
    }

    /**
     * @param $userID
     * @return array
     */
    private function getSourceForUser($userID): array
    {
        return RecipientsList::select(DB::raw("DISTINCT('source') AS source"))
            ->where('user_id', $userID)
            ->whereNotNull('source')
            ->get()->pluck('source')->toArray();

    }
}
