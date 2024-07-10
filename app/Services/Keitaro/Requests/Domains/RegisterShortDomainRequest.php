<?php

namespace App\Services\Keitaro\Requests\Domains;

use App\Services\Keitaro\Requests\AbstractRequest;

class RegisterShortDomainRequest extends AbstractRequest
{
    protected $path = '/admin_api/v1/domains/register';
    protected $method = 'post';

    private $name;
    private $default_campaign_id;
    private $group_id;
    private $catch_not_found;
    private $notes;
    private $ssl_redirect;
    private $allow_indexing;
    private $admin_dashboard;


    /**
     * @param $name
     * @param $default_campaign_id
     * @param $group_id
     * @param $catch_not_found
     * @param $notes
     * @param $ssl_redirect
     * @param $allow_indexing
     * @param $admin_dashboard
     */
    public function __construct($name, $default_campaign_id = null, $group_id = null, $catch_not_found = null, $notes = null,
                                $ssl_redirect = null, $allow_indexing = null, $admin_dashboard = null)
    {
        $this->name = $name;
        $this->default_campaign_id = $default_campaign_id;
        $this->group_id = $group_id;
        $this->catch_not_found = $catch_not_found;
        $this->notes = $notes;
        $this->ssl_redirect = $ssl_redirect;
        $this->allow_indexing = $allow_indexing;
        $this->admin_dashboard = $admin_dashboard;
    }


    public function getRequestBody(array $extraInformation = null)
    {
        return [
            'name'                => $this->name,
            'default_campaign_id' => $this->default_campaign_id,
            'group_id'            => $this->group_id,
            'catch_not_found'     => $this->catch_not_found,
            'notes'               => $this->notes,
            'ssl_redirect'        => $this->ssl_redirect,
            'allow_indexing'      => $this->allow_indexing,
            'admin_dashboard'     => $this->admin_dashboard,
        ];
    }
}
