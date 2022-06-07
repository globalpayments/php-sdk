<?php

namespace GlobalPayments\Api\Builders\RequestBuilder\GpEcom;

use DOMDocument;
use GlobalPayments\Api\Builders\BaseBuilder;
use GlobalPayments\Api\Builders\TransactionReportBuilder;
use GlobalPayments\Api\Entities\Enums\ReportType;
use GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException;
use GlobalPayments\Api\Entities\Request;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpEcomConfig;
use GlobalPayments\Api\Utils\GenerationUtils;

class GpEcomReportRequestBuilder
{
    public static function canProcess($builder)
    {
        if ($builder instanceof TransactionReportBuilder) {
            return true;
        }

        return false;
    }

    /**
     * @param BaseBuilder $builder
     * @param GpEcomConfig $config
     *
     * @return Request
     */
    public function buildRequest(BaseBuilder $builder, GpEcomConfig $config)
    {
        $xml = new DOMDocument();
        $timestamp = GenerationUtils::generateTimestamp();

        $request = $xml->createElement("request");
        $request->setAttribute("timestamp", $timestamp);
        $request->setAttribute("type", $this->mapReportRequestType($builder->reportType));
        $request->appendChild($xml->createElement("merchantid", $config->merchantId));

        if ($config->accountId !== null) {
            $request->appendChild($xml->createElement("account", $config->accountId));
        }
        $request->appendChild($xml->createElement("orderid", $builder->transactionId));
        $hash = GenerationUtils::generateHash(
            $config->sharedSecret,
            implode('.', [
                $timestamp,
                $config->merchantId,
                $builder->transactionId,
                '',
                '',
                ''
            ])
        );
        $request->appendChild($xml->createElement("sha1hash", $hash));

        return new Request('', 'POST', $xml->saveXML($request));
    }

    /**
     * @param ReportType $reportType
     */
    private function mapReportRequestType($reportType)
    {
        switch ($reportType) {
            case ReportType::TRANSACTION_DETAIL:
                return 'query';
            default:
                throw new UnsupportedTransactionException("This reporting call is not supported by your currently configured gateway.");
        }
    }
}