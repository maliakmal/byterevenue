<?php

namespace App\Repositories\Model\CampaignShortUrl;

use App\Models\CampaignShortUrl;
use App\Repositories\Contract\CampaignShortUrl\CampaignShortUrlRepositoryInterface;
use App\Repositories\Model\BaseRepository;

class CampaignShortUrlRepository extends BaseRepository implements CampaignShortUrlRepositoryInterface
{
    public function __construct(CampaignShortUrl $model)
    {
        $this->model = $model;
    }

    public function findWithCampaignIDUrlID($campaignID, $url)
    {
        return $this->model->where('campaign_id', $campaignID)->where('url_shortener', 'like', $url.'%')->first();
    }

    /**
     * @param array $search
     * @return mixed
     */
    public function search(array $params)
    {
        $q = $this->model->query();
        if(isset($params['domain']) && !empty($params['domain'])){
            $q = $q->where('url_shortener', 'like', '%'.trim($params['domain']).'%');
        }
        if(isset($params['url_shortener_is_null'])){
            $q = $q->whereNull('url_shortener_id');
        }

        return $q->get();
    }

}
