<?php

namespace App\Http\Controllers\Api;

use App\Enums\BroadcastLog\BroadcastLogStatus;
use App\Http\Controllers\ApiController;
use App\Jobs\UpdateSentMessagesJob;
use App\Models\UpdateSentMessage;
use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Trait\CSVReader;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BroadcastLogApiController extends ApiController
{
    /**
     * @param BroadcastLogRepositoryInterface $broadcastLogRepository
     */
    public function __construct(
        protected BroadcastLogRepositoryInterface $broadcastLogRepository
    ) {}

    use CSVReader;

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateSentMessage(Request $request): JsonResponse
    {
        $request->validate([
            'messages_csv_file' => "required|max:" . config('app.csv.upload_max_size_allowed'),
        ]);

        $file = $request->file('messages_csv_file');
        $newFilename = 'update-'. str_replace('.', '', microtime(true)) . '.' . $file->getClientOriginalExtension();
        $file->storeAs('update_sent_messages', $newFilename);

        $updateLog = UpdateSentMessage::create([
            'file_name'  => $newFilename,
            'status'     => UpdateSentMessage::STATUS_CREATED,
            'ip_address' => $request->ip(),
        ]);

        dispatch(new UpdateSentMessagesJob($updateLog->id));

        return $this->responseSuccess(options: [
            'update_log_id' => $updateLog->id
        ]);
    }
}
