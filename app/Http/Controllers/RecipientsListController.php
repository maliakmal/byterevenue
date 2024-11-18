<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\RecipientStoreRequest;
use App\Http\Requests\RecipientUpdateRequest;
use App\Models\RecipientsList;
use App\Models\Contact;
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
class RecipientsListController extends ApiController
{
    /**
     * @param RecipientListService $recipientListService
     */
    public function __construct(
        private RecipientListService $recipientListService
    ) {
    }

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
        return view('recipient_lists.index', compact('recipient_lists'));
    }

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
    public function indexApi(Request $request)
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

        [$success, $message] = $this->recipientListService->store($request->validated(), $file);

        if ($success) {
            return redirect()->route('recipient_lists.index')->with('success', $message);
        }

        return redirect()->back()->with('error', $message);
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
    public function storeApi(RecipientStoreRequest $request)
    {
        $file = $request->file('csv_file');

        [$success, $message] = $this->recipientListService->store($request->validated(), $file);

        if ($success) {
            return $this->responseSuccess($message);
        }

        return $this->responseError($message);
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
    public function showApi(int $id): JsonResponse
    {
        $recipientsList = RecipientsList::findOrFail($id);
        $contacts = $recipientsList->contacts()->paginate(10);

        return $this->responseSuccess(
            [
                'recipientList' => $recipientsList,
                'contacts' => $contacts,
            ]
        );
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
    public function updateApi(int $id, RecipientUpdateRequest $request): JsonResponse
    {
        $recipientsList = RecipientsList::findOrFail($id);

        $recipientsList->update($request->validated());

        return $this->responseSuccess($recipientsList, 'List updated successfully.');
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
    public function destroyApi(int $id)
    {
        $item = RecipientsList::withCount('campaigns')->findOrFail($id);
        if ($item->campaigns_count > 0) {
            return $this->responseError('List is associated with a campaign - this cannot be deleted.');
        }

        $item->delete();

        return $this->responseSuccess('List deleted successfully.');
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
