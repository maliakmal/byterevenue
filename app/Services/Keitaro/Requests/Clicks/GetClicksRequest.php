<?php

namespace App\Services\Keitaro\Requests\Clicks;

use App\Services\Keitaro\Requests\AbstractRequest;

class GetClicksRequest extends AbstractRequest
{
    protected $path = '/admin_api/v1/clicks/log';
    protected $method = 'post';

    private $from;
    private $end;
    private $timezone;
    private $interval;
    private $limit;
    private $offset;
    private $columns;
    private $filters;
    private $sort;

    /**
     * @param $from
     * @param $end
     * @param $timezone
     * @param $interval
     * @param $limit
     * @param $offset
     * @param $columns
     * @param $filters
     * @param $sort
     */
    public function __construct($from, $end, $limit, $offset, $timezone = null, $interval = null, $columns = null, $filters = null, $sort = null)
    {
        $this->from = $from;
        $this->end = $end;
        $this->timezone = $timezone;
        $this->interval = $interval;
        $this->limit = $limit;
        $this->offset = $offset;
        $this->columns = $columns;
        $this->filters = $filters;
        $this->sort = $sort;
    }


    public function getRequestBody(array $extraInformation = null)
    {
        $data = [
            "range" => [
                "from" => $this->from,
                "end" => $this->end,
                "timezone" => $this->timezone,
                "interval" => $this->interval,
            ],
            "limit" => $this->limit,
            "offset" => $this->offset,
        ];
        if(!empty($this->columns) && count($this->columns) > 0){
            $data['columns'] = $this->columns;
        }
        if(!empty($this->filters) && count($this->filters) > 0){
            $data['filters'] = $this->filters;
        }
        if(!empty($this->sort) && count($this->sort) > 0){
            $data['sort'] = $this->sort;
        }
        return $data;
    }
}
