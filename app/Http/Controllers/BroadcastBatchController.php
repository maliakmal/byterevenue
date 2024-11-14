<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\BroadcastBatchStoreRequest;
use App\Models\BroadcastBatch;
use App\Models\BroadcastLog;
use App\Models\Campaign;
use App\Models\Message;
use App\Models\RecipientsList;
use App\Services\BroadcastBatch\BroadcastBatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BroadcastBatchController extends ApiController
{
    public function __construct(private BroadcastBatchService $broadcastBatchService) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        //
        $campaign_id = $request->query('campaign_id');
        $campaign = Campaign::find($campaign_id);

        $recipient_lists = RecipientsList::where('user_id', auth()->id())->get();
        return view('broadcast_batch.create', compact('campaign', 'recipient_lists'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BroadcastBatchStoreRequest $request)
    {
        [$campaign, $broadcast_batch] = $this->broadcastBatchService->store($request->validated());

        return redirect()->route('campaigns.show', $campaign)
            ->with('success', 'Broadcast Job created successfully.');
    }

    /**
     * @OA\Post(
     *     path="/broadcast_batches",
     *     summary="Store a new broadcast batch",
     *     tags={"Broadcast Batches"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="campaign_id", type="integer", example=1),
     *             @OA\Property(property="recipients_list_id", type="integer", example=1),
     *             @OA\Property(property="message_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Broadcast Job created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="campaign", type="object"),
     *             @OA\Property(property="broadcast_batch", type="object")
     *         )
     *     )
     * )
     * @param BroadcastBatchStoreRequest $request
     *
     * @return JsonResponse
     */
    public function storeApi(BroadcastBatchStoreRequest $request)
    {
        [$campaign, $broadcast_batch] = $this->broadcastBatchService->store($request->validated());

        return $this->responseSuccess(
            [
                'campaign' => $campaign,
                'broadcast_batch' => $broadcast_batch,
            ],
            'Broadcast Job created successfully.'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(BroadcastBatch $broadcastBatch)
    {
        $recipient_lists = $broadcastBatch->recipient_list;
        if ($broadcastBatch->isDraft()) {
            $contacts = $recipient_lists->contacts()->paginate(10);
            $logs = [];

        } else {
            $contacts = [];
            $logs = BroadcastLog::select()
                ->where('recipients_list_id', '=', $broadcastBatch->recipients_list_id)
                ->paginate(10);
        }

        return view('broadcast_batch.show')->with(
            [
                'campaign' => $broadcastBatch->campaign,
                'contacts' => $contacts,
                'logs' => $logs,
                'broadcast_batch' => $broadcastBatch,
                'message' => $broadcastBatch->message,
                'recipient_lists' => $recipient_lists,
            ]
        );
    }

    /**
     * @OA\Get(
     *     path="/broadcast_batches/{id}",
     *     summary="Get a broadcast batch",
     *     tags={"Broadcast Batches"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Broadcast Batch ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="campaign", type="object"),
     *             @OA\Property(property="contacts", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="logs", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="broadcast_batch", type="object"),
     *             @OA\Property(property="message", type="object"),
     *             @OA\Property(property="recipient_lists", type="object")
     *         )
     *     )
     * )
     * @param int $id
     *
     * @return JsonResponse
     */
    public function showApi(int $id)
    {
        $broadcastBatch = BroadcastBatch::where('id', $id)
            ->with(['campaign', 'recipient_list', 'message'])
            ->firstOrFail();
        $recipient_lists = $broadcastBatch->recipient_list;

        if ($broadcastBatch->isDraft()) {
            $contacts = $recipient_lists->contacts()->paginate(10);
            $logs = [];
        } else {
            $contacts = [];
            $logs = BroadcastLog::select()
                ->where('recipients_list_id', '=', $broadcastBatch->recipients_list_id)
                ->paginate(10);
        }

        return $this->responseSuccess(
            [
                'campaign' => $broadcastBatch->campaign,
                'contacts' => $contacts,
                'logs' => $logs,
                'broadcast_batch' => $broadcastBatch,
                'message' => $broadcastBatch->message,
                'recipient_lists' => $recipient_lists,
            ]
        );
    }

    /**
     * @param $id
     *
     * @return RedirectResponse
     */
    public function markAsProcessed($id)
    {
        [$result, $message] = $this->broadcastBatchService->markedAsProcessed($id);

        if ($result) {
            return redirect()->back()->with('success', $message);
        }

        return redirect()->back()->withErrors(['error' => $message]);
    }

    /**
     * @OA\Post(
     *     path="/broadcast_batches/mark_as_processed/{id}",
     *     summary="Mark a broadcast batch as processed",
     *     tags={"Broadcast Batches"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Broadcast Batch ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Broadcast Batch marked as processed",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Broadcast Batch marked as processed.")
     *         )
     *     )
     * )
     * @param int $id
     *
     * @return JsonResponse
     */
    public function markAsProcessedApi(int $id)
    {
        [$result, $message] = $this->broadcastBatchService->markedAsProcessed($id);

        return $result
            ? $this->responseSuccess([], $message)
            : $this->responseError([], $message);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BroadcastBatch $broadcastBatch)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BroadcastBatch $broadcastBatch)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BroadcastBatch $broadcastBatch)
    {
        //
    }
}
