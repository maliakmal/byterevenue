<?php

namespace App\Console\Commands;

use App\Models\CampaignShortUrl;
use App\Services\Campaign\CampaignService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PurneCampaignsOnKeitaro extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'keitaro:purne-campaigns';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'command to delete (archive) campaign periodically on keitaro';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $campaign_service = new CampaignService();
        $setting_key = 'prune_keitaro_campaigns';
        $schedule_to_purne = config("setting.$setting_key", null);
        if(!is_numeric($schedule_to_purne) || $schedule_to_purne <= 0){
            $this->error('the key prune_keitaro_campaigns is not set in settings');
            exit();
        }
        $purne_date = Carbon::now()->subDays($schedule_to_purne);

        $counter = 0;

        CampaignShortUrl::where('created_at', '<=', $purne_date)
            ->where('deleted_on_keitaro', false)
            ->whereNotNull('keitaro_campaign_id')
            ->chunk(10000, function ($data) use (&$counter, $campaign_service){
                $ids = [];
                foreach ($data as $datum){
                    try {
                        Http::fake(['http://3.84.28.36/*' => Http::response()]);
                        $campaign_service->moveCampaignToArchive($datum->keitaro_campaign_id);
                        $counter++;
                        $ids[] = $datum->id;
                    }
                    catch (\Exception $exception){
                        Log::error('error archive campaign ' , ['method' => __METHOD__, 'data' => $datum]);
                        $this->error($exception->getMessage());
                    }
                }
                if(CampaignShortUrl::whereIn('id', $ids)->update(['deleted_on_keitaro' => true]) === false){
                    Log::error('error update deleted_on_keitaro field in database', ['method' => __METHOD__, 'ids' => $ids]);
                }
            });

        $this->info("Number of $counter campaign was deleted from keitaro");

    }
}
