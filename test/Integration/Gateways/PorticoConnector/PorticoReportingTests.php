<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\PorticoConnector;

use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Tests\Data\TestCards;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\Entities\Enums\TimeZoneConversion;
use DateTime;
use DateInterval;
use GlobalPayments\Api\ServiceConfigs\Gateways\PorticoConfig;
use GlobalPayments\Api\Utils\Logging\{Logger, SampleRequestLogger};

class PorticoReportingTests extends TestCase
{
    protected CreditCardData $card;
    private bool $enableCryptoUrl = true;
    /** @var ReportingService */
    private ReportingService $reportingService;

    public function setup() : void
    {
        $this->card = new CreditCardData();
        $this->card->number = '4111111111111111';
        $this->card->expMonth = 12;
        $this->card->expYear = TestCards::validCardExpYear();
        $this->card->cvn = '123';
        $this->card->cardHolderName = 'Joe Smith';
        
        
        $this->reportingService = new ReportingService();

        ServicesContainer::configureService($this->getConfig());
    }
        
    protected function getConfig()
    {
        $config = new PorticoConfig();
        //$config->secretApiKey = 'skapi_cert_MaePAQBr-1QAqjfckFC8FTbRTT120bVQUlfVOjgCBw';
        /*$config->serviceUrl = ($this->enableCryptoUrl) ?
                              'https://cert.api2-c.heartlandportico.com/':
                              'https://cert.api2.heartlandportico.com';*/
        $config->secretApiKey = 'skapi_cert_MTeSAQAfG1UA9qQDrzl-kz4toXvARyieptFwSKP24w';
        $config->serviceUrl = 'https://cert.api2.heartlandportico.com';
        //$config->requestLogger = new SampleRequestLogger(new Logger('portico-logs'));
        $config->requestLogger = new SampleRequestLogger(new Logger('portico-logs'));

        return $config;
    }

    public function testFindTransactions()
    {
        date_default_timezone_set("UTC");
        $dateFormat = 'Y-m-d\TH:i:s.00\Z';
        $dateMinus15days = new DateTime();
        $dateMinus15days->sub(new DateInterval('P15D'));
        $dateMinus10Utc = gmdate($dateFormat, $dateMinus15days->Format('U'));
        $nowUtc = gmdate($dateFormat);
             
        $response = $this->reportingService->findTransactions()
            ->withStartDate($dateMinus10Utc)
            ->withEndDate($nowUtc)
            ->execute();
        $this->assertNotNull($response);
        $this->assertTrue(count($response) > 0);
    }

    public function testReportActivityByDays()
    {
        date_default_timezone_set("UTC");
        $dateFormat = 'Y-m-d\TH:i:s.00\Z';
        $dateMinus5days = new DateTime();
        $dateMinus5days->sub(new DateInterval('P5D'));
        $dateMinus10Utc = gmdate($dateFormat, $dateMinus5days->Format('U'));
        $nowUtc = gmdate($dateFormat);
             
        $response = $this->reportingService->findTransactions()
            ->withStartDate($dateMinus10Utc)
            ->withEndDate($nowUtc)
            ->execute();
        $this->assertNotNull($response);
        $this->assertTrue(count($response) > 0);
    }
    
    public function testReportTransactionDetail()
    {
        $response = $this->card->charge(10)
            ->withCurrency("USD")
            ->withAmountEstimated(false)
            ->withAllowDuplicates(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);

        $response = $this->reportingService->transactionDetail($response->transactionReference->transactionId)->execute();
        $this->assertNotNull($response);
    }

    public function testInvoiceNumber()
    {
        $address = new Address();
        $address->postalCode = "12345";

        $authResponse = $this->card->charge(10)
            ->withCurrency("USD")
            ->withAddress($address)
            ->withInvoiceNumber('123456')
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($authResponse);
        $this->assertEquals('00', $authResponse->responseCode);
        $this->assertNotNull($authResponse->transactionId);

        $report = ReportingService::transactionDetail($authResponse->transactionId)
            ->execute();

        $this->assertNotNull($report);
        $this->assertEquals('123456', $report->invoiceNumber);
    }

    public function testReportCardHolderName()
    {
        $gateway_response = $this->card->charge(10)
            ->withCurrency('USD')
            ->withAllowDuplicates(true)
            ->execute();
        
        $response = $this->reportingService->transactionDetail($gateway_response->transactionId)->execute();
        
        $this->assertEquals('Joe', $response->cardHolderFirstName);
        $this->assertEquals('Smith', $response->cardHolderLastName);
    }
    
    public function testReportFindTransactionWithTransactionId()
    {
        $gateway_response = $this->card->charge(10)
            ->withCurrency('USD')
            ->withAllowDuplicates(true)
            ->execute();

        $response = $this->reportingService->findTransactions($gateway_response->transactionId)->execute();
        $this->assertNotNull($response);
    }
    
    public function testReportFindTransactionNoCriteria()
    {
        $response = $this->reportingService->findTransactions()->execute();
        $this->assertNotNull($response);
    }
    
    public function testReportFindTransactionWithCriteria()
    {
        date_default_timezone_set("UTC");
        $dateFormat = 'Y-m-d\TH:i:s.00\Z';
        $dateMinus10days = new DateTime();
        $dateMinus10days->sub(new DateInterval('P5D'));
        $dateMinus5Utc = gmdate($dateFormat, $dateMinus10days->Format('U'));
        $nowUtc = gmdate($dateFormat);
        
        $response = $this->reportingService->findTransactions()
            ->withTimeZoneConversion(TimeZoneConversion::MERCHANT)
            ->where('startDate', $dateMinus5Utc)
            ->andWith('endDate', $nowUtc)
            ->execute();
            
        $this->assertNotNull($response);
        $this->assertTrue(count($response) > 0);
    }
    
    public function testCreditAuthWithConvenienceAmount()
    {
        $authorization = $this->card->authorize(14)
            ->withCurrency('USD')
            ->withAllowDuplicates(true)
            ->withConvenienceAmount(2.00)
            ->execute();
            
        $this->assertNotNull($authorization);
        $this->assertEquals('00', $authorization->responseCode);
        
        $report = $this->reportingService->transactionDetail($authorization->transactionId)->execute();
        $this->assertNotNull($report);
        $this->assertEquals('2.00', $report->convenienceAmount);
    }
    
    public function testCreditAuthWithShippingAmount()
    {
        $authorization = $this->card->authorize(14)
            ->withCurrency('USD')
            ->withAllowDuplicates(true)
            ->withShippingAmount(2.00)
            ->execute();
        
        $this->assertNotNull($authorization);
        $this->assertEquals('00', $authorization->responseCode);
        
        $report = $this->reportingService->transactionDetail($authorization->transactionId)->execute();
        $this->assertNotNull($report);
        $this->assertEquals('2.00', $report->shippingAmount);
    }
    
    public function testCreditSaleWithConvenienceAmount()
    {
        $response = $this->card->charge(15)
            ->withCurrency('USD')
            ->withAllowDuplicates(true)
            ->withConvenienceAmount(2.00)
            ->execute();
            
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        
        $report = $this->reportingService->transactionDetail($response->transactionId)->execute();
        $this->assertNotNull($report);
        $this->assertEquals('2.00', $report->convenienceAmount);
    }
    
    public function testCreditSaleWithShippingAmount()
    {
        $response = $this->card->charge(15)
            ->withCurrency('USD')
            ->withAllowDuplicates(true)
            ->withShippingAmount(2.00)
            ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        
        $report = $this->reportingService->transactionDetail($response->transactionId)->execute();
        $this->assertNotNull($report);
        $this->assertEquals('2.00', $report->shippingAmount);
    }
    
    public function testCreditOfflineAuthWithConvenienceAmount()
    {
        $response = $this->card->authorize(16)
            ->withCurrency('USD')
            ->withOfflineAuthCode('12345')
            ->withAllowDuplicates(true)
            ->withConvenienceAmount(2.00)
            ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        
        $report = $this->reportingService->transactionDetail($response->transactionId)->execute();
        $this->assertNotNull($report);
        $this->assertEquals('2.00', $report->convenienceAmount);
    }
    
    public function testCreditOfflineAuthWithShippingAmount()
    {
        $response = $this->card->authorize(16)
            ->withCurrency('USD')
            ->withOfflineAuthCode('12345')
            ->withAllowDuplicates(true)
            ->withShippingAmount(2.00)
            ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        
        $report = $this->reportingService->transactionDetail($response->transactionId)->execute();
        $this->assertNotNull($report);
        $this->assertEquals('2.00', $report->shippingAmount);
    }
    
    public function testCreditOfflineSaleWithConvenienceAmount()
    {
        $response = $this->card->charge(17)
            ->withCurrency('USD')
            ->withOfflineAuthCode('12345')
            ->withAllowDuplicates(true)
            ->WithConvenienceAmount(2.00)
            ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        
        $report = $this->reportingService->transactionDetail($response->transactionId)->execute();
        $this->assertNotNull($report);
        $this->assertEquals('2.00', $report->convenienceAmount);
    }

    public function testCreditOfflineSaleWithShippingAmount()
    {
        $response = $this->card->charge(17)
            ->withCurrency('USD')
            ->withOfflineAuthCode('12345')
            ->withAllowDuplicates(true)
            ->withShippingAmount(2.00)
            ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);
        
        $report = $this->reportingService->transactionDetail($response->transactionId)->execute();
        $this->assertNotNull($report);
        $this->assertEquals('2.00', $report->shippingAmount);
    }

    public function testReportTransactionAvsCvvDetail()
    {
        $response = $this->card->charge(10)
            ->withCurrency("USD")
            ->withAmountEstimated(false)
            ->withAllowDuplicates(true)
            ->execute();
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->responseCode);

        $response = ReportingService::transactionDetail($response->transactionReference->transactionId)
            ->execute();

        $this->assertNotNull($response);

        $this->assertNotNull($response->avsResponseCode);
        $this->assertNotNull($response->avsResponseMessage);

        $this->assertNotNull($response->cvnResponseCode);
        $this->assertNotNull($response->cvnResponseMessage);
    }

    public function testReportBatchDetailWithClientTxnIdAndBatchID()
    {
        //setting up the card to use
        $card = new CreditCardData();
        $card->number = '4111111111111111';
        $card->expMonth = '12';
        $card->expYear = '2025';
        $card->cvn = '123';

        // generate random clienttxnid
        $randomID = rand(10,100000);
        /** @var string */
        $clientTxnID = (string)$randomID;

        // Do authorize
        $response = $card->authorize(15)
            ->withCurrency('USD')
            ->withClientTransactionId($clientTxnID)
            ->withAllowDuplicates(true)
            ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        // Do a capture to add to batch
        $capture = $response->capture(16)
            ->withGratuity(2)
            ->execute();
        
        $this->assertNotNull($capture);
        $this->assertEquals('00', $capture->responseCode);

        // Get ReportBatchDetail
        $reportResponse = ReportingService::batchDetail()
            //->withBatchId(992515)
            ->execute();
        
        //Get reportItem that matches the clienttxnid
        $reportItem = array_filter(
            $reportResponse,
            function ($summary) use ($clientTxnID) {
                return $summary->clientTransactionId === $clientTxnID;
            }
        );

        $reportItem = reset($reportItem); // Get the first match

        $this->assertNotNull($reportItem);
        $this->assertEquals($reportItem->clientTransactionId, $clientTxnID);
    }

    public function testReportOpenAuthsWithClientTxnId()
    {
        //setting up the card to use
        $card = new CreditCardData();
        $card->number = '4111111111111111';
        $card->expMonth = '12';
        $card->expYear = '2025';
        $card->cvn = '123';

        // generate random clienttxnid
        $randomID = rand(10,100000);
        /** @var string */
        $clientTxnID = (string)$randomID;

        // Do authorize
        $response = $card->authorize(15)
            ->withCurrency('USD')
            ->withClientTransactionId($clientTxnID)
            ->withAllowDuplicates(true)
            ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->responseCode);

        // Get ReportOpenAuths
        $reportResponse = ReportingService::openAuths()
            ->withDeviceId('5577503')
            ->execute();
        
        $this->assertNotNull($reportResponse);

        // Get reportItem that matches the clienttxnid
        $reportItem = array_filter(
            $reportResponse,
            function ($summary) use ($clientTxnID) {
                return $summary->clientTransactionId === $clientTxnID;
            }
        );

        $reportItem = reset($reportItem); // Get the first match

        $this->assertNotNull($reportItem);
        $this->assertEquals($reportItem->clientTransactionId, $clientTxnID);
    }
}
