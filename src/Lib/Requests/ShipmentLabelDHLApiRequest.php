<?php
/**
 * Created by IntelliJ IDEA.
 * User: sebastiankoller
 * Date: 08.12.17
 * Time: 14:32
 */

namespace DHLApi\Lib\Requests;


use Cake\Chronos\Chronos;
use Cake\Http\Client;

class ShipmentLabelDHLApiRequest extends DHLApiRequest
{

    private $label = '';

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
                $this->label = $xml->xpath('//LabelImage')[0]->OutputImage;
            } else {
                $this->isError = true;
                $this->errorCode = (String)$xml->xpath('//ConditionCode')[0];
                $this->errorMessage = (String)$xml->xpath('//ConditionData')[0];
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
            'label' => $this->label
        ];
        return parent::getResponse();
    }


    public function setRequest()
    {
        $now = Chronos::now();
        $this->request = '
<?xml version="1.0" encoding="UTF-8"?>
<req:ShipmentValidateRequestEA xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xsi:schemaLocation="http://www.dhl.com 
ship-val-req_EA.xsd">
<Request>
    <ServiceHeader>
        <MessageTime>' . $now->format("Y-m-d\TH:m:s.000-08:00") . '</MessageTime>
        <MessageReference>' . $this->config['messageReference'] . '</MessageReference>
        <SiteID>' . $this->config['siteID'] . '</SiteID>
        <Password>' . $this->config['password'] . '</Password>
    </ServiceHeader>
</Request>
<NewShipper>N</NewShipper>
<LanguageCode>de</LanguageCode>
<PiecesEnabled>Y</PiecesEnabled>
<Billing>
    <ShipperAccountNumber>' . $this->config['accountNumber'] . '</ShipperAccountNumber>
    <ShippingPaymentType>S</ShippingPaymentType>
    <BillingAccountNumber>' . $this->config['accountNumber'] . '</BillingAccountNumber>
    <DutyPaymentType>R</DutyPaymentType>
</Billing>
<Consignee>
    <CompanyName>' . $this->data['praxis'] . '</CompanyName>
    <AddressLine>' . $this->data['street'] . '</AddressLine>
    <City>' . $this->data['city'] . '</City>
    <Division>' . $this->data['district'] . '</Division>
    <PostalCode>' . $this->data['zip'] . '</PostalCode>
    <CountryCode>DE</CountryCode>
    <CountryName>Germany</CountryName>
    <Contact>
        <PersonName>' . $this->data['contact'] . '</PersonName>
        <PhoneNumber>' . $this->data['phone'] . '</PhoneNumber>
        <PhoneExtension>na</PhoneExtension>
        <FaxNumber>na</FaxNumber>
        <Telex>na</Telex>
    </Contact>
</Consignee>
<ShipmentDetails>
    <NumberOfPieces>' . $this->data['cases'] . '</NumberOfPieces>
    <CurrencyCode>EUR</CurrencyCode>
    <Pieces>
        <Piece>
            <PieceID>1</PieceID>
            <PackageType>EE</PackageType>
            <Weight>2.0</Weight>
            <Depth>10</Depth>
            <Width>20</Width>
            <Height>30</Height>
        </Piece>
    </Pieces>
    <PackageType>DC</PackageType>
    <Weight>2.0</Weight>
    <DimensionUnit>C</DimensionUnit>
    <WeightUnit>K</WeightUnit>
    <GlobalProductCode>N</GlobalProductCode>
    <LocalProductCode>E</LocalProductCode>
    <DoorTo>DD</DoorTo>
    <Date>' . $this->data['date'] . '</Date>
    <Contents>Zahnabdruecke</Contents>
</ShipmentDetails>
<Shipper>
    <ShipperID>' . $this->config['accountNumber'] . '</ShipperID>
    <CompanyName>unique company ptd limit 1234567890</CompanyName>
    <AddressLine>XYZ-Str. 123</AddressLine>
    <City>Koeln</City>
    <Division></Division>
    <PostalCode>51113</PostalCode>
    <CountryCode>DE</CountryCode>
    <CountryName>Germany</CountryName>
    <Contact>
        <PersonName>shipper unique ptd limit 1234567890</PersonName>
        <PhoneNumber>11234-325423</PhoneNumber>
        <PhoneExtension>45232</PhoneExtension>
        <FaxNumber>11234325423</FaxNumber>
        <Telex>454586</Telex>
    </Contact>
</Shipper>
<EProcShip>N</EProcShip>
<LabelImageFormat>PDF</LabelImageFormat>
</req:ShipmentValidateRequestEA>';
        $this->request = utf8_encode($this->request);
    }
}