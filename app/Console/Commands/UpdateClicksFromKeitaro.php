<?php

namespace App\Console\Commands;

use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Services\Clicks\ClickService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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
                $date_time_string = $row['datetime'];
                $date_time = null;
                try {
                    $date_time = Carbon::parse($date_time_string);
                }
                catch (\Exception $exception){
                    $date_time = Carbon::now();
                }
                if($this->broadcastLogRepository->updateByID([
                    'is_click' => true,
                    'clicked_at' => $date_time
                ], $log_id) === false){
                    Log::error('update click failed', ['id' => $log_id, 'clicked_at' => $date_time]);
                }
            }
            $offset += $limit;
            $total = $response['total'];
        }
    }
}
