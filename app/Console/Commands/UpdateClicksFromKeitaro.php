<?php

namespace App\Console\Commands;

use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Services\Clicks\ClickService;
use Carbon\Carbon;
use Illuminate\Console\Command;
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

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->broadcastLogRepository = app()->make(BroadcastLogRepositoryInterface::class);
        $click_service = new ClickService();
        $form = $end = Carbon::now()->format('Y-m-d');
        $limit = 1000;
        $offset = 0;
        $total = null;
        $response = null;
        while ($offset == 0 || $total > $offset) {
            try {
                $response = $click_service->getClicksOnKeitaro($form, $end, $limit, $offset);
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
            foreach ($response['rows'] as $row){
                $log_id = $row['sub_id_1'];
                if(!is_numeric($log_id)){
                    continue;
                }
                $log_data = $this->tryGetLog($row);
                $date_time_string = $row['datetime'];
                $date_time = null;
                try {
                    $date_time = Carbon::parse($date_time_string);
                }
                catch (\Exception $exception){
                    $date_time = Carbon::now();
                }
                $updateData = [
                    'is_click' => true,
                    'clicked_at' => $date_time,
                    'keitaro_click_log' => $log_data,
                ];
                if(isset($row['is_bot'])){
                    $updateData['is_bot'] = $row['is_bot'];
                }
                if(isset($row['is_unique_global'])){
                    $updateData['is_unique_global'] = $row['is_unique_global'];
                }
                if(isset($row['is_unique_campaign'])){
                    $updateData['is_unique_campaign'] = $row['is_unique_campaign'];
                }
                if($this->broadcastLogRepository->updateByID($updateData, $log_id) === false){
                    Log::error('update click failed', ['id' => $log_id, 'clicked_at' => $date_time]);
                }
            }
            $offset += $limit;
            $total = $response['total'];
        }
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
        }catch (\Exception $exception){
            report($exception);
        }
        return null;

    }
}
