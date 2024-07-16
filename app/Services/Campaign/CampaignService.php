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

    public function createCampaignOnKeitaro($alias, $title, $groupID, $domainID, $type = 'position', $uniqueness_method = 'ip_ua',
                                            $cookies_ttl = 24, $position = 9999, $state = 'active', $cost_type = 'CPC', $cost_value = 0,
                                            $cost_currency = 'USD', $traffic_source_id = 0, $cost_auto = true, $uniqueness_use_cookies = true,
                                            $traffic_loss = 0

    )
    {
        $caller = new KeitaroCaller();
        $keitaro_token = uniqid();
        $create_campaign_request = new CreateCampaignRequest($alias, $title, $keitaro_token, $type, $groupID.'',
            $domainID, $cookies_ttl, $state, $cost_type, $cost_value, $cost_currency, $cost_auto, null,
        $traffic_source_id,null,null,null, $uniqueness_method, $position, $uniqueness_use_cookies,
        $traffic_loss);
        return $caller->call($create_campaign_request);
    }

    public function createFlowOnKeitaro($campaignID, $campaignTitle, $action_payload = null, $filters = null, $action_options = null,
                                        $type = 'regular', $schema = 'redirect', $position = 1,
                                        $comments = null, $state = 'deleted', $action_type = 'http',
                                        $collect_clicks = true, $filter_or = false, $weight = 100,
    )
    {
        $create_flow_request = new CreateFlowRequest(
            $campaignID, $schema, $type,
            Str::slug($campaignTitle), $action_type, $action_payload, $position, $weight, $action_options, $comments, $state,
            $collect_clicks, $filter_or, $filters
        );
        $caller = new KeitaroCaller();
        return $caller->call($create_flow_request);
    }
}
