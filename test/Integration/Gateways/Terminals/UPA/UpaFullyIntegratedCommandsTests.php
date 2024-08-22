<?php

namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals\UPA;

use GlobalPayments\Api\Entities\Enums\CardType;
use GlobalPayments\Api\Entities\Enums\ManualEntryMethod;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Services\DeviceService;
use GlobalPayments\Api\Terminals\Abstractions\IDeviceInterface;
use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Entities\Button;
use GlobalPayments\Api\Terminals\Entities\GenericData;
use GlobalPayments\Api\Terminals\Entities\HostData;
use GlobalPayments\Api\Terminals\Entities\MessageLines;
use GlobalPayments\Api\Terminals\Entities\PromptButtons;
use GlobalPayments\Api\Terminals\Entities\PromptData;
use GlobalPayments\Api\Terminals\Entities\PromptMessages;
use GlobalPayments\Api\Terminals\Enums\AcquisitionType;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\DeviceType;
use GlobalPayments\Api\Terminals\Enums\DisplayOption;
use GlobalPayments\Api\Terminals\Enums\HostDecision;
use GlobalPayments\Api\Terminals\Enums\InputAlignment;
use GlobalPayments\Api\Terminals\Enums\MerchantDecision;
use GlobalPayments\Api\Terminals\Enums\PromptForManualEntryPassword;
use GlobalPayments\Api\Terminals\Enums\PromptType;
use GlobalPayments\Api\Terminals\Enums\TextFormat;
use GlobalPayments\Api\Terminals\UPA\Responses\TransactionResponse;
use GlobalPayments\Api\Tests\Data\TestCards;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;
use GlobalPayments\Api\Utils\Logging\TerminalLogManagement;
use PHPUnit\Framework\TestCase;

class UpaFullyIntegratedCommandsTests extends TestCase
{
    private IDeviceInterface $device;
    private CreditCardData $card;

    public function setup(): void
    {
        $this->device = DeviceService::create($this->getConfig());

        $this->card = new CreditCardData();
        $this->card->number = '4111111111111111';
        $this->card->expMonth = 12;
        $this->card->expYear = TestCards::validCardExpYear();
        $this->card->cvn = '123';
        $this->card->cardHolderName = 'Joe Smith';
        $this->card->entryMethod = ManualEntryMethod::MAIL;
    }

    public function tearDown(): void
    {
        sleep(3);
    }

    protected function getConfig()
    {
        $config = new ConnectionConfig();
        $config->ipAddress = '192.168.8.181';
        $config->port = '8081';
        $config->deviceType = DeviceType::UPA_VERIFONE_T650P;
        $config->connectionMode = ConnectionModes::TCP_IP;
        $config->timeout = 30;
        $config->requestIdProvider = new RequestIdProvider();
        $config->logManagementProvider = new TerminalLogManagement();

        return $config;
    }

    public function testRemoveCard()
    {
        $response = $this->device->removeCard('FR');

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
    }

    public function testEnterPin()
    {
        $prompts = new PromptMessages();
        $prompts->prompt1 = 'Enter PIN';
        $accountNumber = '1234567890123456';

        /** @var TransactionResponse $response */
        $response = $this->device->enterPIN($prompts, true, $accountNumber);

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
        $this->assertNotEmpty($response->pinDUKPT);
        $this->assertNotEmpty($response->pinDUKPT->pinBlock);
        $this->assertNotEmpty($response->pinDUKPT->ksn);
    }

    public function testPromptWithOptions()
    {
        $promptData = new PromptData();
        $promptData->prompts = new PromptMessages();
        $promptData->prompts->prompt1 = 'Prompt 1';
        $promptData->prompts->prompt2 = 'Prompt 2';
        $promptData->prompts->prompt3 = 'Prompt 3';
        $button1 = new Button();
        $button1->text = 'Yes';
        $button1->color = 'green';
        $button2 = new Button();
        $button2->text = 'No';
        $button2->color = 'red';
        $button3 = new Button();
        $button3->text = 'Cancel';
        $button3->color = 'blue';
        $promptData->buttons = new PromptButtons($button1, $button2, $button3);
        /** @var TransactionResponse $response */
        $response = $this->device->prompt(PromptType::OPTIONS, $promptData);

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
        $this->assertNotEmpty($response->buttonPressed);
    }

    public function testPromptMenu()
    {
        $promptData = new PromptData();
        $promptData->prompts = new PromptMessages();
        $promptData->prompts->prompt1 = 'Select Application';

        $button1 = new Button();
        $button1->text = 'Yes';
        $button1->color = 'green';
        $button2 = new Button();
        $button2->text = 'No';
        $button2->color = 'red';
        $button3 = new Button();
        $button3->text = 'Cancel';
        $button3->color = 'blue';
        $promptData->buttons = new PromptButtons($button1, $button2, $button3);
        $promptData->menu = ["Visa", "Mastercard"];

        $response = $this->device->prompt(PromptType::MENU, $promptData);

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
        $this->assertNotEmpty($response->promptMenuSelected);
    }

    public function testGeneralEntry()
    {
        $data = new GenericData();
        $data->prompts = new PromptMessages();
        $data->prompts->prompt1 = 'Enter Driver’s License';
        $data->textButton1 = 'Cancel';
        $data->textButton2 = 'OK';
        $data->timeout = 60;
        $data->entryFormat = [TextFormat::PASSWORD, TextFormat::ALPHANUMERIC];
        $data->entryMinLen = 10;
        $data->entryMaxLen = 20;
        $data->alignment = InputAlignment::RIGHT_TO_LEFT;
        $response = $this->device->getGenericEntry($data);

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
    }

    public function testDisplayMessage()
    {
        $messageLines = new MessageLines();
        $messageLines->line1 = 'Please wait...';
        $messageLines->timeout = 0;

        $response = $this->device->displayMessage($messageLines);

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
    }

    public function testReturnDefaultScreen()
    {
        $response = $this->device->returnDefaultScreen(DisplayOption::RETURN_TO_IDLE_SCREEN);

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
    }

    public function testGetEncryptionType()
    {
        $response = $this->device->getEncryptionType();

        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
        $this->assertNotEmpty($response->dataEncryptionType);
    }

    public function testStartCardTransactionSale()
    {
        $response = $this->device->startTransaction(10.01)
            ->withCashBack(0.1)
            ->withQuickChip(false)
            ->withCheckLuhn(false)
            ->withSecurityCode(false)
            ->withCardTypeFilter([CardType::VISA, CardType::MASTERCARD])
            ->withTransactionDate(new \DateTime('now'))
            ->withAcquisitionTypes([AcquisitionType::MANUAL])
            ->execute();

        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
    }

    public function testContinueEMVTransaction()
    {
        $response = $this->device->continueTransaction(10.01, true)
            ->withCashBack(0.1)
            ->withQuickChip(false)
            ->withMerchantDecision(MerchantDecision::APPROVED)
            ->withLanguage("EN")
            ->execute();

        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
        $this->assertNotEmpty($response->emvTags);
    }

    public function testCompleteEMVTransaction()
    {
        $hostData = new HostData();
        $hostData->hostDecision = HostDecision::APPROVED;
        $hostData->issuerAuthData = 'xxx';
        $hostData->issuerScripts = 'aaa';

        $response = $this->device->completeTransaction()
            ->withQuickChip(false)
            ->withLanguage("EN")
            ->withHostData($hostData)
            ->execute();

        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
        $this->assertNotEmpty($response->emvTags);
    }

    public function testStartCardTransactionRefund()
    {
        /** @var TransactionResponse $response */
        $response = $this->device->startTransaction(10.01, TransactionType::REFUND)
            ->withCashBack(0.1)
            ->withQuickChip(false)
            ->withCheckLuhn(true)
            ->withSecurityCode(false)
            ->withCardTypeFilter([CardType::VISA, CardType::MASTERCARD])
            ->withTimeout(0)
            ->withAcquisitionTypes([AcquisitionType::CONTACT, AcquisitionType::CONTACTLESS])
            ->withDisplayTotalAmount(true)
            ->withPromptForManualEntryPassword(PromptForManualEntryPassword::DONT_PROMPT)
            ->execute();

        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
        $this->assertTrue($response->luhnCheckPassed);
    }

    public function testProcessCardTransactionSale()
    {
        /** @var TransactionResponse $response */
        $response = $this->device->processTransaction(10.01)
            ->withAcquisitionTypes([AcquisitionType::MANUAL])
            ->withTimeout(3)
            ->withMerchantDecision(MerchantDecision::APPROVED)
            ->withQuickChip(true)
            ->withCheckLuhn(false)
            ->withCashBack(0.1)
            ->withTransactionDate(new \DateTime('now'))
            ->execute();

        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
    }

    public function testContinueCardTransaction()
    {
        /** @var TransactionResponse $response */
        $response = $this->device->continueTransaction(10.01)
            ->withCashBack(0.1)
            ->withMerchantDecision(MerchantDecision::APPROVED)
            ->withLanguage("EN")
            ->execute();

        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('Success', $response->status);
        $this->assertNotEmpty($response->emvTags);
    }
}