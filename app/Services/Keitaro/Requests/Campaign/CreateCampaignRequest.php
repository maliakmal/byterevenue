<?php

namespace App\Services\Keitaro\Requests\Campaign;

use App\Services\Keitaro\Requests\AbstractRequest;

class CreateCampaignRequest extends AbstractRequest
{
    protected $path = '/admin_api/v1/campaigns';
    protected $method = 'post';

    private $alias;
    private $name;
    private $type;
    private $cookies_ttl;
    private $state;
    private $cost_type;
    private $cost_value;
    private $cost_currency;
    private $cost_auto;
    private $group_id;
    private $bind_visitors;
    private $traffic_source_id;
    private $parameters;
    private $token;
    private $domain_id;
    private $postbacks;
    private $notes;

    /**
     * @param $alias
     * @param $name
     * @param $type
     * @param $cookies_ttl
     * @param $state
     * @param $cost_type
     * @param $cost_value
     * @param $cost_currency
     * @param $cost_auto
     * @param $group_id
     * @param $bind_visitors
     * @param $traffic_source_id
     * @param $parameters
     * @param $token
     * @param $domain_id
     * @param $postbacks
     * @param $notes
     */
    public function __construct($alias, $name, $token, $type = null, $cookies_ttl = null, $state = null,
                                $cost_type = null, $cost_value = null, $cost_currency = null, $cost_auto = null,
                                $group_id = null, $bind_visitors = null, $traffic_source_id = null, $parameters = null,
                                $domain_id = null, $postbacks = null, $notes = null)
    {
        $this->alias = $alias;
        $this->name = $name;
        $this->type = $type;
        $this->cookies_ttl = $cookies_ttl;
        $this->state = $state;
        $this->cost_type = $cost_type;
        $this->cost_value = $cost_value;
        $this->cost_currency = $cost_currency;
        $this->cost_auto = $cost_auto;
        $this->group_id = $group_id;
        $this->bind_visitors = $bind_visitors;
        $this->traffic_source_id = $traffic_source_id;
        $this->parameters = $parameters;
        $this->token = $token;
        $this->domain_id = $domain_id;
        $this->postbacks = $postbacks;
        $this->notes = $notes;
    }


    public function getRequestBody(array $extraInformation = null)
    {
        return [
            'alias'             => $this->alias,
            'name'              => $this->name,
            'type'              => $this->type,
            'cookies_ttl'       => $this->cookies_ttl,
            'state'             => $this->state,
            'cost_type'         => $this->cost_type,
            'cost_value'        => $this->cost_value,
            'cost_currency'     => $this->cost_currency,
            'cost_auto'         => $this->cost_auto,
            'group_id'          => $this->group_id,
            'bind_visitors'     => $this->bind_visitors,
            'traffic_source_id' => $this->traffic_source_id,
            'parameters'        => $this->parameters,
            'token'             => $this->token,
            'domain_id'         => $this->domain_id,
            'postbacks'         => $this->postbacks,
            'notes'             => $this->notes,

        ];
    }
}
