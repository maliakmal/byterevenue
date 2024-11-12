<?php

namespace App\Http\Controllers\Api;

use App\Enums\BroadcastLog\BroadcastLogStatus;
use App\Http\Controllers\Controller;
use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Trait\CSVReader;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BroadcastLogController extends Controller
{
    /**
     * @param BroadcastLogRepositoryInterface $broadcastLogRepository
     */
    public function __construct(
        protected BroadcastLogRepositoryInterface $broadcastLogRepository
    )
    {
    }

    use CSVReader;

    /**
     * @OA\Post(
     *     path="/messages/update-by-file/sent",
     *     summary="Update sent messages from CSV file",
     *     tags={"Broadcast Logs"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="messages_csv_file",
     *                     type="string",
     *                     format="binary",
     *                     description="CSV file containing message IDs"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Number of updated rows",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="updated_rows", type="integer", example=10)
     *         )
     *     )
     * )
     * @param Request $request
     * @return mixed
     */
    public function updateSentMessage(Request $request)
    {
        $max_allowed_csv_upload_file = config('app.csv.upload_max_size_allowed');
        $request->validate([
            'messages_csv_file' => "required|max:$max_allowed_csv_upload_file"
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
        return response()->success([
            'updated_rows' => $number_of_updated_rows
        ]);
    }
}
