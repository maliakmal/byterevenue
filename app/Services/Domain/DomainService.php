<?php

namespace App\Services\Domain;

use App\Services\Keitaro\KeitaroCaller;
use App\Services\Keitaro\Requests\Domains\GetDomainRequest;

class DomainService
{
    public function isDomainPropaginated($assetID)
    {
        $request = new GetDomainRequest($assetID);

        $response  = KeitaroCaller::call($request);
        return $response['network_status'] == 'active';
    }
}
