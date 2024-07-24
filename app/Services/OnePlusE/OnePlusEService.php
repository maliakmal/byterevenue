<?php

namespace App\Services\OnePlusE;

use App\Services\OnePlusE\Requests\Campaign\UploadCampaignRequest;
use App\Services\OnePlusE\Requests\CampAnalytic\UploadAcceptedNumbersRequest;
use App\Services\OnePlusE\Requests\CampAnalytic\UploadBlockListedNumbersRequest;
use App\Services\OnePlusE\Requests\ESim\DeleteProfileRequest;
use App\Services\OnePlusE\Requests\ESim\DisableProfileRequest;
use App\Services\OnePlusE\Requests\ESim\EnableProfileRequest;
use App\Services\OnePlusE\Requests\ESim\ListProfilesRequest;
use App\Services\OnePlusE\Requests\ESim\RemoveProfilesRequest;
use App\Services\OnePlusE\Requests\ESim\ResetRequest;
use App\Services\OnePlusE\Requests\ESim\RestartRequest;
use App\Services\OnePlusE\Requests\ESim\RunESIMScriptRequest;
use App\Services\OnePlusE\Requests\QMI\ChangeDeviceIDRequest;
use App\Services\OnePlusE\Requests\QMI\GetDeviceIDRequest;
use App\Services\OnePlusE\Requests\QMI\SetDeviceIDRequest;
use App\Services\OnePlusE\Requests\SendMMS\GetAcceptsCountRequest;
use App\Services\OnePlusE\Requests\SendMMS\SendCampaignBBCRequest;
use App\Services\OnePlusE\Requests\SendMMS\SendCampaignRequest;
use App\Services\OnePlusE\Requests\SendMMS\SendSingleRequest;
use App\Services\OnePlusE\Requests\SendMMS\StopSendingRequest;

class OnePlusEService
{
    public function uploadCampaign($campaign_file)
    {
        $caller = new OnePlusECaller();
        $request = new UploadCampaignRequest($campaign_file);
        return $caller->call($request);
    }

    public function uploadAcceptedNumbersCampaignAnalytic($file, $camp_name)
    {
        $caller = new OnePlusECaller();
        $request = new UploadAcceptedNumbersRequest($file, $camp_name);
        return $caller->call($request);
    }

    public function uploadBlockListedNumbersCampaignAnalytic($file)
    {
        $caller = new OnePlusECaller();
        $request = new UploadBlockListedNumbersRequest($file);
        return $caller->call($request);
    }

    public function deleteProfileESIM($ICCID)
    {
        $caller = new OnePlusECaller();
        $request = new DeleteProfileRequest($ICCID);
        return $caller->call($request);
    }
    public function disableProfileESIM($ICCID)
    {
        $caller = new OnePlusECaller();
        $request = new DisableProfileRequest($ICCID);
        return $caller->call($request);
    }

    public function enableProfileESIM($ICCID)
    {
        $caller = new OnePlusECaller();
        $request = new EnableProfileRequest($ICCID);
        return $caller->call($request);
    }

    public function listProfileESIM()
    {
        $caller = new OnePlusECaller();
        $request = new ListProfilesRequest();
        return $caller->call($request);
    }
    public function removeProfileESIM($deviceID)
    {
        $caller = new OnePlusECaller();
        $request = new RemoveProfilesRequest($deviceID);
        return $caller->call($request);
    }
    public function resetESIM()
    {
        $caller = new OnePlusECaller();
        $request = new ResetRequest();
        return $caller->call($request);
    }

    public function restartESIM()
    {
        $caller = new OnePlusECaller();
        $request = new RestartRequest();
        return $caller->call($request);
    }
    public function runESIMScriptESIM($esimCode)
    {
        $caller = new OnePlusECaller();
        $request = new RunESIMScriptRequest($esimCode);
        return $caller->call($request);
    }
    public function changeDeviceIDQMI($ipType, $apn, $deviceID = null)
    {
        $caller = new OnePlusECaller();
        $request = new ChangeDeviceIDRequest($ipType, $apn, $deviceID);
        return $caller->call($request);
    }
    public function getDeviceIDQMI($ipType, $apn, $deviceID = null)
    {
        $caller = new OnePlusECaller();
        $request = new GetDeviceIDRequest($ipType, $apn, $deviceID);
        return $caller->call($request);
    }
    public function setDeviceIDQMI($deviceID = null)
    {
        $caller = new OnePlusECaller();
        $request = new SetDeviceIDRequest($deviceID);
        return $caller->call($request);
    }
    public function getAcceptsCountMMS()
    {
        $caller = new OnePlusECaller();
        $request = new GetAcceptsCountRequest();
        return $caller->call($request);
    }
    public function sendCampaignBBCMMS($headers = null, $bcc_count = null)
    {
        $caller = new OnePlusECaller();
        $request = new SendCampaignBBCRequest($headers, $bcc_count);
        return $caller->call($request);
    }
    public function sendCampaignMMS($headers = null, $bcc_count = null)
    {
        $caller = new OnePlusECaller();
        $request = new SendCampaignRequest($headers, $bcc_count);
        return $caller->call($request);
    }
    public function sendSingleMMS($number = null, $text = null)
    {
        $caller = new OnePlusECaller();
        $request = new SendSingleRequest($number, $text);
        return $caller->call($request);
    }
    public function stopSendingMMS()
    {
        $caller = new OnePlusECaller();
        $request = new StopSendingRequest();
        return $caller->call($request);
    }
}
