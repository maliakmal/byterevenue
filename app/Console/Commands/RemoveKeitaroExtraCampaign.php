<?php

namespace App\Console\Commands;

use App\Models\CampaignShortUrl;
use App\Services\Campaign\CampaignService;
use Doctrine\DBAL\Driver\Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RemoveKeitaroExtraCampaign extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'keitaro:remove-extra-campaigns';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $campaign_service = new CampaignService();
        $limit = 1000;
        $offset = 0;
        $counter = 0;
        try {
            while (true){
                $campaigns = $campaign_service->getAllCampaigns($limit, $offset);
                if(count($campaigns) == 0){
                    break;
                }
                $keitaro_campaign_ids = collect($campaigns)->pluck('id')->toArray();
                $database_campaign_ids = CampaignShortUrl::select('keitaro_campaign_id')->whereIn('keitaro_campaign_id', $keitaro_campaign_ids)
                    ->pluck('keitaro_campaign_id')->toArray();
                $differences = array_diff($keitaro_campaign_ids, $database_campaign_ids);
                foreach ($differences as $diff)
                {
                    try {
                        $campaign_service->moveCampaignToArchive($diff);
                        $counter++;
                    }catch (\Exception $exception){
                        $this->error('error move campaign to archive from keitaro: '.$exception->getMessage());
                        Log::error('error call move campaign to archive keitaro ', [$exception]);
                    }
                }
                $offset+=$limit;
                $this->info("report: A total of $counter campaign were deleted from keitaro until now");
            }
            $this->info("Remove Extra Campaign Was Successful - $counter Numbers Were Deleted");
        }catch (Exception $exception){
            report($exception);
            $this->error('error remove extra campaign data from keitaro '.$exception->getMessage());
            exit();
        }
    }
}
