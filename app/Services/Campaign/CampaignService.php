<?php

namespace App\Services\Campaign;

use App\Services\Keitaro\KeitaroCaller;
use App\Services\Keitaro\Requests\Campaign\CreateCampaignRequest;
use App\Services\Keitaro\Requests\Flows\CreateFlowRequest;
use Illuminate\Support\Str;

class CampaignService
{
    public function generateUrlForCampaign($domain, $alias, $messageID)
    {
        return $domain.DIRECTORY_SEPARATOR.$alias.'?uid='.$messageID;
    }

    public function createCampaignOnKeitaro($alias, $title, $groupID)
    {
        $caller = new KeitaroCaller();
        $keitaro_token = uniqid();
        $create_campaign_request = new CreateCampaignRequest($alias, $title, $keitaro_token, null, $groupID);
        return $caller->call($create_campaign_request);
    }

    public function createFlowOnKeitaro($campaignUrl, $campaignID, $campaignTitle)
    {
        $action_options = new \stdClass();
        $action_options->url = $campaignUrl;
        $create_flow_request = new CreateFlowRequest(
            $campaignID, 'redirect', 'forced',
            Str::slug($campaignTitle), 'http', null, null, $action_options);
        $caller = new KeitaroCaller();
        return $caller->call($create_flow_request);
    }
}
