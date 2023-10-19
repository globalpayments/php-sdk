<?php

namespace GlobalPayments\Api\Terminals;

use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\NotImplementedException;
use GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException;
use GlobalPayments\Api\Terminals\Abstractions\IBatchCloseResponse;
use GlobalPayments\Api\Terminals\Builders\TerminalAuthBuilder;
use GlobalPayments\Api\Terminals\Builders\TerminalManageBuilder;
use GlobalPayments\Api\Terminals\Enums\CurrencyType;
use GlobalPayments\Api\Terminals\Abstractions\IDeviceInterface;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;

abstract class DeviceInterface implements IDeviceInterface
{
    /** @var DeviceController */
    public DeviceController $controller;

    /** @var RequestIdProvider */
    public $requestIdProvider;

    public $ecrId;

    public function __construct(DeviceController $controller)
    {
        $this->controller = $controller;
        $this->requestIdProvider = $controller->requestIdProvider;
    }
    public function cancel($cancelParams = null)
    {
        throw new UnsupportedTransactionException(
            "This function is not supported by the currently configured device."
        );
    }

    public function closeLane() : DeviceResponse
    {
        throw new UnsupportedTransactionException("This function is not supported by the currently configured device.");
    }

    public function disableHostResponseBeep() : DeviceResponse
    {
        throw new UnsupportedTransactionException("This function is not supported by the currently configured device.");
    }

    public function getSignatureFile()
    {
        throw new UnsupportedTransactionException("This function is not supported by the currently configured device.");
    }

    public function initialize()
    {
        throw new UnsupportedTransactionException("This function is not supported by the currently configured device.");
    }

    public function lineItem(
        string $leftText,
        string $rightText = null,
        string $runningLeftText = null,
        string $runningRightText = null
    ) : DeviceResponse
    {
        throw new UnsupportedTransactionException("This function is not supported by the currently configured device.");
    }

    public function openLane() : DeviceResponse
    {
        throw new UnsupportedTransactionException("This function is not supported by the currently configured device.");
    }

    public function promptForSignature(string $transactionId = null)
    {
        throw new UnsupportedTransactionException("This function is not supported by the currently configured device.");
    }

    public function reboot() : DeviceResponse
    {
        throw new UnsupportedTransactionException("This function is not supported by the currently configured device.");
    }

    public function startCard(PaymentMethodType $paymentMethodType) : DeviceResponse
    {
        throw new UnsupportedTransactionException("This function is not supported by the currently configured device.");
    }
    public function sendSaf($safIndicator = null) : DeviceResponse
    {
            throw new NotImplementedException();
    }

    public function reset(): DeviceResponse
    {
        throw new NotImplementedException();
    }

    /********* END ADMIN METHODS ********/

    /**********START Batching ************/

    public function endOfDay()
    {
        throw new UnsupportedTransactionException("This function is not supported by the currently configured device.");
    }

    public function batchClose(): IBatchCloseResponse
    {
        throw new UnsupportedTransactionException("This function is not supported by the currently configured device.");
    }

    /********* END Batching ********/

    /**********START Transactions ************/

    /**
     * @return TerminalAuthBuilder
     */
    public function addValue($amount = null) : TerminalAuthBuilder
    {
        return (new TerminalAuthBuilder(TransactionType::ADD_VALUE, PaymentMethodType::GIFT))
                ->withAmount($amount)
                ->withCurrency(CurrencyType::CURRENCY);
    }

    public function authorize($amount = null) : TerminalAuthBuilder
    {
        return (new TerminalAuthBuilder(TransactionType::AUTH, PaymentMethodType::CREDIT))
            ->withAmount($amount);
    }

    public function balance() : TerminalAuthBuilder
    {
        return (new TerminalAuthBuilder(TransactionType::BALANCE, PaymentMethodType::GIFT))
            ->withCurrency(CurrencyType::CURRENCY);
    }

    public function capture($amount = null) : TerminalManageBuilder
    {
        return (new TerminalManageBuilder(TransactionType::CAPTURE, PaymentMethodType::CREDIT))
            ->withAmount($amount);
    }

    public function refund($amount = null) : TerminalAuthBuilder
    {
        return (new TerminalAuthBuilder(TransactionType::REFUND, PaymentMethodType::CREDIT))
                ->withAmount($amount);
    }

    public function sale($amount = null) : TerminalAuthBuilder
    {
        return (new TerminalAuthBuilder(TransactionType::SALE, PaymentMethodType::CREDIT))
            ->withAmount($amount);
    }

    public function verify() : TerminalAuthBuilder
    {
        return (new TerminalAuthBuilder(TransactionType::VERIFY, PaymentMethodType::CREDIT));
    }

    public function void() : TerminalManageBuilder
    {
        return (new TerminalManageBuilder(TransactionType::VOID, PaymentMethodType::CREDIT));
    }

    public function withdrawal($amount = null) : TerminalAuthBuilder
    {
        return (new TerminalAuthBuilder(TransactionType::BENEFIT_WITHDRAWAL, PaymentMethodType::EBT))
                ->withAmount($amount);
    }

    public function tipAdjust($amount = null): TerminalManageBuilder
    {
        throw new UnsupportedTransactionException(
            "This method is not supported by the currently configured device."
        );
    }

    public function tokenize(): TerminalAuthBuilder
    {
        throw new UnsupportedTransactionException(
            "This method is not supported by the currently configured device."
        );
    }

    public function deletePreAuth() : TerminalManageBuilder
    {
        throw new UnsupportedTransactionException(
            "This method is not supported by the currently configured device."
        );
    }

    public function increasePreAuth($amount) : TerminalManageBuilder
    {
        throw new UnsupportedTransactionException(
            "This method is not supported by the currently configured device."
        );
    }

    public function refundById(string $transactionId): TerminalManageBuilder
    {
        throw new UnsupportedTransactionException(
            "This method is not supported by the currently configured device."
        );
    }

    /****************************************END*************************************/

    public function safDelete($safIndicator)
    {
        // TODO: Implement safDelete() method.
    }

    public function startDownload($deviceSettings)
    {
        // TODO: Implement startDownload() method.
    }

    public function setSafMode($paramValue)
    {
        // TODO: Implement setSafMode() method.
    }

    public function safSummaryReport($safIndicator = null)
    {
        // TODO: Implement safSummaryReport() method.
    }

    public function sendFile($sendFileData)
    {
        // TODO: Implement sendFile() method.
    }

    public function getDiagnosticReport($totalFields)
    {
        // TODO: Implement getDiagnosticReport() method.
    }

    public function getLastResponse()
    {
        // TODO: Implement getLastResponse() method.
    }
}