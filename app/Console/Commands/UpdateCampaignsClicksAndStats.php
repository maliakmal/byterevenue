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
        \Log::info('Scan from db on clicks ' . count($pending_campaigns) . ' campaigns ', ['pending_campaigns_ids' => $pending_campaigns->pluck('id')->toArray()]);

        foreach($pending_campaigns as $campaign) {
            $totals = $this->broadcastLogRepository->getSentAndClicksByCampaign($campaign->id);
            $campaign->total_recipients_sent_to    = $totals['total_sent'];
            $campaign->total_recipients_click_thru = $totals['total_clicked'];
            $campaign->total_recipints_in_process  = $totals['total_processed'];

            if ($totals['total_clicked'] == 0 || $campaign->total_recipients == 0) {
                $campaign->total_ctr = 0;
            } else {
                $campaign->total_ctr = ($totals['total_clicked'] / $campaign->total_recipients) * 100;
            }

            $campaign->save();

            $this->info('Campaign '.$campaign->id.' updated total='.$campaign->total_recipients.' sent = '.$campaign->total_recipients_sent_to.' clicked = '.$campaign->total_recipients_click_thru);

            if ($campaign->total_recipients_sent_to >= $campaign->total_recipients) {
                $campaign->update(['status' => Campaign::STATUS_DONE]);
                \Log::info('Campaign '.$campaign->id.' marked as DONE ');
            }
        }
    }
}
