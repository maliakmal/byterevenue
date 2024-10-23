<?php

namespace App\Services\Campaign;

use App\Services\Keitaro\KeitaroCaller;
use App\Services\Keitaro\Requests\Campaign\CreateCampaignRequest;
use App\Services\Keitaro\Requests\Campaign\GetAllCampaignsRequest;
use App\Services\Keitaro\Requests\Campaign\MoveCampaignToArchiveRequest;
use App\Services\Keitaro\Requests\Flows\CreateFlowRequest;
use Illuminate\Support\Str;

class CampaignService
{
    public function generateUrlForCampaign($domain, $alias, $messageID = null)
    {
        $param = config('app.keitaro.uid_param', 'sub_id_1');
        return $domain.DIRECTORY_SEPARATOR.$alias.( $messageID ? '?'.$param.'='.$messageID : '' );
    }

    public function createCampaignOnKeitaro($alias, $title, $groupID, $domainID, $type = 'position', $uniqueness_method = 'ip_ua',
                                            $cookies_ttl = 24, $position = 9999, $state = 'active', $cost_type = 'CPC', $cost_value = 0,
                                            $cost_currency = 'USD', $traffic_source_id = 0, $cost_auto = true, $uniqueness_use_cookies = true,
                                            $traffic_loss = 0

    )
    {

        $keitaro_token = uniqid();
        $create_campaign_request = new CreateCampaignRequest($alias, $title, $keitaro_token, $type, $groupID.'',
            $domainID, $cookies_ttl, $state, $cost_type, $cost_value, $cost_currency, $cost_auto, null,
        $traffic_source_id,null,null,null, $uniqueness_method, $position, $uniqueness_use_cookies,
        $traffic_loss);
        return KeitaroCaller::call($create_campaign_request);
    }

    public function createFlowOnKeitaro($campaignID, $campaignTitle, $action_payload = null, $filters = null, $action_options = null,
                                        $type = 'forced', $schema = 'redirect', $position = 1,
                                        $comments = null, $state = 'active', $action_type = 'http',
                                        $collect_clicks = true, $filter_or = false, $weight = 100,
    )
    {
        $create_flow_request = new CreateFlowRequest(
            $campaignID, $schema, $type,
            Str::slug($campaignTitle), $action_type, $action_payload, $position, $weight, $action_options, $comments, $state,
            $collect_clicks, $filter_or, $filters
        );

        return KeitaroCaller::call($create_flow_request);
    }

    /**
     * @param int|null $limit
     * @param int $offset
     * @return mixed
     */
    public function getAllCampaigns(?int $limit, int $offset)
    {
        $request = new GetAllCampaignsRequest($limit, $offset);

        return KeitaroCaller::call($request);
    }

    /**
     * @param int $campaignID
     * @return mixed
     */
    public function moveCampaignToArchive(int $campaignID)
    {
        $request = new MoveCampaignToArchiveRequest($campaignID);

        return KeitaroCaller::call($request);
    }
}
