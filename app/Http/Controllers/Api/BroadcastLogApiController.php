<?php

namespace App\Http\Controllers\Api;

use App\Enums\BroadcastLog\BroadcastLogStatus;
use App\Http\Controllers\ApiController;
use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Trait\CSVReader;
use Carbon\Carbon;
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

    public function updateSentMessage(Request $request)
    {
        $request->validate([
            'messages_csv_file' => "required|max:" . config('app.csv.upload_max_size_allowed'),
        ]);

        $file = $request->file('messages_csv_file');
        $content = file_get_contents($file->getRealPath());
        $csv = $this->csvToCollection($content);
        $message_ids = $csv->pluck('UID')->toArray();

        $number_of_updated_rows = $this->broadcastLogRepository->updateWithIDs($message_ids, [
            'sent_at' => Carbon::now(),
            'is_sent' => true,
            'status' => BroadcastLogStatus::SENT,
        ]);

        $this->responseSuccess(options: [
            'updated_rows' => $number_of_updated_rows
        ]);
    }
}
