<?php

namespace App\Services\Clicks;

use App\Services\Keitaro\KeitaroCaller;
use App\Services\Keitaro\Requests\Clicks\GetClicksRequest;

class ClickService
{
    /**
     * @param $from
     * @param $end
     * @param $limit
     * @param $offset
     * @param $timezone
     * @param $interval
     * @param $columns
     * @param $filters
     * @param $sort
     * @return mixed
     */
    public function getClicksOnKeitaro($from, $end, $limit, $offset, $timezone = null, $interval = null, $columns = null, $filters = null, $sort = null)
    {
        $request = new GetClicksRequest($from, $end, $limit, $offset, $timezone, $interval, $columns, $filters, $sort);

        return KeitaroCaller::call($request);
    }
}
