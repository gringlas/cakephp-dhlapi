<?php

namespace App\Test\TestCase\Lib;

use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\TestSuite\TestCase;
use DHLApi\Lib\DHLApi;
use DHLApi\Lib\Requests\BookPickupDHLApiRequest;
use DHLApi\Lib\Requests\CancelPickupDHLApiRequest;
use DHLApi\Lib\Requests\CapabilityCheckDHLApiRequest;
use DHLApi\Lib\Requests\ShipmentLabelDHLApiRequest;

/**
 * Created by IntelliJ IDEA.
 * User: sebastiankoller
 * Date: 02.04.17
 * Time: 17:44
 */
class DHLApiTest extends TestCase
{

    private $config;


    public function setUp()
    {
        $this->config = [
            'uri' => 'https://xmlpitest-ea.dhl.com/XMLShippingServlet',
            'siteID' => 'ImexDental',
            'password' => '5d4LixjNwQ',
            'accountNumber' => '144053708',
            'messageReference' => 'ImexDental PHPUNITTest' . time()
        ];
        $this->logPath = LOGS . 'api_errors' . DS;
        parent::setUp();
    }


    private function doPickup(FrozenDate $pickupdate, $readytime, $closetime)
    {
        $data = [
            'companyname' => "Sebs PHPUNIT Test 1",
            'address1' => "Kreuzstr. 1-3",
            'packagelocation' => 'Praxis',
            'city' => "Mülheim",
            'postalcode' => "45468",
            'pickupdate' => $pickupdate->format('Y-m-d'),
            'readybytime' => $readytime,
            'closetime' => $closetime,
            'personname' => "Sebastian Köller",
            'phone' => "020888387559",
            'cases' => 1
        ];
        $BookPickupDHLApiRequest = new BookPickupDHLApiRequest($data, $this->config);
        $BookPickupDHLApiRequest->callApi();
        return $BookPickupDHLApiRequest->getResponse();
    }


    public function testBookpickup()
    {
        $date = new FrozenDate();
        $result = json_encode($this->doPickup($date->modify("next Monday"), "17:00", "19:30"));
        $this->assertRegExp('/ordernumber":"[\d]+/i', $result);
    }


    public function testBookpickupWithError()
    {
        $frozenTime = new FrozenTime();
        $date = new FrozenDate();
        $result = $this->doPickup($date->modify("next Monday"), "18:00", "20:00");
        $this->assertCount(1, $result['errorMessages'], "Die Abholung ist ungültig.");

        $requestErrorFilename = $this->logPath  . 'request' . DS . "BookPickupDHLApiRequest_" . $frozenTime->format('Y-m-d_H:i') . '.xml';
        $this->assertFileExists($requestErrorFilename, "Eine Datei mit dem fehlerhaften Request wurde angelegt");
        $responseErrorFilename = $this->logPath  . 'response' . DS . "BookPickupDHLApiRequest_" . $frozenTime->format('Y-m-d_H:i') . '.xml';
        $this->assertFileExists($requestErrorFilename, "Eine Datei mit dem fehlerhafgen Response wurde angelegt");
        unlink($requestErrorFilename);
        unlink($responseErrorFilename);
    }


    public function testCapabilitycheck()
    {
        $date = new FrozenDate();
        $data = [
            'city' => 'Mülheim',
            'postalcode' => '45468',
            'pickupdate' => $date->modify("next Monday")->format('Y-m-d'),
            'readybytime' => 'PT08H00M'
        ];
        $CapabilityCheckApiRequest = new CapabilityCheckDHLApiRequest($data, $this->config);
        $CapabilityCheckApiRequest->callApi();
        $result = $CapabilityCheckApiRequest->getResponse();

        $assertResult = [
            'isError' => false,
            'errorMessages' => [
                0 => ''
            ],
            'pickupDate' => $date->modify("next Monday")->format('Y-m-d'),
            'pickupCutoffTime' => 'PT19H30M',
            'bookingTime' => 'PT18H'
        ];
        $this->assertEquals(json_encode($assertResult), json_encode($result),
            "Die Buchung muss bis spätestens 18:00 und die Abholung bis 19:30 erfolgen.");

        $result = $CapabilityCheckApiRequest->convertTimeToPTnHnM("07:00");
        $this->assertEquals("PT07H00M", $result);
        $result = $CapabilityCheckApiRequest->convertTimeToPTnHnM("14:30");
        $this->assertEquals("PT14H30M", $result);

        $result = CapabilityCheckDHLApiRequest::convertPTnHnMToTime("PT07H00M");
        $this->assertEquals("07:00", $result);
        $result = CapabilityCheckDHLApiRequest::convertPTnHnMToTime("PT14H30M");
        $this->assertEquals("14:30", $result);

        $result = CapabilityCheckDHLApiRequest::convertPTnHnMToTime("PT08H");
        $this->assertEquals("08:00", $result);
    }


    public function testCancelPickupNotExistingOrder()
    {
        $frozenTime = new FrozenTime();
        $pickupId = 26871;
        $data = [
            'confirmationNumber' => $pickupId + 1,
            'requestorName' => 'Sebastian Köller',
            'countryCode' => 'DE',
            'reason' => '001',
            'pickupDate' => $frozenTime->modify("+3 day")->format('Y-m-d')
        ];
        $cancelPickupApiRequest = new CancelPickupDHLApiRequest($data, $this->config);
        $cancelPickupApiRequest->callApi();
        $this->assertTrue($cancelPickupApiRequest->getIsError(), "Eine erfundene Abholung wurde erfolgreich storniert.");

        $requestErrorFilename = $this->logPath  . 'request' . DS . "CancelPickupDHLApiRequest_" . $frozenTime->format('Y-m-d_H:i') . '.xml';
        $this->assertFileExists($requestErrorFilename, "Eine Datei mit dem fehlerhaften Request wurde angelegt");
        $responseErrorFilename = $this->logPath  . 'response' . DS . "CancelPickupDHLApiRequest_" . $frozenTime->format('Y-m-d_H:i') . '.xml';
        $this->assertFileExists($requestErrorFilename, "Eine Datei mit dem fehlerhafgen Response wurde angelegt");
        unlink($requestErrorFilename);
        unlink($responseErrorFilename);
    }


    public function testCancelPickupValidOrder()
    {
        $date = new FrozenDate();
        $pickup = $this->doPickup($date->modify("next Monday"), "17:00", "19:30");
        $data = [
            'confirmationNumber' => $pickup['ordernumber'],
            'requestorName' => 'Sebastian Köller',
            'countryCode' => 'DE',
            'reason' => '001',
            'pickupDate' => $date->modify("next Monday")->toDateString()
        ];
        $cancelPickupApiRequest = new CancelPickupDHLApiRequest($data, $this->config);
        $cancelPickupApiRequest->callApi();
        $this->assertFalse($cancelPickupApiRequest->getIsError(), "Eine aufgegebene Abholung wurde erfolgreich storniert.");
    }


    public function testShipmentLabel()
    {
        $frozenTime = new FrozenTime();
        $data = [
            'praxis' => 'PHPUNIT Testpraxis',
            'street' => 'Kreuzstr. 1-3',
            'city' => 'Mülheim',
            'district' => '',
            'zip' => 45468,
            'contact' => 'Sebastian Köller',
            'phone' => '0208 88 387 559',
            'cases' => 1,
            'date' => $frozenTime->modify('+1 day')->format('Y-m-d')
        ];
        $shipmentLabelApiRequest = new ShipmentLabelDHLApiRequest($data, $this->config);
        $shipmentLabelApiRequest->callApi();
        $response = $shipmentLabelApiRequest->getResponse();
        $filename = 'tmp/shippingLabelTest.pdf';
        file_put_contents($filename, base64_decode($response['label']));
        $this->assertEquals('application/pdf', mime_content_type($filename));
        unlink($filename);
    }

    public function testShipmentLabelDateGermanFormat()
    {
        $frozenTime = new FrozenTime();
        $data = [
            'praxis' => 'PHPUNIT Testpraxis',
            'street' => 'Kreuzstr. 1-3',
            'city' => 'Mülheim',
            'district' => '',
            'zip' => 45468,
            'contact' => 'Sebastian Köller',
            'phone' => '0208 88 387 559',
            'cases' => 1,
            'date' => $frozenTime->modify('+1 day')->format('d.m.Y')
        ];
        $shipmentLabelApiRequest = new ShipmentLabelDHLApiRequest($data, $this->config);
        $shipmentLabelApiRequest->callApi();
        $response = $shipmentLabelApiRequest->getResponse();
        $filename = 'tmp/shippingLabelTest.pdf';
        file_put_contents($filename, base64_decode($response['label']));
        $this->assertEquals('application/pdf', mime_content_type($filename));
        unlink($filename);
    }


    /**
     * If an error on the DHL API occures the requests and if given the response will be logged with the
     * DHLApiRequest::errorRequestAndResponseToFile() into separate xml files.
     *
     */
    public function testShipmentLabelWithErrorWillCreateXMLErrorLogs()
    {
        $frozenTime = new FrozenTime();
        $data = [
            'praxis' => 'PHPUNIT Testpraxis viel zu langer Name ist das hier und sollte eigentlich nicht klappen',
            'street' => 'Kreuzstr. 1-3',
            'city' => 'Mülheim',
            'district' => '',
            'zip' => "asdasdasd",
            'contact' => 'Sebastian Köller',
            'phone' => '0208 88 387 559',
            'cases' => 1,
            'date' => $frozenTime->modify('+1 day')->format('d.m.Y')
        ];
        $shipmentLabelApiRequest = new ShipmentLabelDHLApiRequest($data, $this->config);
        $shipmentLabelApiRequest->callApi();
        $response = $shipmentLabelApiRequest->getResponse();
        $this->assertTrue($response['isError'], "DHL API meldet einen Fehler in der Anfrage.");

        $requestErrorFilename = $this->logPath  . 'request' . DS . "ShipmentLabelDHLApiRequest_" . $frozenTime->format('Y-m-d_H:i') . '.xml';
        $this->assertFileExists($requestErrorFilename, "Eine Datei mit dem fehlerhaften Request wurde angelegt");
        $responseErrorFilename = $this->logPath  . 'response' . DS . "ShipmentLabelDHLApiRequest_" . $frozenTime->format('Y-m-d_H:i') . '.xml';
        $this->assertFileExists($requestErrorFilename, "Eine Datei mit dem fehlerhafgen Response wurde angelegt");
        unlink($requestErrorFilename);
        unlink($responseErrorFilename);
    }
}
