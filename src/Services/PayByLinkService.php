<?php

namespace GlobalPayments\Api\Services;

use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Builders\TransactionReportBuilder;
use GlobalPayments\Api\Entities\Enums\ReportType;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\PayByLinkData;

class PayByLinkService
{
    public static function create(PayByLinkData $payByLink, $amount)
    {
        return (new AuthorizationBuilder( TransactionType::CREATE))
            ->withAmount($amount)
            ->withPayByLinkData($payByLink);
    }

    public static function edit($payByLinkId)
    {
        return (new ManagementBuilder( TransactionType::PAYBYLINK_UPDATE))
            ->withPaymentLinkId($payByLinkId);
    }


    public static function payByLinkDetail($payByLinkId)
    {
        return (new TransactionReportBuilder(ReportType::PAYBYLINK_DETAIL))
            ->withPayByLinkId($payByLinkId);
    }

    public static function findPayByLink($page, $pageSize)
    {
        return (new TransactionReportBuilder(ReportType::FIND_PAYBYLINK_PAGED))
            ->withPaging($page, $pageSize);
    }
}