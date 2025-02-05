<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Repositories\Contract\BroadcastLog\BroadcastLogRepositoryInterface;
use App\Repositories\Contract\Campaign\CampaignRepositoryInterface;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Update Campaign status from count of sent|clicked messages in DB
 *
 * Class UpdateSentMessagesJob
 * @package App\Jobs
 */
class UpdateCampaignsFromDbJob extends BaseJob implements ShouldQueue
{
    public $timeout = 600; // 10 minutes
    public $tries = 1;

    const QUEUE_KEY = 'import_recipient_list_processing';

    protected $broadcastLogRepository;
    protected $campaignRepository;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->broadcastLogRepository = app(BroadcastLogRepositoryInterface::class);
        $this->campaignRepository = app()->make(CampaignRepositoryInterface::class);

        $this->onQueue(self::QUEUE_KEY);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $pending_campaigns = $this->campaignRepository->getPendingCampaigns([]);
        \Log::info('Scan from db on clicks ' . count($pending_campaigns) . ' campaigns ', ['pending_campaigns_ids' => $pending_campaigns->pluck('id')->toArray()]);

        foreach($pending_campaigns as $campaign) {
            $totals = $this->broadcastLogRepository->getSentAndClicksByCampaign($campaign->id);
            $campaign->total_recipients_sent_to    = $totals['total_sent'];
            $campaign->total_recipients_click_thru = $totals['total_clicked'];
            $campaign->total_recipients_in_process  = $totals['total_processed'];

            if ($totals['total_clicked'] == 0 || $totals['total_sent'] == 0) {
                $campaign->total_ctr = 0;
            } elseif ($totals['total_clicked'] > $totals['total_sent']) {
                $campaign->total_ctr = 0;
            } else {
                $campaign->total_ctr = ($totals['total_clicked'] / $totals['total_sent']) * 100;
            }

            $campaign->save();

            if ($campaign->status === Campaign::STATUS_PROCESSING && $campaign->total_recipients_in_process >= $campaign->total_recipients) {
                $campaign->update(['status' => Campaign::STATUS_DONE]);
                \Log::info('Campaign '.$campaign->id.' marked as DONE ');
            }
        }
    }
}
