<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\PaymentMethodName;
use GlobalPayments\Api\Entities\Enums\PaymentProvider;
use GlobalPayments\Api\Entities\Enums\RemittanceReferenceType;
use GlobalPayments\Api\Entities\Enums\TransactionSortProperty;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
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

    public function setup() : void
    {
        ServicesContainer::configureService($this->setUpConfig());
        $this->startDate = (new \DateTime())->modify('-30 days')->setTime(0, 0, 0);
        $this->endDate = (new \DateTime())->modify('-3 days')->setTime(0, 0, 0);
    }

    public static function tearDownAfterClass()
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
}