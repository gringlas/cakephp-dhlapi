<?php
/**
 * Created by IntelliJ IDEA.
 * User: sebastiankoller
 * Date: 05.04.17
 * Time: 15:05
 */

namespace DHLApi\Lib\Requests;


class CapabilityCheckDHLApiRequest extends DHLApiRequest
{
    public function callApi()
    {
        $myClient = new Client();
        $response = $myClient->post('https://xmlpitest-ea.dhl.com/XMLShippingServlet', $this->setRequest(), [
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


    protected function mapData($data)
    {
        return [
            'city' => $data['collection']->city,
            'postalcode' => $data['collection']->zip,
            'pickupdate' => $data['collection']->date->format('Y-m-d'),
            'readybytime' => $data['collection']->timefrom,
            'closetime' => $data['collection']->timeto,
        ];
    }


    public function getResponse()
    {

    }

    public function setRequest()
    {
        $this->request = '
<?xml version="1.0" encoding="UTF-8"?>
<p:DCTRequest xmlns:p="http://www.dhl.com" xmlns:p1="http://www.dhl.com/datatypes" xmlns:p2="http://www.dhl.com/DCTRequestdatatypes" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com DCT-req.xsd ">
  <GetCapability>'
        .$this->setRequestAttribute().
    '<From>
      <CountryCode>DE</CountryCode>
      <Postalcode>'.$this->data['postalcode'].'</Postalcode>
	  <City>'.$this->data['city'].'</City>
    </From>
    <BkgDetails>
      <PaymentCountryCode>DE</PaymentCountryCode>
      <Date>'.$this->data['pickupdate'].'</Date>
      <ReadyTime>PT15H00M</ReadyTime>
      <ReadyTimeGMTOffset>+01:00</ReadyTimeGMTOffset>
      <DimensionUnit>CM</DimensionUnit>
      <WeightUnit>KG</WeightUnit>
      <Pieces>
        <Piece>
          <PieceID>1</PieceID>
          <Height>30</Height>
          <Depth>20</Depth>
          <Width>10</Width>
          <Weight>10.0</Weight>
        </Piece>
      </Pieces>      
      <IsDutiable>N</IsDutiable>
      <NetworkTypeCode>TD</NetworkTypeCode>
    </BkgDetails>
    <To>
      <CountryCode>DE</CountryCode>
      <Postalcode>45163</Postalcode>
	  <City>Essen</City>
    </To>
   <Dutiable>
      <DeclaredCurrency>EUR</DeclaredCurrency>
      <DeclaredValue>102.00</DeclaredValue>
    </Dutiable>
  </GetCapability>
</p:DCTRequest>';
    }


}