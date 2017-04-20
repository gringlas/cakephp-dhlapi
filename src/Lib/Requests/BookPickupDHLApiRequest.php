<?php
namespace DHLApi\Lib\Requests;
use Cake\Chronos\Chronos;
use Cake\Http\Client;

/**
 * Created by IntelliJ IDEA.
 * User: sebastiankoller
 * Date: 02.04.17
 * Time: 17:18
 */
class BookPickupDHLApiRequest extends DHLApiRequest
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
            if (empty($xml->xpath('//ActionStatus'))) {
                $this->orderNumber = (String) $xml->xpath('//ConfirmationNumber')[0];
            } else {
                $this->isError = true;
                $this->errorCode = (String) $xml->xpath('//ConditionCode')[0];
                $this->errorMessage = (String) $xml->xpath('//ConditionData')[0];
            }
        } else {
            $this->isError = true;
            $this->errorMessage = "DHL Server not available";
            $this->errorCode = "DHLNA";
        }
    }


    public function getResponse()
    {
        $this->response = [
            'ordernumber' => $this->orderNumber
        ];
        return parent::getResponse();
    }


    public function setRequest()
    {
        $now = Chronos::now();
        $this->request = '
<?xml version="1.0" encoding="UTF-8"?>
<req:BookPURequest xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com
 book-pickup-global-req.xsd" schemaVersion="1.0">
 <Request>
                <ServiceHeader>
                    <MessageTime>'.$now->format("Y-m-d\TH:m:s.000-08:00").'</MessageTime>
                    <MessageReference>Esteemed Courier Service of DHL</MessageReference>
                    <SiteID>'.$this->config['siteID'].'</SiteID>
                    <Password>'.$this->config['password'].'</Password>
                </ServiceHeader>
            </Request>
    <RegionCode>EU</RegionCode>
    <Requestor>
        <AccountType>D</AccountType>
        <AccountNumber>'.$this->config['accountNumber'].'</AccountNumber>
    </Requestor>
    <Place>
        <LocationType>B</LocationType>
        <CompanyName>'.$this->data['companyname'].'</CompanyName>
        <Address1>'.$this->data['address1'].'</Address1>
        <Address2></Address2>
        <PackageLocation>'.$this->data['packagelocation'].'</PackageLocation>
        <City>'.$this->data['city'].'</City>
        <CountryCode>DE</CountryCode>
        <PostalCode>'.$this->data['postalcode'].'</PostalCode>
    </Place>
    <Pickup>
        <PickupDate>'.$this->data['pickupdate'].'</PickupDate>
        <ReadyByTime>'.$this->data['readybytime'].'</ReadyByTime>
        <CloseTime>'.$this->data['closetime'].'</CloseTime>
    </Pickup>
    <PickupContact>
        <PersonName>'.$this->data['personname'].'</PersonName>
        <Phone>'.$this->data['phone'].'</Phone>
    </PickupContact>
    <ShipmentDetails>
        <AccountType>D</AccountType>
        <AccountNumber>'.$this->config['accountNumber'].'</AccountNumber>
        <BillToAccountNumber>100000000</BillToAccountNumber>
        <AWBNumber>7520067111</AWBNumber>
        <NumberOfPieces>1</NumberOfPieces>
        <Weight>10</Weight>
        <WeightUnit>L</WeightUnit>
        <GlobalProductCode>D</GlobalProductCode>
        <DoorTo>DD</DoorTo>
        <DimensionUnit>I</DimensionUnit>
        <InsuredAmount>999999.99</InsuredAmount>
        <InsuredCurrencyCode>EUR</InsuredCurrencyCode>
        <Pieces>
            <Weight>3</Weight>
            <Width>47</Width>
            <Height>38</Height>
            <Depth>2</Depth>
        </Pieces>
        <SpecialService>S</SpecialService>
        <SpecialService>I</SpecialService>
    </ShipmentDetails>
</req:BookPURequest>';
    }

 }
