<?php

use GlobalPayments\Api\Entities\Enums\RemittanceReferenceType;
use GlobalPayments\Api\Entities\Enums\ShaHashType;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Reporting\SearchCriteria;
use GlobalPayments\Api\Entities\Reporting\TransactionSummary;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\PaymentMethods\BankPayment;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpEcomConfig;
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Utils\GenerationUtils;
use GlobalPayments\Api\Utils\Logging\Logger;
use GlobalPayments\Api\Utils\Logging\SampleRequestLogger;
use GlobalPayments\Api\Entities\Enums\BankPaymentStatus;
use PHPUnit\Framework\TestCase;

class OpenBankingTest extends TestCase
{
    private $currency = 'GBP';
    private $amount = 10.99;

    public function setup() : void
    {
        $config = $this->getConfig();
        ServicesContainer::configureService($config);
    }

    protected function getConfig()
    {
        $config = new GpEcomConfig();
        $config->merchantId = 'openbankingsandbox';
        $config->sharedSecret = 'sharedsecret';
        $config->accountId = 'internet';
        $config->requestLogger = new SampleRequestLogger(new Logger("logs"));
        $config->shaHashType = ShaHashType::SHA512;

        return $config;
    }

    public function testFasterPaymentsCharge()
    {
        $bankPayment = $this->fasterPaymentsConfig();

        $trn = $bankPayment->charge($this->amount)
            ->withCurrency($this->currency)
            ->withRemittanceReference(RemittanceReferenceType::TEXT, 'Nike Bounce Shoes')
            ->execute();

        $this->assertOpenBankingResponse($trn);

        fwrite(STDERR, print_r($trn->bankPaymentResponse->redirectUrl, true));
        sleep(2);
        $response = ReportingService::bankPaymentDetail($trn->bankPaymentResponse->id)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals(1, $response->totalRecordCount);
        $this->assertEquals($trn->bankPaymentResponse->id, $response->result[0]->transactionId);
        $this->assertNull($response->result[0]->bankPaymentResponse->iban);
        $this->assertNull($response->result[0]->bankPaymentResponse->sortCode);
        $this->assertNull($response->result[0]->bankPaymentResponse->accountNumber);
        $this->assertNull($response->result[0]->bankPaymentResponse->accountName);
    }

    public function testSEPACharge()
    {
        $bankPayment = $this->sepaConfig();

        $trn = $bankPayment->charge($this->amount)
            ->withCurrency('EUR')
            ->withRemittanceReference(RemittanceReferenceType::TEXT, 'Nike Bounce Shoes')
            ->execute();

        $this->assertOpenBankingResponse($trn);

//        fwrite(STDERR, print_r($trn->bankPaymentResponse->redirectUrl, TRUE));
        sleep(2);
        $response = ReportingService::bankPaymentDetail($trn->bankPaymentResponse->id)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals(1, $response->totalRecordCount);
        $this->assertEquals($trn->bankPaymentResponse->id, $response->result[0]->transactionId);
        $this->assertNull($response->result[0]->bankPaymentResponse->iban);
        $this->assertNull($response->result[0]->bankPaymentResponse->sortCode);
        $this->assertNull($response->result[0]->bankPaymentResponse->accountNumber);
        $this->assertNull($response->result[0]->bankPaymentResponse->accountName);
    }

    public function testBankPaymentList()
    {
        $startDate = (new \DateTime())->modify('-5 day');
        $endDate = new \DateTime();
        $response = ReportingService::findBankPaymentTransactions(1, 10)
            ->where(SearchCriteria::START_DATE, $startDate)
            ->andWith(SearchCriteria::END_DATE, $endDate)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));
        /** @var TransactionSummary $rs */
        foreach ($response->result as $rs) {
            $this->assertLessThanOrEqual($endDate, $rs->transactionDate);
            $this->assertGreaterThanOrEqual($startDate, $rs->transactionDate);
        }
    }

    public function testBankPaymentList_EmptyList()
    {
        $startDate = (new \DateTime())->modify('-29 day');
        $endDate = (new \DateTime())->modify('-28 day');
        $response = ReportingService::findBankPaymentTransactions(1, 10)
            ->where(SearchCriteria::START_DATE, $startDate)
            ->andWith(SearchCriteria::END_DATE, $endDate)
            ->andWith(SearchCriteria::TRANSACTION_STATUS, BankPaymentStatus::REQUEST_CONSUMER_CONSENT)
            ->execute();

        $this->assertNotNull($response);
        $this->assertCount(0, $response->result);
        $this->assertNull($response->totalRecordCount);
        $this->assertTrue(is_array($response->result));
    }

    public function testBankPaymentListWithReturnPii()
    {
        $startDate = (new \DateTime())->modify('-29 day');
        $endDate = (new \DateTime())->modify('-1 day');
        $response = ReportingService::findBankPaymentTransactions(1, 10)
            ->where(SearchCriteria::START_DATE, $startDate)
            ->andWith(SearchCriteria::END_DATE, $endDate)
            ->andWith(SearchCriteria::RETURN_PII, true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(count($response->result) > 0);
        /** @var TransactionSummary $trn */
        $trn = $response->result[rand(0, count($response->result) - 1)];
        $bankPaymentResponse = $trn->bankPaymentResponse;
        switch ($bankPaymentResponse->type) {
            case \GlobalPayments\Api\Entities\Enums\BankPaymentType::FASTERPAYMENTS:
                $this->assertNotNull($bankPaymentResponse->sortCode);
                $this->assertNotNull($bankPaymentResponse->accountNumber);
                $this->assertNotNull($bankPaymentResponse->accountName);
                break;
            case \GlobalPayments\Api\Entities\Enums\BankPaymentType::SEPA:
                $this->assertNotNull($bankPaymentResponse->iban);
                $this->assertNotNull($bankPaymentResponse->accountName);
                break;
            default:
                $this->fail('Bank payment type unknown!');
                break;
        }
    }

    public function testGetBankPaymentById()
    {
        $obTransId = 'DuVGjawYd1m8UkbZyi';
        /** @var \GlobalPayments\Api\Entities\GpApi\PagedResult $response */
        $response = ReportingService::bankPaymentDetail($obTransId)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals(1, $response->totalRecordCount);
        $this->assertEquals($obTransId, $response->result[0]->transactionId);
    }

    public function testGetBankPaymentById_RandomId()
    {
        $length = 18;
        $obTransId = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 1, $length);

        /** @var \GlobalPayments\Api\Entities\GpApi\PagedResult $response */
        $response = ReportingService::bankPaymentDetail($obTransId)
            ->execute();

        $this->assertNotNull($response);
        $this->assertCount(0, $response->result);
        $this->assertNull($response->totalRecordCount);
        $this->assertTrue(is_array($response->result));
    }

    public function testGetBankPaymentById_InvalidId()
    {
        $obTransId = GenerationUtils::getGuid();

        $exceptionCaught = false;
        try {
            ReportingService::bankPaymentDetail($obTransId)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Status Code: 400 - obTransId is invalid ', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testCharge_AllBankDetails()
    {
        $bankPayment = $this->fasterPaymentsConfig();
        $bankPayment->iban = '123456';

        $trn = $bankPayment->charge($this->amount)
                ->withCurrency($this->currency)
                ->withRemittanceReference(RemittanceReferenceType::TEXT, 'Nike Bounce Shoes')
                ->execute();
        $this->assertOpenBankingResponse($trn);
    }

    public function testFasterPaymentsCharge_MissingRemittanceReference()
    {
        $bankPayment = $this->fasterPaymentsConfig();

        $exceptionCaught = false;
        try {
            $bankPayment->charge($this->amount)
                ->withCurrency($this->currency)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Status Code: 400 - payment.remittance_reference cannot be null ', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testFasterPaymentsCharge_MissingRemittanceReferenceType()
    {
        $bankPayment = $this->fasterPaymentsConfig();

        $exceptionCaught = false;
        try {
            $bankPayment->charge($this->amount)
                ->withCurrency($this->currency)
                ->withRemittanceReference(null, 'Nike Bounce shoes')
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Status Code: 400 - remittance_reference.type cannot be blank or null ', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testFasterPaymentsCharge_MissingRemittanceReferenceValue()
    {
        $bankPayment = $this->fasterPaymentsConfig();

        $exceptionCaught = false;
        try {
            $bankPayment->charge($this->amount)
                ->withCurrency($this->currency)
                ->withRemittanceReference(RemittanceReferenceType::TEXT, null)
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Status Code: 400 - remittance_reference.value cannot be blank or null ', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testFasterPaymentsCharge_MissingReturnUrl()
    {
        $bankPayment = $this->fasterPaymentsConfig();
        $bankPayment->returnUrl = null;

        $exceptionCaught = false;
        try {
            $bankPayment->charge($this->amount)
                ->withCurrency($this->currency)
                ->withRemittanceReference(RemittanceReferenceType::TEXT, 'Nike Bounce Shoes')
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Status Code: 400 - return_url must not be null ', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testFasterPaymentsCharge_MissingStatusUrl()
    {
        $bankPayment = $this->fasterPaymentsConfig();
        $bankPayment->statusUpdateUrl = null;

        $exceptionCaught = false;
        try {
            $bankPayment->charge($this->amount)
                ->withCurrency($this->currency)
                ->withRemittanceReference(RemittanceReferenceType::TEXT, 'Nike Bounce Shoes')
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Status Code: 400 - status_url must not be null ', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testFasterPaymentsCharge_MissingAccountNumber()
    {
        $bankPayment = $this->fasterPaymentsConfig();
        $bankPayment->accountNumber = null;

        $exceptionCaught = false;
        try {
            $bankPayment->charge($this->amount)
                ->withCurrency($this->currency)
                ->withRemittanceReference(RemittanceReferenceType::TEXT, 'Nike Bounce Shoes')
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Status Code: 400 - Invalid Payment Scheme required fields ', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testFasterPaymentsCharge_MissingSortCode()
    {
        $bankPayment = $this->fasterPaymentsConfig();
        $bankPayment->sortCode = null;

        $exceptionCaught = false;
        try {
            $bankPayment->charge($this->amount)
                ->withCurrency($this->currency)
                ->withRemittanceReference(RemittanceReferenceType::TEXT, 'Nike Bounce Shoes')
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Status Code: 400 - Invalid Payment Scheme required fields ', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testFasterPaymentsCharge_MissingAccountName()
    {
        $bankPayment = $this->fasterPaymentsConfig();
        $bankPayment->accountName = null;

        $exceptionCaught = false;
        try {
            $bankPayment->charge($this->amount)
                ->withCurrency($this->currency)
                ->withRemittanceReference(RemittanceReferenceType::TEXT, 'Nike Bounce Shoes')
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Status Code: 400 - payment.destination.name is invalid ', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testFasterPaymentsCharge_InvalidCurrency()
    {
        $bankPayment = $this->fasterPaymentsConfig();

        $exceptionCaught = false;
        try {
            $bankPayment->charge(1)
                ->withCurrency('EUR')
                ->withRemittanceReference(RemittanceReferenceType::TEXT, 'Nike Bounce Shoes')
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Status Code: 400 - Invalid Payment Scheme required fields ', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testSEPACharge_MissingIban()
    {
        $bankPayment = $this->sepaConfig();
        $bankPayment->iban = null;

        $exceptionCaught = false;
        try {
            $bankPayment->charge($this->amount)
                ->withCurrency('EUR')
                ->withRemittanceReference(RemittanceReferenceType::TEXT, 'Nike Bounce Shoes')
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Status Code: 400 - Invalid Payment Scheme required fields ', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testSEPACharge_MissingName()
    {
        $bankPayment = $this->sepaConfig();
        $bankPayment->accountName = null;

        $exceptionCaught = false;
        try {
            $bankPayment->charge($this->amount)
                ->withCurrency('EUR')
                ->withRemittanceReference(RemittanceReferenceType::TEXT, 'Nike Bounce Shoes')
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Status Code: 400 - payment.destination.name is invalid ', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testSEPACharge_InvalidCurrency()
    {
        $bankPayment = $this->sepaConfig();

        $exceptionCaught = false;
        try {
            $bankPayment->charge($this->amount)
                ->withCurrency($this->currency)
                ->withRemittanceReference(RemittanceReferenceType::TEXT, 'Nike Bounce Shoes')
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertEquals('Status Code: 400 - Invalid Payment Scheme required fields ', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    public function testSEPACharge_CADCurrency()
    {
        $bankPayment = $this->sepaConfig();

        $exceptionCaught = false;
        try {
            $bankPayment->charge($this->amount)
                ->withCurrency("CAD")
                ->withRemittanceReference(RemittanceReferenceType::TEXT, 'Nike Bounce Shoes')
                ->execute();
        } catch (GatewayException $e) {
            $exceptionCaught = true;
            $this->assertStringContainsString(' Merchant currency is not enabled for Open Banking', $e->getMessage());
        } finally {
            $this->assertTrue($exceptionCaught);
        }
    }

    private function fasterPaymentsConfig()
    {
        $bankPayment = new BankPayment();
        $bankPayment->accountNumber = '12345678';
        $bankPayment->sortCode = '406650';
        $bankPayment->accountName = 'AccountName';
        $bankPayment->returnUrl = 'https://7b8e82a17ac00346e91e984f42a2a5fb.m.pipedream.net';
        $bankPayment->statusUpdateUrl = 'https://7b8e82a17ac00346e91e984f42a2a5fb.m.pipedream.net';

        return $bankPayment;
    }

    private function sepaConfig()
    {
        $bankPayment = new BankPayment();
        $bankPayment->iban = '123456';
        $bankPayment->accountName = 'AccountName';
        $bankPayment->returnUrl = 'https://7b8e82a17ac00346e91e984f42a2a5fb.m.pipedream.net';
        $bankPayment->statusUpdateUrl = 'https://7b8e82a17ac00346e91e984f42a2a5fb.m.pipedream.net';

        return $bankPayment;
    }

    private function assertOpenBankingResponse(Transaction $trn)
    {
        $this->assertEquals(BankPaymentStatus::PAYMENT_INITIATED, $trn->responseMessage);
        $this->assertNotNull($trn->transactionId);
        $this->assertNotNull($trn->bankPaymentResponse->redirectUrl);
    }
}
