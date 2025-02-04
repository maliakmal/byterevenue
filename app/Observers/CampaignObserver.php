<?php

namespace App\Observers;

use App\Models\Campaign;

class CampaignObserver
{
    /**
     * Handle the Campaign "created" event.
     */
    public function created(Campaign $campaign): void
    {
        //
    }

    /**
     * Handle the Campaign "updated" event.
     */
    public function updated(Campaign $campaign): void
    {
        if ($campaign->isDirty('status')) {
            if ($campaign->status === Campaign::STATUS_PROCESSING) {
                \DB::table('unique_campaigns_stacks')->insertOrIgnore([
                    'campaign_id' => $campaign->id
                ]);
            } elseif (in_array($campaign->status, [Campaign::STATUS_DONE, Campaign::STATUS_ERROR])) {
                \DB::table('unique_campaigns_stacks')->where('campaign_id', $campaign->id)->delete();
            }

            $status = Campaign::nameByValue($campaign->status);

            notification(
                $campaign->user,
                "Campaign #$campaign->id status has been updated to $status",
                ['campaign_id' => $campaign->id, 'status' => $campaign->status],
                true,
            );
        }
    }

    /**
     * Handle the Campaign "deleted" event.
     */
    public function deleted(Campaign $campaign): void
    {
        //
    }

    /**
     * Handle the Campaign "restored" event.
     */
    public function restored(Campaign $campaign): void
    {
        //
    }

    /**
     * Handle the Campaign "force deleted" event.
     */
    public function forceDeleted(Campaign $campaign): void
    {
        //
    }
}
