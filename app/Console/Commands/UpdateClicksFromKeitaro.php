<?php

namespace App\Console\Commands;

use App\Jobs\RefreshBroadcastLogCache;
use App\Models\BroadcastLog;
use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Services\Clicks\ClickService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Mockery\Exception;

class UpdateClicksFromKeitaro extends Command
{

    private BroadcastLogRepositoryInterface $broadcastLogRepository;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'keitaro:update-clicks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update clicks for today from keitaro';

    protected $hasChanges = false;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        \Log::info('start update clicks from keitaro');

        $this->broadcastLogRepository = app()->make(BroadcastLogRepositoryInterface::class);
        $click_service = new ClickService();
        $form = $end = Carbon::now()->format('Y-m-d');
        $limit = 1000;
        $offset = 0;
        $total = null;

        while ($offset == 0 || $total > $offset) {
            try {
                $response = $click_service->getClicksOnKeitaro($form, $end, $limit, $offset);
                \Log::debug('complete request to keitaro. Count of records: ' . count($response['rows'] ?? []));
            } catch (\Exception $exception) {
                Log::error('error read clicks from keitaro', [
                    'form' => $form,
                    'end' => $end,
                    'limit' => $limit,
                    'offset' => $offset,
                ]);
                report($exception);
                $this->error('error read from keitaro');

                exit();
            }
            $uid_param = config('app.keitaro.uid_param', 'sub_id_1');

            foreach ($response['rows'] ?? [] as $row){
                $log_id = $row[$uid_param];

                if (trim($log_id) == '') { // (!preg_match('/^[a-zA-Z0-9_-]+$/', $log_id)) {
                    \Log::error('log id is not valid', ['log_Id'=>$log_id, 'log' => $row]);
                    continue;
                }

                $log_data = $this->tryGetLog($row);
                $date_time_string = $row['datetime'];
                $date_time = null;

                try {
                    $date_time = Carbon::parse($date_time_string);
                }
                catch (\Exception $exception) {
                    \Log::error('error parse date', ['date' => $date_time_string]);
                    $date_time = Carbon::now();
                }

                $updateData = [
                    'is_click' => true,
                    'clicked_at' => $date_time,
                    'keitaro_click_log' => $log_data,
                ];

                if (isset($row['is_bot'])){
                    $updateData['is_bot'] = $row['is_bot'];
                }

                if (isset($row['is_unique_global'])){
                    $updateData['is_unique_global'] = $row['is_unique_global'];
                }

                if (isset($row['is_unique_campaign'])){
                    $updateData['is_unique_campaign'] = $row['is_unique_campaign'];
                }

                if ($this->broadcastLogRepository->updateBySlug($log_id, $updateData) === false) { // FIX THIS HERE <---
                    Log::error('update click failed', ['id' => $log_id, 'clicked_at' => $date_time]);
                } else {
                    $this->hasChanges = true;
                }
            }

            $offset += $limit;
            $total = $response['total'] ?? 0;
        }

        //if ($this->hasChanges) {

            if (!Cache::get(BroadcastLog::CACHE_STATUS_KEY)) {
                Cache::put(BroadcastLog::CACHE_STATUS_KEY, true, now()->addHour());
                RefreshBroadcastLogCache::dispatch();
            } else {
                $this->info('Broadcast log cache is already running.');
            }
        //}
    }

    /**
     * @param $row
     * @return array
     */
    private function tryGetLog($row) : ?array
    {
        try {
            $data = [
                "isp" => $row['isp'] ?? null,
                "country_flag" => $row['country_flag'] ?? null,
                "country" => $row['country'] ?? null,
                "region" => $row['region'] ?? null,
                "city" => $row['city'] ?? null,
                "language" => $row['language'] ?? null,
                "device_type" => $row['device_type'] ?? null,
                "user_agent" => $row['user_agent'] ?? null,
                "os_icon" => $row['os_icon'] ?? null,
                "os" => $row['os'] ?? null,
                "os_version" => $row['os_version'] ?? null,
                "browser" => $row['browser'] ?? null,
                "browser_version" => $row['browser_version'] ?? null,
                "device_model" => $row['device_model'] ?? null,
                "browser_icon" => $row['browser_icon'] ?? null,
                "ip" => $row['ip'] ?? null,
            ];

            return $data;
        } catch (\Exception $exception) {
            \Log::error('error get log data');
            report($exception);
        }

        return null;
    }
}
