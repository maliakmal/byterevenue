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

/**
 * @OA\Tag(
 *     name="RecipientLists",
 *     description="Operations about user"
 * )
 */
class RecipientsListApiController extends ApiController
{
    /**
     * @param RecipientListService $recipientListService
     */
    public function __construct(
        private RecipientListService $recipientListService,
    ) {}

    /**
     * @OA\Get(
     *     path="/recipient_lists",
     *     summary="Get a list of recipient lists",
     *     tags={"Recipient Lists"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(type="object")
     *         )
     *     )
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $nameFilter = $request->get('name');
        $isImportedFilter = $request->get('is_imported', '');
        $perPage = $request->get('per_page', 5);
        $recipientList = $this->recipientListService->getRecipientLists(
            $nameFilter,
            $isImportedFilter,
            $perPage,
        );

        return $this->responseSuccess($recipientList);
    }

    /**
     * @OA\Post(
     *     path="/recipient_lists",
     *     summary="Store a new recipient list",
     *     tags={"Recipient Lists"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="List Name"),
     *             @OA\Property(property="csv_file", type="string", format="binary")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Recipient list created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     * @param RecipientStoreRequest $request
     * @return JsonResponse
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
            return $this->responseError(message: 'Already processing');
        }

        $importRecipientsListId = ImportRecipientsList::create([
            'user_id'   => auth()->id(),
            'data'      => $data,
            'file_path' => $filePath,
        ]);

        ImportRecipientListsJob::dispatch($importRecipientsListId);

        return $this->responseSuccess(message: 'The list is being processed and created');
    }

    /**
     * @OA\Get(
     *     path="/recipient_lists/{id}",
     *     summary="Get a recipient list",
     *     tags={"Recipient Lists"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Recipient List ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="recipientList", type="object"),
     *             @OA\Property(property="contacts", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $recipientsList = RecipientsList::findOrFail($id);
        $recipientsGroup = $recipientsList->recipientsGroup;
        $contacts = $recipientsGroup->getAllContactsPaginated(10);

        return $this->responseSuccess(
            [
                'recipientList' => $recipientsList,
                'contacts' => $contacts,
            ]
        );
    }

    /**
     * @OA\Put(
     *     path="/recipient_lists/{id}",
     *     summary="Update a recipient list",
     *     tags={"Recipient Lists"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Recipient List ID"
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated List Name")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Recipient list updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="recipientList", type="object")
     *         )
     *     )
     * )
     * @param int $id
     * @param RecipientUpdateRequest $request
     * @return JsonResponse
     */
    public function update(int $id, RecipientUpdateRequest $request): JsonResponse
    {
        $recipientsList = RecipientsList::withCount(['contacts', 'campaigns'])->findOrFail($id);

        $recipientsList->update($request->validated());

        return $this->responseSuccess($recipientsList, 'List updated successfully.');
    }

    /**
     * @OA\Delete(
     *     path="/recipient_lists/{id}",
     *     summary="Delete a recipient list",
     *     tags={"Recipient Lists"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Recipient List ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Recipient list deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="List deleted successfully.")
     *         )
     *     )
     * )
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id)
    {
        $item = RecipientsList::withCount('campaigns')->findOrFail($id);
        if ($item->campaigns_count > 0) {
            return $this->responseError('List is associated with a campaign - this cannot be deleted.');
        }

        $item->delete();

        return $this->responseSuccess(message: 'List deleted successfully.');
    }

    /**
     * @param $userID
     * @return JsonResponse
     */
    private function getSourceForUser($userID): array
    {
        $recipientList = RecipientsList::select(DB::raw("DISTINCT('source') AS source"))
            ->where('user_id', $userID)
            ->whereNotNull('source')
            ->get()->pluck('source')
            ->toArray();

        return $this->responseSuccess($recipientList);
    }
}
