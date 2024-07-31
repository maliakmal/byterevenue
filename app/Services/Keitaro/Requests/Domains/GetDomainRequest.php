<?php

namespace App\Services\Keitaro\Requests\Domains;

use App\Services\Keitaro\Requests\AbstractRequest;

class GetDomainRequest extends AbstractRequest
{
    protected $path = '/admin_api/v1/domains/:id';
    protected $method = 'get';

    private $id;


    /**
     * @param $id
     */
    public function __construct($id)
    {
        $this->id = $id;

        $this->path = str_replace(':id', $id, $this->path);
    }


    public function getRequestBody(array $extraInformation = null)
    {
        return [
            'id'     => $this->id,
        ];
    }
}
