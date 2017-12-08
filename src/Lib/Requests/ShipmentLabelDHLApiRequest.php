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
<?xml version="1.0" encoding="utf-8"?>
<req:ShipmentRequest xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com ship-val-global-req.xsd" schemaVersion="5.0">
  <Request>
    <ServiceHeader>
      <MessageTime>' . $now->format("Y-m-d\TH:m:s.000-08:00") . '</MessageTime>
                   
      <MessageReference>1234567890123456789012345678901</MessageReference>
			<SiteID>' . $this->config['siteID'] . '</SiteID>
			<Password>' . $this->config['password'] . '</Password>
    </ServiceHeader>
  </Request>
  <RegionCode>AM</RegionCode>
  <RequestedPickupTime>Y</RequestedPickupTime>
  <NewShipper>Y</NewShipper>
  <LanguageCode>en</LanguageCode>
  <PiecesEnabled>Y</PiecesEnabled>
  <Billing>
    <ShipperAccountNumber>845818859</ShipperAccountNumber>
    <ShippingPaymentType>S</ShippingPaymentType>
    <BillingAccountNumber>845818859</BillingAccountNumber>
    <DutyPaymentType>S</DutyPaymentType>
    <DutyAccountNumber>845818859</DutyAccountNumber>
  </Billing>
  <Consignee>
    <CompanyName>IBM Bruse Pte Ltd</CompanyName>
    <AddressLine>9 Business Park Central 1</AddressLine>
    <AddressLine>3th Floor</AddressLine>
    <AddressLine>The IBM Place</AddressLine>
    <City>Brussels</City>
    <PostalCode>1060</PostalCode>
    <CountryCode>BE</CountryCode>
    <CountryName>Belgium</CountryName>
    <Contact>
      <PersonName>Mrs Orlander</PersonName>
      <PhoneNumber>506-851-2271</PhoneNumber>
      <PhoneExtension>7862</PhoneExtension>
      <FaxNumber>506-851-7403</FaxNumber>
      <Telex>506-851-7121</Telex>
      <Email>c_orlander@gc.ca</Email>
    </Contact>
  </Consignee>
  <Commodity>
    <CommodityCode>cc</CommodityCode>
    <CommodityName>cn</CommodityName>
  </Commodity>
  <Dutiable>
    <DeclaredValue>1200.00</DeclaredValue>
    <DeclaredCurrency>USD</DeclaredCurrency>
    <ScheduleB>3002905110</ScheduleB>
    <ExportLicense>D123456</ExportLicense>
    <ShipperEIN>112233445566</ShipperEIN>
    <ShipperIDType>S</ShipperIDType>
    <ImportLicense>ImportLic</ImportLicense>
    <ConsigneeEIN>ConEIN2123</ConsigneeEIN>
    <TermsOfTrade>FOB</TermsOfTrade>
	<Filing>
		<ITN>X10294857392019</ITN>
	</Filing>
  </Dutiable>
  <Reference>
    <ReferenceID>13WKM110103004</ReferenceID>
    <ReferenceType>St</ReferenceType>
  </Reference>
  <ShipmentDetails>
    <NumberOfPieces>2</NumberOfPieces>
    <Pieces>
      <Piece>
        <PieceID>1</PieceID>
        <PackageType>EE</PackageType>
        <Weight>19.78</Weight>
        <DimWeight>1200.2</DimWeight>
        <Width>100</Width>
        <Height>200</Height>
        <Depth>300</Depth>
      </Piece>
    </Pieces>
    <Weight>19.78</Weight>
    <WeightUnit>L</WeightUnit>
    <GlobalProductCode>P</GlobalProductCode>
    <LocalProductCode>P</LocalProductCode>
    <Date>2013-09-05</Date>
    <Contents>E000717E12 - C/C:2101012200121</Contents>
    <DoorTo>DD</DoorTo>
    <DimensionUnit>I</DimensionUnit>
	<InsuredAmount>50.00</InsuredAmount>
    <PackageType>EE</PackageType>
    <IsDutiable>Y</IsDutiable>
    <CurrencyCode>USD</CurrencyCode>
  </ShipmentDetails>
  <Shipper>
    <ShipperID>845818859</ShipperID>
    <CompanyName>Sony Computer Entertainment US</CompanyName>
    <RegisteredAccount>845818859</RegisteredAccount>
    <AddressLine>1210 S Pine Island Road</AddressLine>
    <City>ERLANGER</City>
    <Division>US</Division>
    <DivisionCode>US</DivisionCode>
    <PostalCode>41018</PostalCode>
    <CountryCode>US</CountryCode>
    <CountryName>United States</CountryName>
        <Contact>
            <PersonName>Kazuo Hirai</PersonName>
            <PhoneNumber>11234-325423</PhoneNumber>
            <PhoneExtension>45232</PhoneExtension>
            <FaxNumber>11234325423</FaxNumber>
            <Telex>454586</Telex>
            <Email>help@tw.playstation.com</Email>
        </Contact>
  </Shipper>
  <SpecialService>
    <SpecialServiceType>A</SpecialServiceType>
  </SpecialService>
  <SpecialService>
    <SpecialServiceType>II</SpecialServiceType>
  </SpecialService>
  <SpecialService>
    <SpecialServiceType>DD</SpecialServiceType>
  </SpecialService>
  <EProcShip>N</EProcShip>
		<DocImages> 
	</DocImages> 
	<LabelImageFormat>PDF</LabelImageFormat>
	<RequestArchiveDoc>Y</RequestArchiveDoc>
	<Label>
		<LabelTemplate>8X4_A4_PDF</LabelTemplate>
		<Logo>Y</Logo>
		<CustomerLogo>
			<LogoImage>iVBORw0KGgoAAAANSUhEUgAAAFwAAABcCAMAAADUMSJqAAAAZlBMVEX///8AAADu7u62trb5+fklJSXy8vLe3t7m5uY6OjrKysr8/Pz19fXS0tKWlpbDw8NgYGBsbGxycnJDQ0NTU1OLi4tJSUkYGBhnZ2eurq54eHiEhIQgICAJCQlYWFgqKiozMzOgoKCyLgE0AAAEHklEQVRogc1Z2ZKqMBA1gAIii4IiKKP8/09eIBCSXgAFq+55mamkOXZ6h+x2/w2Ky++4YyGiX3GHokHxG24vb8nF8RfczktIuD8gT8UAa3PuRHGLl7OO6hCDhYfQkIFND0pPIk6BcrUwAMP9el3OHQkPrCQmeQqfyG6HZdTOn0DHtExyFDDOU4RLuL29KPFqpHPf8X6TYPY8t1uJJ3FEZ1Jx+euzpaFNcVMFt+zi+jpy37r1Gmja5AFxYsh9M1asvajaeuKN5C2r23CF6NHzFHdjE/CM4w+h5yvyJkzt9m9lxtRlmt1qS1OirwQ3ZQnl0kz9bzrnRGXACB95S8V32hZziWi0/9vItavcpVH2eoEVifNh+C+uoW+Hg8ttktvGe6GWmcosGndmFvdOl4oqmlbrTJEH5mqcK6YK/MVpEw4+QZBKogrUFAMGe1iAdoGMKNwJpVGo5CtpblS8dirT4K+ent2yT/WBC6k3JSntguKxdxeZwEFKkCObtLCe1PmHOKMrm4u5mQkjk7tGIqr0Yzr7GXLfaDllQeNc/VrONPYQknNNsxbYvEW/5gfMQyAe35ycqhGau4eKR8V/B9skZ/vxcZAYnacc9sc95FQGOdsvFfmoZjRLvrvr3DkrpsjHCuPPkxt2WSI2uHwMY9bmepdr6iIrNg4JQ5aOP/fmp0CdnJ/exoQYSo9WO8ik7vDWyPkh+oaotBV+bNIjnR3dPC2oZB+xtBV+rtFLIysUC0illyU+Dsol5LoGsgIYUcYa/byA/KCnmqyMxozJ2kVzjDgxMoXOJCPPeGmouGDMNSEuWl46k3S72SO5BNFlmDgH1a0rAKAT0Gc+6iKMAiaPdN/dXEvIBw3HUI0fmFf0OQPI6fa4x0pNna2FS5iF9Bd4kogpK4c0nQZoLHlixTJTIkcFwMkgi3Qonqh82KdjKPEAAgG0rehD8YrXc7OAuRWSMKPR8wmOLuxqvGGqFmJuk70g9kXaTQhoKJGoe7s6xMlalENCxITaYng98si9BklU2DU5hUpc6vhYPF7Mrjz7Yc9sr0NBxtlG6LMF5u0mGMZOlLhbYJhSLCrU1kJViF8YXaUhmQTr8FQd7TAv/Cm0LwzJvPSH0KrD9vGil7Wtk9R49bDn5T+C0RACrvx8B/DBaFvVYRemXsG/BfrSxbSMr4Ab/Ha1kXhLdbYKxxc1EG5lGHpMjeYfXADum+sWJebNcOufVL8Gf1tiveefnsbUJ3pvZRmY/kDvrQrIubs1a4Xu8xcLzm2ehcaiW7vvCgH++kojRq8g8zhzb74IFvGiMInqo2vS+CO/lh9eNAbLK0266HLLxGkZffrl1W5gzzW//PKF1gpuxIf9824vvEvk4RUlPsDrHIWrmSWCg2tH1/Ke/CX3xzUqQmvlTfEG+AcLby5sVddMEAAAAABJRU5ErkJggg==</LogoImage>
			<LogoImageFormat>PNG</LogoImageFormat>
		</CustomerLogo>
	</Label>
</req:ShipmentRequest>';
    }
}