<?php

namespace App\Test\TestCase\Lib;

use Cake\TestSuite\TestCase;
use DHLApi\Lib\DHLApi;

/**
 * Created by IntelliJ IDEA.
 * User: sebastiankoller
 * Date: 02.04.17
 * Time: 17:44
 */
class DHLApiTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();
    }

    public function testCheckAndBuildUri()
    {
        $DHLApi = new DHLApi("bookPickup");
        $result = (String) $DHLApi->callApi();
        $this->assertContains("2017-02-28 00:00:00", $result);

        $dateParser = new DateParser("Posted febr 12ND AT 6:00PM.");
        $result = (String) $dateParser->getParsedDate();
        $this->assertContains("2017-02-12 00:00:00", $result);

        $dateParser = new DateParser("2016/03/12 00:00:00");
        $result = (String) $dateParser->getParsedDate();
        $this->assertContains("2016-03-12 00:00:00", $result);

        $dateParser = new DateParser("10.12.2009 00:00:00");
        $result = (String) $dateParser->getParsedDate();
        $this->assertContains("2009-12-10 00:00:00", $result);

        $now = new Chronos();
        $now = $now->setTime(0,0);
        $now = $now->day(3);
        $dateParser = new DateParser("Heute ist der 3. im Jahre 16");
        $result = (String) $dateParser->getParsedDate();
        $this->assertContains((String) $now, $result);

    }
}
