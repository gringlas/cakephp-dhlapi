<?php

namespace DHLApi\Lib;

use Cake\Http\Client;
use Cake\Http\Client\FormData;
use DHLApi\Lib\Requests\BookPickupDHLApiRequest;
use DHLApi\Lib\Requests\CapabilityCheckDHLApiRequest;

/**
 * Created by IntelliJ IDEA.
 * User: sebastiankoller
 * Date: 02.04.17
 * Time: 17:21
 */
class DHLApi
{
    private $apiRequest;

    public function __construct($requestType, $data, $config)
    {
        switch ($requestType) {
            case('bookPickup'):
                $this->apiRequest = new BookPickupDHLApiRequest($data, $config);
                break;
            case('capabilityCheck'):
                $this->apiRequest = new CapabilityCheckDHLApiRequest($data, $config);
                break;
            default:
                break;
        }
        $this->apiRequest->callApi();
    }


    public function isError()
    {
        return $this->apiRequest->getIsError();
    }


    public function getResponse()
    {
        return $this->apiRequest->getResponse();
    }

}
