<?php

namespace App\Services\Keitaro\Requests\Groups;

use App\Services\Keitaro\Requests\AbstractRequest;

class CreateGroupRequest extends AbstractRequest
{
    protected $path = '/admin_api/v1/groups';
    protected $method = 'post';

    private $name;
    private $type;

    /**
     * @param $name
     * @param $type
     */
    public function __construct($name, $type)
    {
        $this->name = $name;
        $this->type = $type;
    }


    public function getRequestBody(array $extraInformation = null)
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
        ];
    }
}
