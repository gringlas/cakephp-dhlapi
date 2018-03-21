<?php

namespace DHLApi\Lib\Requests;

use Cake\Chronos\Chronos;
use Cake\Http\Client;
use Cake\Log\Log;

/**
 * Created by IntelliJ IDEA.
 * User: sebastiankoller
 * Date: 02.04.17
 * Time: 17:18
 */
class CancelPickupDHLApiRequest extends DHLApiRequest
{

    private $orderNumber = 0;


    public function callApi()
    {
        $myClient = new Client();
        $response = $myClient->post($this->config['uri'], $this->getRequest(), [
            'headers' => [
                'Content-Type' => 'application/xml'
            ]
        ]);
        if ($response->getStatusCode() == 200) {
            $this->dhlResponseBody = $response->body();
            $xml = $response->xml;
            if (!empty($xml->xpath('//ActionNote')) && ($xml->xpath('//ActionNote')[0] == "Success")) {
                $this->orderNumber = $this->data['confirmationNumber'];
                $logMessage = "Pickup " . $this->data['confirmationNumber'] . " for requestor " . $this->data['requestorName'] . " has been deleleted";
            } else {
                $this->isError = true;
                $this->errorCode = (String)$xml->xpath('//ConditionCode')[0];
                $this->errorMessage = (String)$xml->xpath('//ConditionData')[0];
                $logMessage = $this->errorMessage . ". For requestor: " . $this->data['requestorName'];
                $this->errorRequestAndResponseToFile();
            }
        } else {
            $this->isError = true;
            $this->errorMessage = "DHL Server not available";
            $this->errorCode = "DHLNA";
            $logMessage = $this->errorMessage . ". For requestor: " . $this->data['requestorName'];
        }
        Log::info($logMessage, 'dhl');
    }


    public function getResponse()
    {
        $this->response = [
            'ordernumber' => $this->data['confirmationNumber']
        ];
        return parent::getResponse();
    }


    public function setRequest()
    {
        $now = Chronos::now();
        $this->request = '<?xml version="1.0" encoding="UTF-8"?>
<req:CancelPURequest xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
xsi:schemaLocation="http://www.dhl.com cancel-pickup-global-req_EA.xsd" schemaVersion="1.0">
    <Request>
        <ServiceHeader>
            <MessageTime>' . $now->format("Y-m-d\TH:m:s.000-08:00") . '</MessageTime>
            <MessageReference>' . $this->config['messageReference']. '</MessageReference>
            <SiteID>' . $this->config['siteID'] . '</SiteID>
            <Password>' . $this->config['password'] . '</Password>
        </ServiceHeader>
    </Request>
    <RegionCode>EU</RegionCode>	
	<ConfirmationNumber>'.$this->data['confirmationNumber'].'</ConfirmationNumber>
	<RequestorName>'.$this->data['requestorName'].'</RequestorName>
    <CountryCode>'.$this->data['countryCode'].'</CountryCode>
	<Reason>'.$this->data['reason'].'</Reason>	
	<PickupDate>'.$this->data['pickupDate'].'</PickupDate>
	<CancelTime>'. $now->format('H:i').'</CancelTime>    
</req:CancelPURequest>';
    }

}
