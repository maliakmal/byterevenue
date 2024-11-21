<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiController;
use App\Http\Requests\BroadcastBatchStoreRequest;
use App\Models\BroadcastBatch;
use App\Models\BroadcastLog;
use App\Services\BroadcastBatch\BroadcastBatchService;
use Illuminate\Http\JsonResponse;

class BroadcastBatchApiController extends ApiController
{
    public function __construct(
        private BroadcastBatchService $broadcastBatchService
    ) {}

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
    public function store(BroadcastBatchStoreRequest $request)
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
    public function show(int $id)
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
    public function markAsProcessed(int $id)
    {
        [$result, $message] = $this->broadcastBatchService->markedAsProcessed($id);

        return $result
            ? $this->responseSuccess([], $message)
            : $this->responseError([], $message);
    }
}
