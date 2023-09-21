<?php

namespace GlobalPayments\Api\Entities\PayFac;

use GlobalPayments\Api\Entities\Enums\FileType;

class UploadDocumentData
{
    /**
     * Name the document according to instructions provided to you by ProPay's Risk team
     *
     * @var string
     */
    public $documentName;

    /**
     * The transaction number of the chargeback you need to dispute.Required for chargeback document
     *
     * @var string
     */
    public $transactionReference;
    
    /**
     * File location
     *
     * @var string
     */
    public $documentLocation;
    
    /**
     * The type of document you've been asked to provide by ProPay's Risk team. Valid values are:
     * Verification, FraudHolds, Underwriting, RetrievalRequest
     *
     * @var string
     */
    public $documentCategory;

    public string $b64_content;

    /** @var FileType  */
    public string $documentFormat;
}
