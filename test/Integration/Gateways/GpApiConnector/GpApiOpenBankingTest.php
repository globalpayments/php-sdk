<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\PaymentMethodName;
use GlobalPayments\Api\Entities\Enums\PaymentProvider;
use GlobalPayments\Api\Entities\Enums\RemittanceReferenceType;
use GlobalPayments\Api\Entities\Enums\TransactionSortProperty;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\Entities\Exceptions\BuilderException;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Reporting\SearchCriteria;
use GlobalPayments\Api\Entities\Reporting\TransactionSummary;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\PaymentMethods\BankPayment;
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use PHPUnit\Framework\TestCase;

class GpApiOpenBankingTest extends TestCase
{
    private $currency = 'GBP';
    private $amount = 10.99;
    private $startDate;
    private $endDate;

    public function setup(): void
    {
        ServicesContainer::configureService($this->setUpConfig());
        $this->startDate = (new \DateTime())->modify('-30 days')->setTime(0, 0, 0);
        $this->endDate = (new \DateTime())->modify('-3 days')->setTime(0, 0, 0);
    }

    public static function tearDownAfterClass(): void
    {
        BaseGpApiTestConfig::resetGpApiConfig();
    }

    public function setUpConfig()
    {
        $config = BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardNotPresent);
        $config->country = 'GB';
        return $config;
    }

    private function fasterPaymentsConfig()
    {
        $bankPayment = new BankPayment();
        $bankPayment->accountNumber = '99999999';
        $bankPayment->sortCode = '407777';
        $bankPayment->accountName = 'Minal';
        $bankPayment->countries = ['GB', 'IE'];
        $bankPayment->returnUrl = 'https://7b8e82a17ac00346e91e984f42a2a5fb.m.pipedream.net';
        $bankPayment->statusUpdateUrl = 'https://gp-sdk.localhost.com/examples/gp-api/notificationUrl.php';

        return $bankPayment;
    }

    private function sepaConfig()
    {
        $bankPayment = new BankPayment();
        $bankPayment->iban = 'GB33BUKB20201555555555';
        $bankPayment->accountName = 'AccountName';
        $bankPayment->returnUrl = 'https://7b8e82a17ac00346e91e984f42a2a5fb.m.pipedream.net';
        $bankPayment->statusUpdateUrl = 'https://7b8e82a17ac00346e91e984f42a2a5fb.m.pipedream.net';

        return $bankPayment;
    }

    private function assertOpenBankingResponse(Transaction $trn)
    {
        $this->assertEquals(TransactionStatus::INITIATED, $trn->responseMessage);
        $this->assertNotNull($trn->transactionId);
        $this->assertNotNull($trn->bankPaymentResponse->redirectUrl);
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

        /** @var TransactionSummary $response */
        $response = ReportingService::transactionDetail($trn->transactionId)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals($trn->transactionId, $response->transactionId);
        $this->assertNotNull($response->bankPaymentResponse->sortCode);
        $this->assertNull($response->bankPaymentResponse->iban);
        $this->assertNotNull($response->bankPaymentResponse->accountNumber);
    }

    public function testSEPACharge()
    {
        $bankPayment = $this->sepaConfig();

        $trn = $bankPayment->charge($this->amount)
            ->withCurrency('EUR')
            ->withRemittanceReference(RemittanceReferenceType::TEXT, 'Nike Bounce Shoes')
            ->execute();

        $this->assertOpenBankingResponse($trn);
        fwrite(STDERR, print_r($trn->bankPaymentResponse->redirectUrl, TRUE));
        sleep(2);
        /** @var TransactionSummary $response */
        $response = ReportingService::transactionDetail($trn->transactionId)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals($trn->transactionId, $response->transactionId);
        $this->assertNotNull($response->bankPaymentResponse->iban);
        $this->assertNull($response->bankPaymentResponse->sortCode);
        $this->assertNull($response->bankPaymentResponse->accountNumber);
    }

    public function testReportFindOBTransactionsByStartDateAndEndDate()
    {
        $response = ReportingService::findTransactionsPaged(1, 10)
            ->orderBy(TransactionSortProperty::TIME_CREATED)
            ->where(SearchCriteria::START_DATE, $this->startDate)
            ->andWith(SearchCriteria::END_DATE, $this->endDate)
            ->andWith(SearchCriteria::PAYMENT_PROVIDER, PaymentProvider::OPEN_BANKING)
            ->execute();

        $this->assertNotNull($response);
        $this->assertTrue(is_array($response->result));

        /** @var TransactionSummary $rs */
        foreach ($response->result as $rs) {
            $this->assertEquals(PaymentMethodName::BANK_PAYMENT, $rs->paymentType);
            $this->assertLessThanOrEqual($this->endDate->format('Y-m-d'), $rs->transactionDate->format('Y-m-d'));
            $this->assertGreaterThanOrEqual($this->startDate->format('Y-m-d'), $rs->transactionDate->format('Y-m-d'));
        }
    }

    public function testFasterPaymentsChargeThenRefund()
    {
        $bankPayment = $this->fasterPaymentsConfig();

        $trn = $bankPayment->charge($this->amount)
            ->withCurrency($this->currency)
            ->withRemittanceReference(RemittanceReferenceType::TEXT, 'Nike Bounce Shoes')
            ->execute();

        $this->assertOpenBankingResponse($trn);

        $errorFound = false;
        try {
            $trn->refund()
                ->withCurrency($this->currency)
                ->execute();
        } catch (BuilderException $e) {
            $errorFound = true;
            $this->assertEquals('The REFUND is not supported for BANK PAYMENT', $e->getMessage());
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testSEPAChargeThenRefund()
    {
        $bankPayment = $this->sepaConfig();

        $trn = $bankPayment->charge($this->amount)
            ->withCurrency('EUR')
            ->withRemittanceReference(RemittanceReferenceType::TEXT, 'Nike Bounce Shoes')
            ->execute();

        $this->assertOpenBankingResponse($trn);

        $errorFound = false;
        try {
            $trn->refund()
                ->withCurrency($this->currency)
                ->execute();
        } catch (BuilderException $e) {
            $errorFound = true;
            $this->assertEquals('The REFUND is not supported for BANK PAYMENT', $e->getMessage());
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testFasterPaymentsMissingRemittanceReference()
    {
        $bankPayment = $this->fasterPaymentsConfig();

        $errorFound = false;
        try {
            $bankPayment->charge($this->amount)
                ->withCurrency($this->currency)
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals('Status Code: SYSTEM_ERROR_DOWNSTREAM - Unable to process your request due to an error with a system down stream.', $e->getMessage());
            $this->assertEquals('50046', $e->responseCode);
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testFasterPaymentsMissingRemittanceReferenceType()
    {
        $bankPayment = $this->fasterPaymentsConfig();

        $errorFound = false;
        try {
            $bankPayment->charge($this->amount)
                ->withCurrency($this->currency)
                ->withRemittanceReference(null, 'Nike Bounce Shoes')
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals('Status Code: SYSTEM_ERROR_DOWNSTREAM - Unable to process your request due to an error with a system down stream.', $e->getMessage());
            $this->assertEquals('50046', $e->responseCode);
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testFasterPaymentsMissingRemittanceReferenceValue()
    {
        $bankPayment = $this->fasterPaymentsConfig();

        $errorFound = false;
        try {
            $bankPayment->charge($this->amount)
                ->withCurrency($this->currency)
                ->withRemittanceReference(RemittanceReferenceType::TEXT, null)
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals('Status Code: SYSTEM_ERROR_DOWNSTREAM - Unable to process your request due to an error with a system down stream.', $e->getMessage());
            $this->assertEquals('50046', $e->responseCode);
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testFasterPaymentsMissingReturnUrl()
    {
        $bankPayment = $this->fasterPaymentsConfig();
        $bankPayment->returnUrl = null;

        $errorFound = false;
        try {
            $bankPayment->charge($this->amount)
                ->withCurrency($this->currency)
                ->withRemittanceReference(RemittanceReferenceType::TEXT, 'Nike Bounce Shoes')
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals('Status Code: SYSTEM_ERROR_DOWNSTREAM - Unable to process your request due to an error with a system down stream.', $e->getMessage());
            $this->assertEquals('50046', $e->responseCode);
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testFasterPaymentsMissingStatusUrl()
    {
        $bankPayment = $this->fasterPaymentsConfig();
        $bankPayment->statusUpdateUrl = null;

        $errorFound = false;
        try {
            $bankPayment->charge($this->amount)
                ->withCurrency($this->currency)
                ->withRemittanceReference(RemittanceReferenceType::TEXT, 'Nike Bounce Shoes')
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals('Status Code: SYSTEM_ERROR_DOWNSTREAM - Unable to process your request due to an error with a system down stream.', $e->getMessage());
            $this->assertEquals('50046', $e->responseCode);
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testFasterPaymentsMissingAccountNumber()
    {
        $bankPayment = $this->fasterPaymentsConfig();
        $bankPayment->accountNumber = null;

        $errorFound = false;
        try {
            $bankPayment->charge($this->amount)
                ->withCurrency($this->currency)
                ->withRemittanceReference(RemittanceReferenceType::TEXT, 'Nike Bounce Shoes')
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals('Status Code: SYSTEM_ERROR_DOWNSTREAM - Unable to process your request due to an error with a system down stream.', $e->getMessage());
            $this->assertEquals('50046', $e->responseCode);
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testFasterPaymentsMissingSortCode()
    {
        $bankPayment = $this->fasterPaymentsConfig();
        $bankPayment->sortCode = null;

        $errorFound = false;
        try {
            $bankPayment->charge($this->amount)
                ->withCurrency($this->currency)
                ->withRemittanceReference(RemittanceReferenceType::TEXT, 'Nike Bounce Shoes')
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals('Status Code: SYSTEM_ERROR_DOWNSTREAM - Unable to process your request due to an error with a system down stream.', $e->getMessage());
            $this->assertEquals('50046', $e->responseCode);
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testFasterPaymentsMissingAccountName()
    {
        $bankPayment = $this->fasterPaymentsConfig();
        $bankPayment->accountName = null;

        $errorFound = false;
        try {
            $bankPayment->charge($this->amount)
                ->withCurrency($this->currency)
                ->withRemittanceReference(RemittanceReferenceType::TEXT, 'Nike Bounce Shoes')
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Request expects the following fields payment_method.bank_transfer.bank.name', $e->getMessage());
            $this->assertEquals('40005', $e->responseCode);
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testFasterPaymentsInvalidCurrency()
    {
        $bankPayment = $this->fasterPaymentsConfig();

        $errorFound = false;
        try {
            $bankPayment->charge($this->amount)
                ->withCurrency('EUR')
                ->withRemittanceReference(RemittanceReferenceType::TEXT, 'Nike Bounce Shoes')
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals('Status Code: SYSTEM_ERROR_DOWNSTREAM - Unable to process your request due to an error with a system down stream.', $e->getMessage());
            $this->assertEquals('50046', $e->responseCode);
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testSEPAChargeMissingIban()
    {
        $bankPayment = $this->sepaConfig();
        $bankPayment->iban = null;

        $errorFound = false;
        try {
            $bankPayment->charge($this->amount)
                ->withCurrency('EUR')
                ->withRemittanceReference(RemittanceReferenceType::TEXT, 'Nike Bounce Shoes')
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals('Status Code: SYSTEM_ERROR_DOWNSTREAM - Unable to process your request due to an error with a system down stream.', $e->getMessage());
            $this->assertEquals('50046', $e->responseCode);
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testSEPAChargeMissingAccountName()
    {
        $bankPayment = $this->sepaConfig();
        $bankPayment->accountName = null;

        $errorFound = false;
        try {
            $bankPayment->charge($this->amount)
                ->withCurrency('EUR')
                ->withRemittanceReference(RemittanceReferenceType::TEXT, 'Nike Bounce Shoes')
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals('Status Code: MANDATORY_DATA_MISSING - Request expects the following fields payment_method.bank_transfer.bank.name', $e->getMessage());
            $this->assertEquals('40005', $e->responseCode);
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testSEPAChargeInvalidCurrency()
    {
        $bankPayment = $this->sepaConfig();

        $errorFound = false;
        try {
            $bankPayment->charge($this->amount)
                ->withCurrency($this->currency)
                ->withRemittanceReference(RemittanceReferenceType::TEXT, 'Nike Bounce Shoes')
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals('Status Code: SYSTEM_ERROR_DOWNSTREAM - Unable to process your request due to an error with a system down stream.', $e->getMessage());
            $this->assertEquals('50046', $e->responseCode);
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testSEPAChargeCADCurrency()
    {
        $bankPayment = $this->sepaConfig();

        $errorFound = false;
        try {
            $bankPayment->charge($this->amount)
                ->withCurrency('CAD')
                ->withRemittanceReference(RemittanceReferenceType::TEXT, 'Nike Bounce Shoes')
                ->execute();
        } catch (GatewayException $e) {
            $errorFound = true;
            $this->assertEquals('Status Code: SYSTEM_ERROR_DOWNSTREAM - Unable to process your request due to an error with a system down stream.', $e->getMessage());
            $this->assertEquals('50046', $e->responseCode);
        } finally {
            $this->assertTrue($errorFound);
        }
    }
}
