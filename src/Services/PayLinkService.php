<?php

namespace GlobalPayments\Api\Services;

use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Builders\TransactionReportBuilder;
use GlobalPayments\Api\Entities\Enums\ReportType;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\PayLinkData;

class PayLinkService
{
    public static function create(PayLinkData $payLink, $amount)
    {
        return (new AuthorizationBuilder( TransactionType::CREATE))
            ->withAmount($amount)
            ->withPayLinkData($payLink);
    }

    public static function edit($payLinkId)
    {
        return (new ManagementBuilder( TransactionType::PAYLINK_UPDATE))
            ->withPaymentLinkId($payLinkId);
    }


    public static function payLinkDetail($payLinkId)
    {
        return (new TransactionReportBuilder(ReportType::PAYLINK_DETAIL))
            ->withPayLinkId($payLinkId);
    }

    public static function findPayLink($page, $pageSize)
    {
        return (new TransactionReportBuilder(ReportType::FIND_PAYLINK_PAGED))
            ->withPaging($page, $pageSize);
    }
}