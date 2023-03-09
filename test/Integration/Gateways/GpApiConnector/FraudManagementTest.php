<?php

namespace Gateways\GpApiConnector;

use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Entities\Enums\Channel;
use GlobalPayments\Api\Entities\Enums\Environment;
use GlobalPayments\Api\Entities\Enums\FraudFilterMode;
use GlobalPayments\Api\Entities\Enums\FraudFilterResult;
use GlobalPayments\Api\Entities\Enums\ReasonCode;
use GlobalPayments\Api\Entities\Enums\TransactionSortProperty;
use GlobalPayments\Api\Entities\Enums\TransactionStatus;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\FraudRuleCollection;
use GlobalPayments\Api\Entities\GpApi\AccessTokenInfo;
use GlobalPayments\Api\Entities\GpApi\PagedResult;
use GlobalPayments\Api\Entities\Reporting\SearchCriteria;
use GlobalPayments\Api\Entities\Reporting\TransactionSummary;
use GlobalPayments\Api\Entities\Transaction;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;
use GlobalPayments\Api\Services\ReportingService;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Tests\Data\BaseGpApiTestConfig;
use GlobalPayments\Api\Utils\GenerationUtils;
use GlobalPayments\Api\Utils\Logging\Logger;
use GlobalPayments\Api\Utils\Logging\SampleRequestLogger;
use PHPUnit\Framework\TestCase;

class FraudManagementTest extends TestCase
{
    /** @var CreditCardData */
    private $card;
    /** @var Address $address */
    private $address;
    /** @var string */
    private $currency = 'USD';

    public function setup(): void
    {
        ServicesContainer::configureService($this->setUpConfig());
        $this->card = new CreditCardData();
        $this->card->number = "4263970000005262";
        $this->card->expMonth = date('m');
        $this->card->expYear = date('Y', strtotime('+1 year'));
        $this->card->cvn = "131";
        $this->card->cardHolderName = "James Mason";

        $this->address = new Address();
        $this->address->streetAddress1 = "123 Main St.";
        $this->address->city = "Downtown";
        $this->address->state = "NJ";
        $this->address->country = "US";
        $this->address->postalCode = "12345";
    }

    public static function tearDownAfterClass(): void
    {
        BaseGpApiTestConfig::resetGpApiConfig();
    }

    private function setUpConfig()
    {
        $config = new GpApiConfig();
        $config->appId = 'Q18DcsJvh8TtRo9zxICvg9S78S3RN8u2';
        $config->appKey = 'CFaMNPgpPN4KXibu';
        $config->environment = Environment::TEST;
        $config->channel = Channel::CardNotPresent;
        $accessTokenInfo = new AccessTokenInfo();
        $accessTokenInfo->transactionProcessingAccountName = 'transaction_processing';
        $config->accessTokenInfo = $accessTokenInfo;
        $config->requestLogger = new SampleRequestLogger(new Logger("logs"));

        return $config;
    }

    public function testFraudManagementDataSubmissions()
    {
        $fraudFilters = [
            FraudFilterMode::ACTIVE => FraudFilterResult::PASS,
            FraudFilterMode::PASSIVE => FraudFilterResult::PASS,
            FraudFilterMode::OFF => ''
        ];
        foreach ($fraudFilters as $fraudFilterMode => $fraudFilterStatus) {
            $response = $this->card->charge(98.10)
                ->withCurrency($this->currency)
                ->withAddress($this->address)
                ->withFraudFilter($fraudFilterMode)
                ->execute();

            $this->assertNotNull($response);
            $this->assertEquals("SUCCESS", $response->responseCode);
            $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
            $this->assertNotNull($response->fraudFilterResponse);
            $this->assertEquals($fraudFilterMode, $response->fraudFilterResponse->fraudResponseMode);
            $this->assertEquals($fraudFilterStatus, $response->fraudFilterResponse->fraudResponseResult);
        }
    }

    public function testFraudManagementDataSubmissionWithRules()
    {
        $rule1 = '2c49c2e6-5843-4275-9b92-8c9b6dc8e566';
        $rule2 = '2cfa3a28-f8f3-42f8-abbf-79b54e35de16';

        $rules = new FraudRuleCollection();
        $rules->addRule($rule1, FraudFilterMode::ACTIVE);
        $rules->addRule($rule2, FraudFilterMode::OFF);

        $response = $this->card->charge(98.10)
            ->withCurrency($this->currency)
            ->withAddress($this->address)
            ->withFraudFilter(FraudFilterMode::ACTIVE, $rules)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("SUCCESS", $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
        $this->assertNotNull($response->fraudFilterResponse);
        $this->assertEquals(FraudFilterMode::ACTIVE, $response->fraudFilterResponse->fraudResponseMode);

        $this->assertEquals(FraudFilterResult::PASS, $response->fraudFilterResponse->fraudResponseResult);
        foreach ($response->fraudFilterResponse->fraudResponseRules as $fraudResponseRule) {
            if ($fraudResponseRule->key == $rule1) {
                $this->assertEquals(FraudFilterResult::PASS, $fraudResponseRule->result);
            }
            if ($fraudResponseRule->key == $rule2) {
                $this->assertEquals(FraudFilterResult::NOT_EXECUTED, $fraudResponseRule->result);
            }
        }
    }

    public function testFraudManagementDataSubmissionWith_AllRulesActive()
    {
        $ruleList = [
            '2c49c2e6-5843-4275-9b92-8c9b6dc8e566',
            '2cfa3a28-f8f3-42f8-abbf-79b54e35de16',
            '21db158b-4541-4217-aa81-927596465547',
            '6acbcb2e-79c7-40c3-8c17-b65c5fba2a54',
            'a7da55fb-69c4-4c41-abb6-c4dded40354e'
        ];

        $rules = new FraudRuleCollection();
        foreach ($ruleList as $rule) {
            $rules->addRule($rule, FraudFilterMode::ACTIVE);
        }

        $response = $this->card->charge(10.10)
            ->withCurrency($this->currency)
            ->withAddress($this->address)
            ->withFraudFilter(FraudFilterMode::ACTIVE, $rules)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("SUCCESS", $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
        $this->assertNotNull($response->fraudFilterResponse);
        $this->assertEquals(FraudFilterMode::ACTIVE, $response->fraudFilterResponse->fraudResponseMode);

        $this->assertEquals(FraudFilterResult::PASS, $response->fraudFilterResponse->fraudResponseResult);
        foreach ($response->fraudFilterResponse->fraudResponseRules as $fraudResponseRule) {
            $this->assertContains($fraudResponseRule->key, $ruleList);
        }
    }

    public function testFraudManagementDataSubmissionWith_AllRulesOff()
    {
        $ruleList = [
            '2c49c2e6-5843-4275-9b92-8c9b6dc8e566',
            '2cfa3a28-f8f3-42f8-abbf-79b54e35de16',
            '21db158b-4541-4217-aa81-927596465547',
            '6acbcb2e-79c7-40c3-8c17-b65c5fba2a54',
            'a7da55fb-69c4-4c41-abb6-c4dded40354e'
        ];

        $rules = new FraudRuleCollection();
        foreach ($ruleList as $rule) {
            $rules->addRule($rule, FraudFilterMode::OFF);
        }

        $response = $this->card->charge(98.10)
            ->withCurrency($this->currency)
            ->withAddress($this->address)
            ->withFraudFilter(FraudFilterMode::ACTIVE, $rules)
            ->execute();

        $this->assertNotNull($response);
        $this->assertEquals("SUCCESS", $response->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $response->responseMessage);
        $this->assertNotNull($response->fraudFilterResponse);
        $this->assertEquals(FraudFilterMode::ACTIVE, $response->fraudFilterResponse->fraudResponseMode);
        $this->assertEquals(FraudFilterResult::NOT_EXECUTED, $response->fraudFilterResponse->fraudResponseResult);
        foreach ($response->fraudFilterResponse->fraudResponseRules as $fraudResponseRule) {
            $this->assertContains($fraudResponseRule->key, $ruleList);
            $this->assertEquals(FraudFilterResult::NOT_EXECUTED, $fraudResponseRule->result);
        }
    }

    public function testFraudManagementDataSubmissionFullCycle()
    {
        $trn = $this->card->authorize(15.10)
            ->withCurrency($this->currency)
            ->withAddress($this->address)
            ->withFraudFilter(FraudFilterMode::ACTIVE)
            ->execute();

        $this->assertNotNull($trn);
        $this->assertEquals("SUCCESS", $trn->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $trn->responseMessage);
        $this->assertNotNull($trn->fraudFilterResponse);
        $this->assertEquals(FraudFilterMode::ACTIVE, $trn->fraudFilterResponse->fraudResponseMode);
        $this->assertEquals(FraudFilterResult::PASS, $trn->fraudFilterResponse->fraudResponseResult);

        $trn = $trn->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->execute();

        $this->assertNotNull($trn);
        $this->assertEquals("SUCCESS", $trn->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $trn->responseMessage);
        $this->assertNotNull($trn->fraudFilterResponse);
        $this->assertEquals(FraudFilterResult::HOLD_SUCCESSFUL, $trn->fraudFilterResponse->fraudResponseResult);

        $trn = $trn->release()
            ->withReasonCode(ReasonCode::FALSE_POSITIVE)
            ->execute();

        $this->assertNotNull($trn);
        $this->assertEquals("SUCCESS", $trn->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $trn->responseMessage);
        $this->assertNotNull($trn->fraudFilterResponse);
        $this->assertEquals(FraudFilterResult::RELEASE_SUCCESSFUL, $trn->fraudFilterResponse->fraudResponseResult);

        $trn = $trn->capture()->execute();

        $this->assertNotNull($trn);
        $this->assertEquals("SUCCESS", $trn->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $trn->responseMessage);
    }

    public function testFraudManagementDataSubmissionFullCycle_HoldAndReleaseWithoutReasonCode()
    {
        $trn = $this->card->authorize(98.10)
            ->withCurrency($this->currency)
            ->withAddress($this->address)
            ->withFraudFilter(FraudFilterMode::ACTIVE)
            ->execute();

        $this->assertNotNull($trn);
        $this->assertEquals("SUCCESS", $trn->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $trn->responseMessage);
        $this->assertNotNull($trn->fraudFilterResponse);
        $this->assertEquals(FraudFilterMode::ACTIVE, $trn->fraudFilterResponse->fraudResponseMode);
        $this->assertEquals(FraudFilterResult::PASS, $trn->fraudFilterResponse->fraudResponseResult);

        $trn = $trn->hold()
            ->execute();

        $this->assertNotNull($trn);
        $this->assertEquals("SUCCESS", $trn->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $trn->responseMessage);
        $this->assertNotNull($trn->fraudFilterResponse);
        $this->assertEquals(FraudFilterResult::HOLD_SUCCESSFUL, $trn->fraudFilterResponse->fraudResponseResult);

        $trn = $trn->release()
            ->execute();

        $this->assertNotNull($trn);
        $this->assertEquals("SUCCESS", $trn->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $trn->responseMessage);
        $this->assertNotNull($trn->fraudFilterResponse);
        $this->assertEquals(FraudFilterResult::RELEASE_SUCCESSFUL, $trn->fraudFilterResponse->fraudResponseResult);

        $trn = $trn->capture()->execute();

        $this->assertNotNull($trn);
        $this->assertEquals("SUCCESS", $trn->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $trn->responseMessage);
    }

    public function testCaptureTransactionAfterFraudResultHold()
    {
        $trn = $this->card->authorize(10.10)
            ->withCurrency($this->currency)
            ->withAddress($this->address)
            ->withFraudFilter(FraudFilterMode::ACTIVE)
            ->withCustomerIpAddress('123.123.123.123')
            ->execute();

        $this->assertNotNull($trn);
        $this->assertEquals("SUCCESS", $trn->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $trn->responseMessage);
        $this->assertNotNull($trn->fraudFilterResponse);
        $this->assertEquals(FraudFilterMode::ACTIVE, $trn->fraudFilterResponse->fraudResponseMode);
        $this->assertEquals(FraudFilterResult::HOLD, $trn->fraudFilterResponse->fraudResponseResult);

        $errorFound = false;
        try {
            $trn->capture()->execute();
        } catch (ApiException $e) {
            $this->assertEquals('50020', $e->responseCode);
            $this->assertStringContainsString('This transaction has been held', $e->getMessage());
            $errorFound = true;
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testRefundTransactionAfterFraudResultHold()
    {
        $trn = $this->card->charge(10.10)
            ->withCurrency($this->currency)
            ->withAddress($this->address)
            ->withFraudFilter(FraudFilterMode::ACTIVE)
            ->withCustomerIpAddress('123.123.123.123')
            ->execute();

        $this->assertNotNull($trn);
        $this->assertEquals("SUCCESS", $trn->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $trn->responseMessage);
        $this->assertNotNull($trn->fraudFilterResponse);
        $this->assertEquals(FraudFilterMode::ACTIVE, $trn->fraudFilterResponse->fraudResponseMode);
        $this->assertEquals(FraudFilterResult::HOLD, $trn->fraudFilterResponse->fraudResponseResult);

        $errorFound = false;
        try {
            $trn->refund()
                ->withCurrency($this->currency)
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('50017', $e->responseCode);
            $this->assertEquals('Status Code: SYSTEM_ERROR_DOWNSTREAM - The refund password you entered was incorrect ', $e->getMessage());
            $errorFound = true;
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testFraudManagementDataSubmissionFullCycle_Charge()
    {
        $trn = $this->card->charge(98.10)
            ->withCurrency($this->currency)
            ->withAddress($this->address)
            ->withFraudFilter(FraudFilterMode::ACTIVE)
            ->execute();

        $this->assertNotNull($trn);
        $this->assertEquals("SUCCESS", $trn->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $trn->responseMessage);
        $this->assertNotNull($trn->fraudFilterResponse);
        $this->assertEquals(FraudFilterMode::ACTIVE, $trn->fraudFilterResponse->fraudResponseMode);
        $this->assertEquals(FraudFilterResult::PASS, $trn->fraudFilterResponse->fraudResponseResult);

        $trn = $trn->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->execute();

        $this->assertNotNull($trn);
        $this->assertEquals("SUCCESS", $trn->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $trn->responseMessage);
        $this->assertNotNull($trn->fraudFilterResponse);
        $this->assertEquals(FraudFilterResult::HOLD_SUCCESSFUL, $trn->fraudFilterResponse->fraudResponseResult);

        $trn = $trn->release()
            ->withReasonCode(ReasonCode::FALSE_POSITIVE)
            ->execute();

        $this->assertNotNull($trn);
        $this->assertEquals("SUCCESS", $trn->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $trn->responseMessage);
        $this->assertNotNull($trn->fraudFilterResponse);
        $this->assertEquals(FraudFilterResult::RELEASE_SUCCESSFUL, $trn->fraudFilterResponse->fraudResponseResult);
    }

    public function testRefundFraudManagementDataSubmissionFullCycle_Charge()
    {
        $trn = $this->card->charge(1)
            ->withCurrency($this->currency)
            ->withAddress($this->address)
            ->withFraudFilter(FraudFilterMode::ACTIVE)
            ->execute();

        $this->assertNotNull($trn);
        $this->assertEquals("SUCCESS", $trn->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $trn->responseMessage);
        $this->assertNotNull($trn->fraudFilterResponse);
        $this->assertEquals(FraudFilterMode::ACTIVE, $trn->fraudFilterResponse->fraudResponseMode);
        $this->assertEquals(FraudFilterResult::PASS, $trn->fraudFilterResponse->fraudResponseResult);

        $trn = $trn->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->execute();

        $this->assertNotNull($trn);
        $this->assertEquals("SUCCESS", $trn->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $trn->responseMessage);
        $this->assertNotNull($trn->fraudFilterResponse);
        $this->assertEquals(FraudFilterResult::HOLD_SUCCESSFUL, $trn->fraudFilterResponse->fraudResponseResult);

        $trn = $trn->release()
            ->withReasonCode(ReasonCode::FALSE_POSITIVE)
            ->execute();

        $this->assertNotNull($trn);
        $this->assertEquals("SUCCESS", $trn->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $trn->responseMessage);
        $this->assertNotNull($trn->fraudFilterResponse);
        $this->assertEquals(FraudFilterResult::RELEASE_SUCCESSFUL, $trn->fraudFilterResponse->fraudResponseResult);

        $errorFound = false;
        try {
            $trn->refund()
                ->withCurrency($this->currency)
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('50017', $e->responseCode);
            $this->assertEquals('Status Code: SYSTEM_ERROR_DOWNSTREAM - The refund password you entered was incorrect ', $e->getMessage());
            $errorFound = true;
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testFraudManagementDataSubmissionFullCycle_ChargePassive()
    {
        $trn = $this->card->charge(98.10)
            ->withCurrency($this->currency)
            ->withAddress($this->address)
            ->withFraudFilter(FraudFilterMode::PASSIVE)
            ->execute();

        $this->assertNotNull($trn);
        $this->assertEquals("SUCCESS", $trn->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $trn->responseMessage);
        $this->assertNotNull($trn->fraudFilterResponse);
        $this->assertEquals(FraudFilterMode::PASSIVE, $trn->fraudFilterResponse->fraudResponseMode);
        $this->assertEquals(FraudFilterResult::PASS, $trn->fraudFilterResponse->fraudResponseResult);

        $trn = $trn->hold()
            ->withReasonCode(ReasonCode::FRAUD)
            ->execute();

        $this->assertNotNull($trn);
        $this->assertEquals("SUCCESS", $trn->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $trn->responseMessage);
        $this->assertNotNull($trn->fraudFilterResponse);
        $this->assertEquals(FraudFilterResult::HOLD_SUCCESSFUL, $trn->fraudFilterResponse->fraudResponseResult);

        $trn = $trn->release()
            ->withReasonCode(ReasonCode::FALSE_POSITIVE)
            ->execute();

        $this->assertNotNull($trn);
        $this->assertEquals("SUCCESS", $trn->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $trn->responseMessage);
        $this->assertNotNull($trn->fraudFilterResponse);
        $this->assertEquals(FraudFilterResult::RELEASE_SUCCESSFUL, $trn->fraudFilterResponse->fraudResponseResult);
    }

    public function testFraudManagement_Charge_ReleaseWithoutHold()
    {
        $trn = $this->card->charge(1)
            ->withCurrency($this->currency)
            ->withAddress($this->address)
            ->withFraudFilter(FraudFilterMode::ACTIVE)
            ->execute();

        $this->assertNotNull($trn);
        $this->assertEquals("SUCCESS", $trn->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $trn->responseMessage);
        $this->assertNotNull($trn->fraudFilterResponse);
        $this->assertEquals(FraudFilterMode::ACTIVE, $trn->fraudFilterResponse->fraudResponseMode);
        $this->assertEquals(FraudFilterResult::PASS, $trn->fraudFilterResponse->fraudResponseResult);

        $errorFound = false;
        try {
            $trn->release()
                ->withReasonCode(ReasonCode::FALSE_POSITIVE)
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('50020', $e->responseCode);
            $this->assertEquals('Status Code: INVALID_REQUEST_DATA - Cant release transaction that is not held', $e->getMessage());
            $errorFound = true;
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testFraudManagement_Authorize_ReleaseWithoutHold()
    {
        $trn = $this->card->authorize(1)
            ->withCurrency($this->currency)
            ->withAddress($this->address)
            ->withFraudFilter(FraudFilterMode::ACTIVE)
            ->execute();

        $this->assertNotNull($trn);
        $this->assertEquals("SUCCESS", $trn->responseCode);
        $this->assertEquals(TransactionStatus::PREAUTHORIZED, $trn->responseMessage);
        $this->assertNotNull($trn->fraudFilterResponse);
        $this->assertEquals(FraudFilterMode::ACTIVE, $trn->fraudFilterResponse->fraudResponseMode);
        $this->assertEquals(FraudFilterResult::PASS, $trn->fraudFilterResponse->fraudResponseResult);

        $errorFound = false;
        try {
            $trn->release()
                ->withReasonCode(ReasonCode::FALSE_POSITIVE)
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('50020', $e->responseCode);
            $this->assertEquals('Status Code: INVALID_REQUEST_DATA - Cant release transaction that is not held', $e->getMessage());
            $errorFound = true;
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testReleaseTransactionAfterFraudResultHold()
    {
        $trn = $this->card->charge(98.10)
            ->withCurrency($this->currency)
            ->withAddress($this->address)
            ->withFraudFilter(FraudFilterMode::ACTIVE)
            ->withCustomerIpAddress('123.123.123.123')
            ->execute();

        $this->assertNotNull($trn);
        $this->assertEquals("SUCCESS", $trn->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $trn->responseMessage);
        $this->assertNotNull($trn->fraudFilterResponse);
        $this->assertEquals(FraudFilterMode::ACTIVE, $trn->fraudFilterResponse->fraudResponseMode);
        $this->assertEquals(FraudFilterResult::HOLD, $trn->fraudFilterResponse->fraudResponseResult);

        $trn = $trn->release()
            ->withReasonCode(ReasonCode::FALSE_POSITIVE)
            ->execute();

        $this->assertNotNull($trn);
        $this->assertEquals("SUCCESS", $trn->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $trn->responseMessage);
        $this->assertNotNull($trn->fraudFilterResponse);
        $this->assertEquals(FraudFilterResult::RELEASE_SUCCESSFUL, $trn->fraudFilterResponse->fraudResponseResult);
    }

    public function testRelease_AllReasonCodes()
    {
        $releaseReasonCodes = ['FALSEPOSITIVE', 'INSTOCK', 'OTHER', 'NOTGIVEN'];
        foreach ($releaseReasonCodes as $value) {
            $trn = $this->card->charge(1)
                ->withCurrency($this->currency)
                ->withAddress($this->address)
                ->withFraudFilter(FraudFilterMode::ACTIVE)
                ->execute();

            $this->assertNotNull($trn);
            $this->assertEquals("SUCCESS", $trn->responseCode);
            $this->assertEquals(TransactionStatus::CAPTURED, $trn->responseMessage);
            $this->assertNotNull($trn->fraudFilterResponse);
            $this->assertEquals(FraudFilterMode::ACTIVE, $trn->fraudFilterResponse->fraudResponseMode);
            $this->assertEquals(FraudFilterResult::PASS, $trn->fraudFilterResponse->fraudResponseResult);

            $trn = $trn->hold()
                ->withReasonCode(ReasonCode::FRAUD)
                ->execute();

            $this->assertNotNull($trn);
            $this->assertEquals("SUCCESS", $trn->responseCode);
            $this->assertEquals(TransactionStatus::CAPTURED, $trn->responseMessage);
            $this->assertNotNull($trn->fraudFilterResponse);
            $this->assertEquals(FraudFilterResult::HOLD_SUCCESSFUL, $trn->fraudFilterResponse->fraudResponseResult);

            $trn = $trn->release()
                ->withReasonCode($value)
                ->execute();

            $this->assertNotNull($trn);
            $this->assertEquals("SUCCESS", $trn->responseCode);
            $this->assertEquals(TransactionStatus::CAPTURED, $trn->responseMessage);
            $this->assertNotNull($trn->fraudFilterResponse);
            $this->assertEquals(FraudFilterResult::RELEASE_SUCCESSFUL, $trn->fraudFilterResponse->fraudResponseResult);
        }
    }

    public function testRelease_RandomTransaction()
    {
        $trn = new Transaction();
        $trn->transactionId = GenerationUtils::getGuid();

        $errorFound = false;
        try {
            $trn->release()
                ->withReasonCode(ReasonCode::FALSE_POSITIVE)
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('40008', $e->responseCode);
            $this->assertEquals(sprintf('Status Code: RESOURCE_NOT_FOUND - Transaction %s not found at this location.', $trn->transactionId), $e->getMessage());
            $errorFound = true;
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testRelease_InvalidReason()
    {
        $trn = $this->card->charge(98.10)
            ->withCurrency($this->currency)
            ->withAddress($this->address)
            ->withFraudFilter(FraudFilterMode::ACTIVE)
            ->execute();

        $this->assertNotNull($trn);
        $this->assertEquals("SUCCESS", $trn->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $trn->responseMessage);
        $this->assertNotNull($trn->fraudFilterResponse);
        $this->assertEquals(FraudFilterMode::ACTIVE, $trn->fraudFilterResponse->fraudResponseMode);
        $this->assertEquals(FraudFilterResult::PASS, $trn->fraudFilterResponse->fraudResponseResult);

        $errorFound = false;
        try {
            $trn->release()
                ->withReasonCode(ReasonCode::FRAUD)
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('40259', $e->responseCode);
            $this->assertEquals('Status Code: INVALID_REQUEST_DATA - reason_code value is invalid. Please check the reason_code is entered correctly', $e->getMessage());
            $errorFound = true;
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testHoldTransactionAfterFraudResultHold()
    {
        $trn = $this->card->charge(98.10)
            ->withCurrency($this->currency)
            ->withAddress($this->address)
            ->withFraudFilter(FraudFilterMode::ACTIVE)
            ->withCustomerIpAddress('123.123.123.123')
            ->execute();

        $this->assertNotNull($trn);
        $this->assertEquals("SUCCESS", $trn->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $trn->responseMessage);
        $this->assertNotNull($trn->fraudFilterResponse);
        $this->assertEquals(FraudFilterMode::ACTIVE, $trn->fraudFilterResponse->fraudResponseMode);
        $this->assertEquals(FraudFilterResult::HOLD, $trn->fraudFilterResponse->fraudResponseResult);

        $errorFound = false;
        try {
            $trn->hold()
                ->withReasonCode(ReasonCode::FRAUD)
                ->execute();
        } catch (GatewayException $e) {
            $this->assertStringContainsString('This transaction is already held', $e->getMessage());
            $this->assertEquals('50020', $e->responseCode);
            $errorFound = true;
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testHold_AllReasonCodes()
    {
        $holdReasonCodes = ['FRAUD', 'OUTOFSTOCK', 'OTHER', 'NOTGIVEN'];
        foreach ($holdReasonCodes as $value) {
            $trn = $this->card->charge(98.10)
                ->withCurrency($this->currency)
                ->withAddress($this->address)
                ->withFraudFilter(FraudFilterMode::ACTIVE)
                ->execute();

            $this->assertNotNull($trn);
            $this->assertEquals("SUCCESS", $trn->responseCode);
            $this->assertEquals(TransactionStatus::CAPTURED, $trn->responseMessage);
            $this->assertNotNull($trn->fraudFilterResponse);
            $this->assertEquals(FraudFilterMode::ACTIVE, $trn->fraudFilterResponse->fraudResponseMode);
            $this->assertEquals(FraudFilterResult::PASS, $trn->fraudFilterResponse->fraudResponseResult);

            $trn = $trn->hold()
                ->withReasonCode($value)
                ->execute();

            $this->assertNotNull($trn);
            $this->assertEquals("SUCCESS", $trn->responseCode);
            $this->assertEquals(TransactionStatus::CAPTURED, $trn->responseMessage);
            $this->assertNotNull($trn->fraudFilterResponse);
            $this->assertEquals(FraudFilterResult::HOLD_SUCCESSFUL, $trn->fraudFilterResponse->fraudResponseResult);
        }
    }

    public function testHold_RandomTransaction()
    {
        $trn = new Transaction();
        $trn->transactionId = GenerationUtils::getGuid();

        $errorFound = false;
        try {
            $trn->hold()
                ->withReasonCode(ReasonCode::FALSE_POSITIVE)
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('40008', $e->responseCode);
            $this->assertEquals(sprintf('Status Code: RESOURCE_NOT_FOUND - Transaction %s not found at this location.', $trn->transactionId), $e->getMessage());
            $errorFound = true;
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testHold_InvalidReason()
    {
        $trn = $this->card->charge(98.10)
            ->withCurrency($this->currency)
            ->withAddress($this->address)
            ->withFraudFilter(FraudFilterMode::ACTIVE)
            ->execute();

        $this->assertNotNull($trn);
        $this->assertEquals("SUCCESS", $trn->responseCode);
        $this->assertEquals(TransactionStatus::CAPTURED, $trn->responseMessage);
        $this->assertNotNull($trn->fraudFilterResponse);
        $this->assertEquals(FraudFilterMode::ACTIVE, $trn->fraudFilterResponse->fraudResponseMode);
        $this->assertEquals(FraudFilterResult::PASS, $trn->fraudFilterResponse->fraudResponseResult);

        $errorFound = false;
        try {
            $trn->hold()
                ->withReasonCode(ReasonCode::IN_STOCK)
                ->execute();
        } catch (GatewayException $e) {
            $this->assertEquals('40259', $e->responseCode);
            $this->assertEquals('Status Code: INVALID_REQUEST_DATA - reason_code value is invalid. Please check the reason_code is entered correctly', $e->getMessage());
            $errorFound = true;
        } finally {
            $this->assertTrue($errorFound);
        }
    }

    public function testGetTransactionWithFraudCheck()
    {
        $startDate = (new \DateTime())->modify('-30 days');
        $endDate = (new \DateTime())->modify('-3 days');

        /** @var PagedResult $response */
        $response = ReportingService::findTransactionsPaged(1, 10)
            ->orderBy(TransactionSortProperty::TIME_CREATED)
            ->where(SearchCriteria::START_DATE, $startDate)
            ->andWith(SearchCriteria::END_DATE, $endDate)
            ->andWith(SearchCriteria::RISK_ASSESSMENT_RESULT, FraudFilterResult::HOLD)
            ->execute();

        $this->assertGreaterThan(0, count($response->result));
        /** @var TransactionSummary $trnSummary */
        $trnSummary = $response->result[rand(0, count($response->result) - 1)];
        $this->assertNotNull($trnSummary->fraudManagementResponse);
        $this->assertEquals(FraudFilterResult::HOLD, $trnSummary->fraudManagementResponse->fraudResponseResult);
    }
}