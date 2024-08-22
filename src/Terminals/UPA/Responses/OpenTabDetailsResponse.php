<?php

namespace GlobalPayments\Api\Terminals\UPA\Responses;

class OpenTabDetailsResponse extends UpaResponseHandler
{
    public ?string $merchantName;
    /** @var array<OpenTab>  */
    public array $openTabs = [] ;

    public function __construct($jsonResponse)
    {
        $this->parseJsonResponse($jsonResponse);
    }

    protected function parseJsonResponse($jsonResponse): void
    {
        parent::parseJsonResponse($jsonResponse);
        $secondNodeData = $this->isGpApiResponse($jsonResponse) ? $jsonResponse->response->data : $jsonResponse->data->data;
        $this->merchantName = $secondNodeData->merchantName ?? null;
        $this->multipleMessage = $secondNodeData->multipleMessage ?? null;
        if (isset($secondNodeData->openTabDetails)) {
            foreach ($secondNodeData->openTabDetails as $openTabDetail) {
                $tab = new OpenTab();
                $tab->authorizedAmount = $openTabDetail->authorizedAmount ?? null;
                $tab->cardType = $openTabDetail->cardType ?? null;
                $tab->transactionId = $openTabDetail->referenceNumber ?? null;
                $tab->maskedPan = $openTabDetail->maskedPan ?? null;
                $tab->clerkId = $openTabDetail->clerkId ?? null;
                $this->openTabs[] = $tab;
            }
        }
    }
}