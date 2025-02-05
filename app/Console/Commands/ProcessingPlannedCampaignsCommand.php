<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Services\Campaign\CampaignService;
use Illuminate\Console\Command;

class ProcessingPlannedCampaignsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:processing-planned-campaigns-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for processing planned campaigns';

    /**
     * @var CampaignService
     */
    protected CampaignService $campaignService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(CampaignService $campaignService)
    {
        parent::__construct();

        $this->campaignService = $campaignService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $campaigns = Campaign::where('status', Campaign::STATUS_PLANNED)
            ->where('is_paid', true)
            ->where('planned_at', '>=', now()->toDateTimeString())
            ->get();

        foreach ($campaigns as $campaign) {
            $campaign->markAsProcessed();

            [$result, $message] = $this->campaignService->markAsProcessed($campaign->id);

            if (!$result) {
                \Log::error('Error while start delay processing campaign', [
                    'campaign_id' => $campaign->id,
                    'message' => $message
                ]);

                $campaign->update([
                    'status' => Campaign::STATUS_ERROR,
                ]);
            }
        }

        return Command::SUCCESS;
    }
}
