<?php

use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\PaymentMethods\CreditTrackData;

class TrackDataTest extends TestCase
{
    public function testMastercardTrack1()
    {
        $track = new CreditTrackData();
        $track->setValue('%B5473500000000014^MC TEST CARD^251210199998888777766665555444433332?');
        $this->assertEquals('MC', $track->cardType);
        $this->assertEquals('5473500000000014', $track->pan);
        $this->assertEquals('2512', $track->expiry);
        $this->assertFalse($track->isFleet);
        $this->assertNotNull($track->trackData);
    }
    public function testMastercardTrack1Truncated()
    {
        $track = new CreditTrackData();
        $track->setValue('B5473500000000014^MC TEST CARD^2512');
        $this->assertEquals('5473500000000014', $track->pan);
        $this->assertEquals('2512', $track->expiry);
        $this->assertEquals('MC', $track->cardType);
        $this->assertFalse($track->isFleet);
        $this->assertNotNull($track->trackData);
    }

    public function testMastercardTrack2()
    {
        $track = new CreditTrackData();
        $track->setValue(';5473500000000014=25121019999888877776?');
        $this->assertEquals('MC', $track->cardType);
        $this->assertEquals('5473500000000014', $track->pan);
        $this->assertEquals('2512', $track->expiry);
        $this->assertFalse($track->isFleet);
        $this->assertNotNull($track->trackData);
    }

    public function testMastercardTrack2Truncated()
    {
        $track = new CreditTrackData();
        $track->setValue('5473500000000014=2512');
        $this->assertEquals('MC', $track->cardType);
        $this->assertEquals('5473500000000014', $track->pan);
        $this->assertEquals('2512', $track->expiry);
        $this->assertFalse($track->isFleet);
        $this->assertNotNull($track->trackData);
    }

    public function testMastercardFleet()
    {
        $track = new CreditTrackData();
        $track->setValue('5532320000001113=20121019999888877712');
        $this->assertEquals('MCFleet', $track->cardType);
        $this->assertEquals('5532320000001113', $track->pan);
        $this->assertEquals('2012', $track->expiry);
        $this->assertTrue($track->isFleet);
        $this->assertNotNull($track->trackData);
    }

    public function testVisaTrack1()
    {
        $track = new CreditTrackData();
        $track->setValue('%B4012002000060016^VI TEST CREDIT^251210118039000000000396?');
        $this->assertEquals('Visa', $track->cardType);
        $this->assertEquals('4012002000060016', $track->pan);
        $this->assertEquals('2512', $track->expiry);
        $this->assertFalse($track->isFleet);
        $this->assertNotNull($track->trackData);
    }

    public function testVisaTrack2()
    {
        $track = new CreditTrackData();
        $track->setValue('4012002000060016=25121011803939600000');
        $this->assertEquals('Visa', $track->cardType);
        $this->assertEquals('4012002000060016', $track->pan);
        $this->assertEquals('1011803939600000', $track->discretionaryData);
        $this->assertEquals('2512', $track->expiry);
        $this->assertFalse($track->isFleet);
        $this->assertNotNull($track->trackData);
    }

    public function testVisaBothTracks()
    {
        $track = new CreditTrackData();
        $track->setValue('%B4012002000060016^VI TEST CREDIT^251210118039000000000396?;4012002000060016=25121011803939600000?');
        $this->assertEquals('Visa', $track->cardType);
        $this->assertEquals('4012002000060016', $track->pan);
        $this->assertEquals('2512', $track->expiry);
        $this->assertFalse($track->isFleet);
        $this->assertNotNull($track->trackData);
    }

    public function testVisaFleet()
    {
        $track = new CreditTrackData();
        $track->setValue('%B4485531111111118^VISA TEST CARD/GOOD^20121019206100000000003?');
        $this->assertEquals('VisaFleet', $track->cardType);
        $this->assertEquals('4485531111111118', $track->pan);
        $this->assertEquals('2012', $track->expiry);
        $this->assertTrue($track->isFleet);
        $this->assertNotNull($track->trackData);
    }

    public function testWexFleetTrack1()
    {
        $track = new CreditTrackData();
        $track->setValue("%B6900460000001113^WEX FLEET TEST^20121019999888877712?");
        $this->assertEquals('WexFleet', $track->cardType);
        $this->assertEquals('6900460000001113', $track->pan);
        $this->assertEquals('2012', $track->expiry);
        $this->assertTrue($track->isFleet);
        $this->assertNotNull($track->trackData);
    }

    public function testWexFleetTrack2()
    {
        $track = new CreditTrackData();
        $track->setValue("6900460000001113=20121019999888877712");
        $this->assertEquals('WexFleet', $track->cardType);
        $this->assertEquals('6900460000001113', $track->pan);
        $this->assertEquals('2012', $track->expiry);
        $this->assertTrue($track->isFleet);
        $this->assertNotNull($track->trackData);
    }

    public function testVoyagerFleetTrack1()
    {
        $track = new CreditTrackData();
        $track->setValue("B7088890000001113^VOYAGER FLEET TEST^20121019999888877712");

        $this->assertEquals('VoyagerFleet', $track->cardType);
        $this->assertEquals('7088890000001113', $track->pan);
        $this->assertEquals('2012', $track->expiry);
        $this->assertTrue($track->isFleet);
        $this->assertNotNull($track->trackData);
    }

    public function testVoyagerFleetTrack2()
    {
        $track = new CreditTrackData();
        $track->setValue("7088850000001113=20121019999888877712");

        $this->assertEquals('VoyagerFleet', $track->cardType);
        $this->assertEquals('7088850000001113', $track->pan);
        $this->assertEquals('2012', $track->expiry);
        $this->assertTrue($track->isFleet);
        $this->assertNotNull($track->trackData);
    }
}