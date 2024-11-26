<?php

namespace App\Console\Commands;

use App\Jobs\RefreshBroadcastLogCache;
use App\Models\BroadcastLog;
use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Trait\CSVReader;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class UpdateBroadcastLogSentAtWithCSV extends Command
{
    use CSVReader;

    private BroadcastLogRepositoryInterface $broadcastLogRepository;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:broadcast-logs-sent {fileName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->broadcastLogRepository = app()->make(BroadcastLogRepositoryInterface::class);
        $file_name = $this->argument('fileName');
        $folder_address = config('setting.csv_uploaded_file_address', 'csv');
        $file_address = $folder_address.'/'.$file_name;
        $file = Storage::disk(config('app.csv.disk'))->get($file_address);

        if (empty($file)){
            $this->error('file not found');

            exit();
        }

        $csv = $this->csvToCollection($file);
        $message_ids = $csv->pluck('UID')->toArray();
        $number_of_updated_rows = $this->broadcastLogRepository->updateWithIDs($message_ids, [
            'sent_at' => Carbon::now()
        ]);

        if (count($message_ids) > 0) {
            if (!Cache::get(BroadcastLog::CACHE_STATUS_KEY)) {
                Cache::put(BroadcastLog::CACHE_STATUS_KEY, true, now()->addHour());
                RefreshBroadcastLogCache::dispatch();
            } else {
                $this->info('Broadcast log cache is already running.');
            }
        }

        $this->info("broadcast_logs sent at column updated for $number_of_updated_rows number of rows");
    }
}
