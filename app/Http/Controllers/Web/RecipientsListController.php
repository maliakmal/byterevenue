<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\RecipientStoreRequest;
use App\Http\Requests\RecipientUpdateRequest;
use App\Jobs\ImportRecipientListsJob;
use App\Models\ImportRecipientsList;
use App\Models\RecipientsList;
use App\Services\RecipientList\RecipientListService;
use Illuminate\Support\Facades\DB;

class RecipientsListController extends Controller
{
    /**
     * @param RecipientListService $recipientListService
     */
    public function __construct(
        private RecipientListService $recipientListService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $nameFilter = request()->input('name');
        $isImportedFilter = request()->input('is_imported', '');

        $recipient_lists = $this->recipientListService->getRecipientLists($nameFilter, $isImportedFilter);

        if (request()->input('output') == 'json') {
            return response()->success(null, $recipient_lists);
        }

        $processing = ImportRecipientsList::query()
            ->whereNull('processed_at')
            ->where('is_failed', 0)
            ->exists();

        return view('recipient_lists.index', compact('recipient_lists', 'processing'));
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
    public function store(RecipientStoreRequest $request)
    {
        $file = $request->file('csv_file');

        $newFileName = uniqid() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('recipient_lists', $newFileName);

        $data = $request->validated();
        unset($data['csv_file']);

        $interfaceBusy = ImportRecipientsList::query()
            ->whereNull('processed_at')
            ->whereNull('is_failed')
            ->first();

        if ($interfaceBusy) {
            return redirect()->back()->with('error', 'Already processing');
        }

        $importRecipientsListId = ImportRecipientsList::create([
            'user_id'   => auth()->id(),
            'data'      => $data,
            'file_path' => $filePath,
        ]);

        ImportRecipientListsJob::dispatch($importRecipientsListId);

        return redirect()->route('recipient_lists.index')->with('success', 'The list is being processed and created');
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
    public function update(RecipientUpdateRequest $request, $id)
    {
        $recipientsList = RecipientsList::findOrFail($id);

        $recipientsList->update($request->validated());

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
