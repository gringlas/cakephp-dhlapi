<?php

namespace App\Test\TestCase\Lib;

use Cake\I18n\Date;
use Cake\I18n\FrozenDate;
use Cake\TestSuite\TestCase;
use DHLApi\Lib\DHLApi;
use DHLApi\Lib\Requests\BookPickupDHLApiRequest;
use DHLApi\Lib\Requests\CapabilityCheckDHLApiRequest;

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
            'siteID' => 'xmlcimde',
            'password' => 'kruY4DhZiA',
            'accountNumber' => '143816942'
        ];
        parent::setUp();
    }

    public function testBookpickup()
    {
        $date = new FrozenDate();
        $data = [
            'companyname' => "Sebs PHPUNIT Test 1",
            'address1' => "Kreuzstr. 1-3",
            'packagelocation' => 'Praxis',
            'city' => "Mülheim",
            'postalcode' => "45468",
            'pickupdate' => $date->modify("+1 day")->format('Y-m-d'),
            'readybytime' => "08:00",
            'closetime' => "09:30",
            'personname' => "Sebastian Köller",
            'phone' => "020888387559",
            'cases' => 1
        ];
        $BookPickupDHLApiRequest = new BookPickupDHLApiRequest($data, $this->config);
        $BookPickupDHLApiRequest->callApi();
        $result = json_encode($BookPickupDHLApiRequest->getResponse());
        $this->assertRegExp('/ordernumber":"[\d]+/i', $result);



        $data["readybytime"] = "19:00";
        $data["closetime"] = "20:00";
        $data["pickupdate"] = $date->format('Y-m-d');
        $BookPickupDHLApiRequest = new BookPickupDHLApiRequest($data, $this->config);
        $BookPickupDHLApiRequest->callApi();
        $result = $BookPickupDHLApiRequest->getResponse();
        $this->assertCount(1, $result['errorMessages']);
    }


    public function testCapabilitycheck()
    {
        $date = new FrozenDate();
        $data = [
            'city' => 'Mülheim',
            'postalcode' => '45468',
            'pickupdate' => "2017-04-13",
            'readybytime' => 'PT18H00M'
        ];
        $CapabilityCheckApiRequest = new CapabilityCheckDHLApiRequest($data, $this->config);
        $CapabilityCheckApiRequest->callApi();
        $result = $CapabilityCheckApiRequest->getResponse();

        $assertResult = [
            'isError' => false,
            'errorMessages' => [
                0 => ''
            ],
            'pickupDate' => '2017-04-13',
            'pickupCutoffTime' => 'PT19H30M',
            'bookingTime' => 'PT18H'
        ];
        $this->assertEquals(json_encode($result), json_encode($assertResult));
    }
}
