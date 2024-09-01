<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Repositories\Contract\Campaign\CampaignRepositoryInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateCampaignsClicksAndStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-campaigns-clicks-and-stats';

    private  BroadcastLogRepositoryInterface $broadcastLogRepository;
    private  CampaignRepositoryInterface $campaignRepository;

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
        $this->broadcastLogRepository = app()->make(BroadcastLogRepositoryInterface::class);
        $this->campaignRepository = app()->make(CampaignRepositoryInterface::class);
        $pending_campaigns = $this->campaignRepository->getPendingCampaigns([]);
        $this->info('Grabbed '.count($pending_campaigns).' campaigns ');

        foreach($pending_campaigns as $campaign):
            $totals = $this->broadcastLogRepository->getTotalSentAndClicksByCampaign($campaign->id);
            $campaign->total_recipients_sent_to = $totals['total_sent'];
            $campaign->total_recipients_in_process = $totals['total_processed'];
            $campaign->total_recipients_click_thru = $totals['total_clicked'];
            if($totals['total_clicked'] == 0 || $totals['total_sent'] == 0 ){
                $campaign->total_ctr = 0;
            }else{
                $campaign->total_ctr = ($totals['total_clicked']/$totals['total_sent']) * 100;
            }
            $campaign->total_recipients = $totals['total'];
            $campaign->save();

            $this->info('Campaign '.$campaign->id.' updated total='.$campaign->total_recipients.' sent = '.$campaign->total_recipients_sent_to.' clicked = '.$campaign->total_recipients_click_thru);

            if($campaign->total_recipients == $campaign->total_recipients_sent_to){
                $campaign->status = Campaign::STATUS_DONE;
                $campaign->save();
                $this->info('Campaign '.$campaign->id.' marked as DONE ');
            }

        endforeach;

        $this->info('All Done :)');
    }
}
