<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Reporting\{
    SearchCriteria,
    TransactionSummary
};
use GlobalPayments\Api\Entities\{
    Address,
    InstallmentData,
    StoredCredential
};
use GlobalPayments\Api\Entities\Enums\{
    Channel,
    StoredCredentialInitiator,
    StoredCredentialReason,
    StoredCredentialSequence,
    StoredCredentialType,
    TransactionStatus
};
use DateTime;
use GlobalPayments\Api\Entities\GpApi\AccessTokenInfo;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Utils\GenerationUtils;
use GlobalPayments\Api\Utils\Logging\RequestConsoleLogger;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Services\ReportingService;
use PHPUnit\Framework\TestCase;

class GpApiInstallmentTest extends TestCase
{
    /**
     * @var CreditCardData $visaCard
     */
    private CreditCardData $visaCard;

    /**
     * @var CreditCardData $masterCard
     */
    private CreditCardData $masterCard;

    /**
     * @var CreditCardData $carnetCard
     */
    private CreditCardData $carnetCard;

    /**
     * @var StoredCredential $storeCredentials
     */
    private StoredCredential $storeCredentials;

    /**
     * @var Address $address
     */
    private Address $address;

    /**
     * @var InstallmentData $installment
     */
    private InstallmentData $installment;

    /**
     * @var string
     */
    private string $idempotencyKey;
    private DateTime $startDate;
    private DateTime $endDate;
    private string $currency = 'MXN';
    private float $amount = 2.02;

    public function setup(): void
    {
        ServicesContainer::configureService($this->setUpConfig());
        $this->startDate = (new DateTime())->modify('-30 days')->setTime(0, 0, 0);
        $this->endDate = (new DateTime())->modify('-3 days')->setTime(0, 0, 0);

        $this->address = new Address();
        $this->address->streetAddress1 = "123 Main St.";
        $this->address->city = "Downtown";
        $this->address->state = "NJ";
        $this->address->postalCode = "12345";

        $this->installment = new InstallmentData();
        $this->installment->program = "SIP";
        $this->installment->mode = "INTEREST";
        $this->installment->count = "99";
        $this->installment->grace_period_count = "30";

        $this->storeCredentials = new StoredCredential();
        $this->storeCredentials->initiator = StoredCredentialInitiator::PAYER;
        $this->storeCredentials->type = StoredCredentialType::INSTALLMENT;
        $this->storeCredentials->sequence = StoredCredentialSequence::SUBSEQUENT;
        $this->storeCredentials->reason = StoredCredentialReason::INCREMENTAL;

        $this->visaCard = new CreditCardData();
        $this->visaCard->number = "4915669522406071";
        $this->visaCard->expMonth = 04;
        $this->visaCard->expYear = 2026;
        $this->visaCard->cvn = "123";
        $this->visaCard->cardPresent = false;
        $this->visaCard->readerPresent = false;

        $this->masterCard = new CreditCardData();
        $this->masterCard->number = "5579083004810368";
        $this->masterCard->expMonth = 12;
        $this->masterCard->expYear = 2026;
        $this->masterCard->cvn = "123";
        $this->masterCard->cardPresent = false;
        $this->masterCard->readerPresent = false;

        $this->carnetCard = new CreditCardData();
        $this->carnetCard->number = "6363181868200169";
        $this->carnetCard->expMonth = 01;
        $this->carnetCard->expYear = 2030;
        $this->carnetCard->cvn = "123";
        $this->carnetCard->cardPresent = false;
        $this->carnetCard->readerPresent = false;
      
        $this->idempotencyKey = GenerationUtils::getGuid();
    }

    /** START Sale Test Cases for Installment **/
    public function testCreditSaleForInstallmentVisa()
    {
        $response = $this->visaCard->charge($this->amount)
            ->withCurrency($this->currency)
            ->withAddress($this->address)
            ->withInstallment($this->installment)
            ->withStoredCredential($this->storeCredentials)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
        $this->assertNotNull($response->installment->program);
        $this->assertNotNull($response->installment->mode);
        $this->assertNotNull($response->installment->count);
        $this->assertNotNull($response->installment->grace_period_count);
    }

    public function testCreditSaleForInstallmentMC()
    {
        $response = $this->masterCard->charge($this->amount)
            ->withCurrency("MXN")
            ->withAddress($this->address)
            ->withInstallment($this->installment)
            ->withStoredCredential($this->storeCredentials)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
        $this->assertNotNull($response->installment->program);
        $this->assertNotNull($response->installment->mode);
        $this->assertNotNull($response->installment->count);
        $this->assertNotNull($response->installment->grace_period_count);
    }

    public function testCreditSaleForInstallmentCarnet()
    {
        $response = $this->carnetCard->charge($this->amount)
            ->withCurrency("MXN")
            ->withAddress($this->address)
            ->withInstallment($this->installment)
            ->withStoredCredential($this->storeCredentials)
            ->withAllowDuplicates(true)
            ->execute();
          
        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
        $this->assertNotNull($response->installment->program);
        $this->assertNotNull($response->installment->mode);
        $this->assertNotNull($response->installment->count);
        $this->assertNotNull($response->installment->grace_period_count);
    }

    public function testCreditSaleWithoutInstallmentData()
    {
        $response = $this->visaCard->charge($this->amount)
            ->withCurrency($this->currency)
            ->withAddress($this->address)
            ->withAllowDuplicates(true)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('SUCCESS', $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
        $this->assertNull($response->installment);
    }

    /** END Sale Test Cases for Installment **/

    /** START Reporting Test Cases **/
    public function testReportTransactionDetailForInstallmentById()
    {
        $response = $this->visaCard->charge($this->amount)
            ->withCurrency($this->currency)
            ->withAddress($this->address)
            ->withInstallment($this->installment)
            ->withStoredCredential($this->storeCredentials)
            ->execute();

        $transaction = ReportingService::transactionDetail($response->transactionId)->execute();
        $this->assertNotNull($transaction->transactionId);
        $this->assertNotNull($transaction->installment);
        $this->assertInstanceOf(TransactionSummary::class, $transaction);
        $this->assertEquals($response->transactionId, $transaction->transactionId);
    }

    public function testReportTransactionDetailWithoutInstallmentDataById()
    {
        $response = $this->visaCard->charge($this->amount)
            ->withCurrency($this->currency)
            ->withAddress($this->address)
            ->execute();

        $transaction = ReportingService::transactionDetail($response->transactionId)->execute();
        $this->assertNotNull($transaction->transactionId);
        $this->assertNotNull($transaction->installment);
        $this->assertInstanceOf(TransactionSummary::class, $transaction);
        $this->assertEquals($response->transactionId, $transaction->transactionId);
    }

    public function testReportforInstallmentTransactionById()
    {
        $transactionId = 'TRN_Kg6l6MmsbY1SvXz5zarLDj79uiaYzu';
        try {
            /** @var TransactionSummary $response */
           $response = ReportingService::transactionDetail($transactionId)->execute();
        
        } catch (ApiException $e) {
            $this->fail("Installment Transaction by ID report failed with " . $e->getMessage());
        }

        $this->assertNotNull($response);
        $this->assertNotNull($response->installment);
        $this->assertInstanceOf(TransactionSummary::class, $response);
        $this->assertEquals($transactionId, $response->transactionId);
    }

    public function testForStatusInThreeDSecure()
    {
        $transactionStatus = TransactionStatus::AUTHENTICATED;
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->where(SearchCriteria::TRANSACTION_STATUS, $transactionStatus)
                ->andWith(SearchCriteria::START_DATE, $this->startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail("Find Status In Authentication ThreeDSecure Report failed with " . $e->getMessage());
        }

        $this->assertNotNull($response);
    }
    
    public function testReportTransactionsDetailForInstallment()
    {
        try {
            $response = ReportingService::findTransactionsPaged(1, 10)
                ->where(SearchCriteria::START_DATE, $this->startDate)
                ->execute();
        } catch (ApiException $e) {
            $this->fail("Installment transactions list Report failed with " . $e->getMessage());
        }

        $this->assertNotNull($response);
    }

    /** END Reporting Test Cases **/
 
    public function setUpConfig(): GpApiConfig
    {
        $config = BaseGpApiTestConfig::gpApiSetupConfig(Channel::CardNotPresent);
        $config->country = 'MX';
        $config->appId = 'Vw9O4jOMqozC39Grx8q3oGAvqEjLcgGn';
        $config->appKey = 'qgvDUwIhgT8QS2kp';
        $config->serviceUrl = 'https://apis-sit.globalpay.com/ucp';
        $config->accessTokenInfo = new AccessTokenInfo();
        $config->accessTokenInfo->riskAssessmentAccountName = 'EOS_RiskAssessment';
        $config->accessTokenInfo->transactionProcessingAccountName = 'Portico_SIT_405352';
        $config->accessTokenInfo->transactionProcessingAccountID = 'TRA_ba4aa4dd3cd1426e9eecba3abbd2053c';
        $config->accessTokenInfo->merchantManagementAccountID = 'MER_e3b91f1af988437f85d000eb272b777d';

        $config->requestLogger = new RequestConsoleLogger();
        return $config;
    }
}