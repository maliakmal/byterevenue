<?php

namespace App\Services\Keitaro\Requests\Flows;

use App\Services\Keitaro\Requests\AbstractRequest;

class CreateFlowRequest extends AbstractRequest
{
    protected $path = '/admin_api/v1/streams';
    protected $method = 'post';

    private $campaignID;
    private $schema;
    private $type;
    private $name;
    private $actionType;
    private $position;
    private $weight;
    private $actionOption;
    private $comments;
    private $state;
    private $collectClicks;
    private $filterOr;
    private $filters;
    private $triggers;
    private $landings;
    private $offers;

    /**
     * @param $campaignID
     * @param $schema
     * @param $type
     * @param $name
     * @param $actionType
     * @param $position
     * @param $weight
     * @param $actionOption
     * @param $comments
     * @param $state
     * @param $collectClicks
     * @param $filterOr
     * @param $filters
     * @param $triggers
     * @param $landings
     * @param $offers
     */
    public function __construct($campaignID, $schema, $type, $name, $actionType,
                                $position = null, $weight = null, $actionOption = null,
                                $comments = null, $state = null, $collectClicks = null,
                                $filterOr = null, $filters = null, $triggers = null,
                                $landings = null, $offers = null)
    {
        $this->campaignID = $campaignID;
        $this->schema = $schema;
        $this->type = $type;
        $this->name = $name;
        $this->actionType = $actionType;
        $this->position = $position;
        $this->weight = $weight;
        $this->actionOption = $actionOption;
        $this->comments = $comments;
        $this->state = $state;
        $this->collectClicks = $collectClicks;
        $this->filterOr = $filterOr;
        $this->filters = $filters;
        $this->triggers = $triggers;
        $this->landings = $landings;
        $this->offers = $offers;
    }


    public function getRequestBody(array $extraInformation = null)
    {
        return [
          "campaign_id"     => $this->campaignID,
          "type"            => $this->type,
          "name"            => $this->name,
          "position"        => $this->position,
          "weight"          => $this->weight,
          "action_options"  => $this->actionOption,
          "comments"        => $this->comments,
          "state"           => $this->state,
          "action_type"     => $this->actionType,
          "schema"          => $this->schema,
          "collect_clicks"  => $this->collectClicks,
          "filter_or"       => $this->filterOr,
          "filters"         => $this->filters,
          "triggers"        => $this->triggers,
          "landings"        => $this->landings,
          "offers"          => $this->offers,
        ];
    }
}
