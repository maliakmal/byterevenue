<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Requests\RecipientStoreRequest;
use App\Http\Requests\RecipientUpdateRequest;
use App\Jobs\ImportRecipientListsJob;
use App\Models\ImportRecipientsList;
use App\Models\RecipientsList;
use App\Services\RecipientList\RecipientListService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecipientsListApiController extends ApiController
{
    // TODO::CONTROL OF OWNER OF THE LIST!

    /**
     * @param RecipientListService $recipientListService
     */
    public function __construct(
        private RecipientListService $recipientListService,
    ) {
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $recipientList = $this->recipientListService->getRecipientLists($request);

        return $this->responseSuccess($recipientList);
    }

    /**
     * @param RecipientStoreRequest $request
     * @return JsonResponse
     */
    public function store(RecipientStoreRequest $request): JsonResponse
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
            return $this->responseError(message: 'Already processing');
        }

        $importRecipientsList = ImportRecipientsList::create([
            'user_id' => auth()->id(),
            'data' => $data,
            'file_path' => $filePath,
        ]);

        ImportRecipientListsJob::dispatch($importRecipientsList);

        return $this->responseSuccess(message: 'The list is being processed and created');
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $recipientsList = RecipientsList::findOrFail($id);
        $recipientsGroup = $recipientsList->recipientsGroup;
        $contacts = [];
        if (isset($recipientsGroup))
            $contacts = $recipientsGroup->getAllContactsPaginated(10);

        return $this->responseSuccess([
            'recipientList' => $recipientsList,
            'contacts' => $contacts,
        ]);
    }

    /**
     * @param int $id
     * @param RecipientUpdateRequest $request
     * @return JsonResponse
     */
    public function update(int $id, RecipientUpdateRequest $request): JsonResponse
    {
        $relations = ['recipientsGroup:recipients_list_id,count'];

        if (auth()->user()->hasRole('admin')) {
            $relations[] = 'user';
        }

        $recipientsList = RecipientsList::with($relations)
            ->withCount(['campaigns'])
            ->findOrFail($id);

        $recipientsList->update($request->validated());

        return $this->responseSuccess($recipientsList, 'List updated successfully.');
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $item = RecipientsList::withCount('campaigns')->findOrFail($id);
        if ($item->campaigns_count > 0) {
            return $this->responseError(message: 'List is associated with a campaign - this cannot be deleted.');
        }

        $item->delete();

        return $this->responseSuccess(message: 'List deleted successfully.');
    }

    /**
     * @param $userID
     * @return JsonResponse
     */
    private function getSourceForUser($userID): JsonResponse
    {
        $recipientList = RecipientsList::select(DB::raw("DISTINCT('source') AS source"))
            ->where('user_id', $userID)
            ->whereNotNull('source')
            ->get()->pluck('source')
            ->toArray();

        return $this->responseSuccess($recipientList);
    }
}
