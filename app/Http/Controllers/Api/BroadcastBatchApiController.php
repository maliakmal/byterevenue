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
     * @param BroadcastBatchStoreRequest $request
     * @return JsonResponse
     */
    public function store(BroadcastBatchStoreRequest $request): JsonResponse
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
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $broadcastBatch = BroadcastBatch::where('id', $id)
            ->with(['campaign', 'recipient_list', 'message'])
            ->firstOrFail();

        $recipient_lists = $broadcastBatch->recipient_list;

        if ($broadcastBatch->isDraft()) {
            $contacts = $recipient_lists->recipientsGroup->getAllContactsPaginated(10);
            $logs = [];
        } else {
            $contacts = [];
            $logs = BroadcastLog::select()
                ->where('recipients_list_id', '=', $broadcastBatch->recipients_list_id)
                ->paginate(10);
        }

        return $this->responseSuccess([
            'campaign' => $broadcastBatch->campaign,
            'contacts' => $contacts,
            'logs'     => $logs,
            'broadcast_batch' => $broadcastBatch,
            'message'         => $broadcastBatch->message,
            'recipient_lists' => $recipient_lists,
        ]);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function markAsProcessed(int $id): JsonResponse
    {
        [$result, $message] = $this->broadcastBatchService->markedAsProcessed(intval($id));

        return $result
            ? $this->responseSuccess([], $message)
            : $this->responseError([], $message);
    }
}
