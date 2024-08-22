<?php

namespace GlobalPayments\Api\Terminals\Diamond;

use GlobalPayments\Api\Entities\Enums\PaymentMethodType;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException;
use GlobalPayments\Api\Terminals\Abstractions\IBatchCloseResponse;
use GlobalPayments\Api\Terminals\Builders\TerminalAuthBuilder;
use GlobalPayments\Api\Terminals\Builders\TerminalManageBuilder;
use GlobalPayments\Api\Terminals\Builders\TerminalReportBuilder;
use GlobalPayments\Api\Terminals\DeviceInterface;
use GlobalPayments\Api\Terminals\Enums\TerminalReportType;

class DiamondInterface extends DeviceInterface
{
    public function __construct(DiamondController $deviceController)
    {
        parent::__construct($deviceController);
    }

    public function tipAdjust($tipAmount = null) : TerminalManageBuilder
    {
        return (new TerminalManageBuilder(TransactionType::EDIT, PaymentMethodType::CREDIT))
            ->withGratuity($tipAmount);
    }

    public function localDetailReport() : TerminalReportBuilder
    {
        return new TerminalReportBuilder(TerminalReportType::LOCAL_DETAIL_REPORT);
    }

    public function deletePreAuth() : TerminalManageBuilder
    {
        return (new TerminalManageBuilder(TransactionType::DELETE, PaymentMethodType::CREDIT))
            ->withModifier(TransactionModifier::DELETE_PRE_AUTH);
    }

    public function increasePreAuth($amount) : TerminalManageBuilder
    {
        return (new TerminalManageBuilder(TransactionType::AUTH, PaymentMethodType::CREDIT))
            ->withModifier(TransactionModifier::INCREMENTAL)
            ->withAmount($amount);
    }

    public function batchClose() : IBatchCloseResponse
    {
        return (new TerminalAuthBuilder(TransactionType::BATCH_CLOSE))
            ->execute();
    }

    public function refundById($amount = null): TerminalManageBuilder
    {
        return (new TerminalManageBuilder(TransactionType::REFUND, PaymentMethodType::CREDIT))
            ->withAmount($amount);
    }
}

