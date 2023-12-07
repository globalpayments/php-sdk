<?php

namespace GlobalPayments\Api\Builders\RequestBuilder\GpApi;

use GlobalPayments\Api\Builders\BaseBuilder;
use GlobalPayments\Api\Builders\FileProcessingBuilder;
use GlobalPayments\Api\Entities\Enums\FileProcessingActionType;
use GlobalPayments\Api\Entities\GpApi\GpApiRequest;
use GlobalPayments\Api\Entities\IRequestBuilder;
use GlobalPayments\Api\ServiceConfigs\Gateways\GpApiConfig;

class GpApiFileProcessingRequestBuilder implements IRequestBuilder
{
    public static function canProcess($builder = null)
    {
        if ($builder instanceof FileProcessingBuilder) {
            return true;
        }

        return false;
    }

    /**
     * @param BaseBuilder $builder
     * @param GpApiConfig $config
     *
     * @return GpApiRequest|null
     */
    public function buildRequest(BaseBuilder $builder, $config)
    {
        $requestData = null;
        /** @var FileProcessingBuilder $builder */
        switch ($builder->actionType) {
            case FileProcessingActionType::CREATE_UPLOAD_URL:
                $endpoint = GpApiRequest::FILE_PROCESSING;
                $verb = 'POST';
                $requestData = [
                    'merchant_id' => $config->merchantId,
                    'account_id' => $config->accessTokenInfo->fileProcessingAccountID,
                    'notifications' => [
                        'status_url' => $config->statusUrl ?? null
                    ]
                ];
                break;
            case FileProcessingActionType::GET_DETAILS:
                $endpoint = GpApiRequest::FILE_PROCESSING . '/' . $builder->resourceId;
                $verb = 'GET';
                break;
            default:
                return null;
        }

        return new GpApiRequest($endpoint, $verb, $requestData);
    }

    public function buildRequestFromJson($jsonRequest, $config)
    {
        // TODO: Implement buildRequestFromJson() method.
    }
}