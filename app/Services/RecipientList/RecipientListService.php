<?php

namespace App\Services\RecipientList;

use App\Models\Contact;
use App\Models\RecipientsGroup;
use App\Models\RecipientsList;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class RecipientListService
{
    /**
     * @param string|null $nameFilter
     * @param string|null $isImportedFilter
     * @param string|null $perPage
     *
     * @return LengthAwarePaginator
     */
    public function getRecipientLists(Request $request): LengthAwarePaginator
    {
        $nameFilter = $request->get('name');
        $isImportedFilter = $request->get('is_imported', '');
        $perPage = intval($request->get('per_page', 5));
        $sortBy = $request->input('sort_by', 'id');
        $sortOrder = $request->input('sort_order', 'desc');

        $recipient_lists = auth()->user()->hasRole('admin')
            ? RecipientsList::with(['user', 'recipientsGroup:recipients_list_id,count'])->withCount(['campaigns'])
            : auth()->user()->recipientLists()->with(['user', 'recipientsGroup:recipients_list_id,count']);

        if (isset($nameFilter)) {
            $recipient_lists = $recipient_lists->whereLike('name', "%$nameFilter%");
        }

        if (is_numeric($isImportedFilter)) {
            $recipient_lists = $recipient_lists->where('is_imported', $isImportedFilter);
        }

        return $recipient_lists
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);
    }

    /**
     * @param array $data
     * @param $file
     * @param User|null $user
     *
     * @return array
     */
    public function store(array $data, File $file, ?User $user = null)
    {
        $user = auth()->user() ?? $user;

        if ($user->show_introductory_screen) {
            $user->update(['show_introductory_screen' => false]);
        }

        if ($data['entry_type'] === 'file') {
            if ($file instanceof UploadedFile) {
                throw new \Exception('Invalid file type. Use app/Jobs/ImportRecipientListsJob.php example.');
            }

            DB::beginTransaction();
            $recipientsList = $user->recipientLists()->create([
                'name' => $data['name'],
            ]);

            RecipientsGroup::create([
                'user_id' => $user->id,
                'recipients_list_id' => $recipientsList->id,
                'created_at' => now(),
            ]);

            $user_id = $user->id;
            $newFileName = $file->getFilename();
            $fullPath = $file->getPathname();

            $nameColumn = $data['name_column'] ?? null;
            $emailColumn = $data['email_column'] ?? null;
            $phoneColumn = $data['phone_column'] ?? null;
            $totalColumns = $data['total_columns'] ?? null;
            $dummyVariables = array_fill(0, $totalColumns, '@dummy');

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
                               SET name = " . ($nameVar != '@dummy' ? 'name' : "''") . ", email =  " . ($emailVar != '@dummy' ? 'email' : "''") . ", phone = TRIM(phone), created_at = NOW(), user_id='$user_id', file_tag='$newFileName', updated_at = NOW()");
                DB::statement(
                    "INSERT INTO contact_recipient_list (user_id, contact_id, recipients_list_id)
                           SELECT $user_id, id, $recipientsList->id
                           FROM contacts
                           WHERE file_tag='$newFileName'"
                );

                $recipientsList->update([
                    'is_imported' => true,
                    'source' => $data['source'],
                ]);

                DB::commit();

                return [true, 'Contacts imported successfully.'];
            } catch (\Exception $e) {
                DB::rollback();

                return [false, 'Error importing CSV file: ' . $e->getMessage()];
            }
        }

        // $data['entry_type'] != 'file'
        else {
            $data = explode(',', $data['numbers']);
        }

        if (count($data) == 0) {
            return [false, 'Cannot create an empty recipient list.'];
        }

        DB::beginTransaction();
        $recipientsList = $user->recipientLists()->create([
            'name' => $data['name'],
        ]);

        try {
            $insertables = [];
            $now = now()->toDateTimeString();
            $existing_phones_for_user = Contact::where(['user_id' => $user->id])->pluck('phone', 'id')->toArray();
            foreach ($data as $row) {
                if (is_array($row)) {
                    if (!in_array($row['phone'], $existing_phones_for_user)) {
                        $insertables[] = [
                            'phone' => $row['phone'],
                            'user_id' => $user->id,
                            'name' => $row['name'],
                            'email' => $row['email'],
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    } else {
                        $attachable_id = array_search($row['phone'], $existing_phones_for_user);
                        // $recipientsList->contacts()->attach($attachable_id, ['user_id' => $user->id]);
                    }
                } else {
                    $insertables[] = [
                        'phone' => $row,
                        'user_id' => $user->id,
                        'name' => $row,
                        'email' => '',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            $ids = [];

            foreach ($insertables as $insertable) {
                $contact = Contact::create($insertable);
                //$recipientsList->contacts()->attach($contact->id, ['user_id' => auth()->id()]);
                $ids[] = $contact->id;
            }

            RecipientsGroup::create([
                'recipients_list_id' => $recipientsList->id,
                'user_id' => $user->id,
                'ids' => $ids,
                'count' => count($ids),
                'created_at' => now(),
            ]);

            $recipientsList->is_imported = true;
            $recipientsList->save();

            DB::commit();

            return [true, 'Contacts imported successfully.'];
        } catch (\Exception $e) {
            DB::rollback();

            return [false, 'An error occurred while importing contacts.'];
        }
    }
}
