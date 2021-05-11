<?php

namespace GlobalPayments\Api\Entities\Reporting;

use GlobalPayments\Api\Entities\Enum;

class SearchCriteria extends Enum
{
    const ACCOUNT_NAME = 'accountName';
    const ACCOUNT_NUMBER_LAST_FOUR = 'accountNumberLastFour';
    const ALT_PAYMENT_STATUS = 'altPaymentStatus';
    const AQUIRER_REFERENCE_NUMBER = 'aquirerReferenceNumber';
    const AUTH_CODE = 'authCode';
    const BANK_ROUTING_NUMBER = 'bankRoutingNumber';
    const BATCH_ID = 'batchId';
    const BATCH_SEQUENCE_NUMBER = 'batchSequenceNumber';
    const BRAND_REFERENCE = 'brandReference';
    const BUYER_EMAIL_ADDRESS = 'buyerEmailAddress';
    const CARD_BRAND = 'cardBrand';
    const CARD_HOLDER_FIRST_NAME = 'cardHolderFirstName';
    const CARD_HOLDER_LAST_NAME = 'cardHolderLastName';
    const CARD_HOLDER_PO_NUMBER = 'cardHolderPoNumber';
    const CARD_NUMBER_FIRST_SIX = 'cardNumberFirstSix';
    const CARD_NUMBER_LAST_FOUR = 'cardNumberLastFour';
    const CHANNEL = 'channel';
    const CHECK_FIRST_NAME = 'checkFirstName';
    const CHECK_LAST_NAME = 'checkLastName';
    const CHECK_NAME = 'checkName';
    const CHECK_NUMBER = 'checkNumber';
    const CLERK_ID = 'clerkId';
    const CLIENT_TRANSACTION_ID = 'clientTransactionId';
    const CUSTOMER_ID = 'customerId';
    const DEPOSIT_STATUS = 'depositStatus';
    const DISPLAY_NAME = 'displayName';
    const END_DATE = 'endDate';
    const FULLY_CAPTURED = 'fullyCaptured';
    const GIFT_CURRENCY = 'giftCurrency';
    const GIFT_MASKED_ALIAS = 'giftMaskedAlias';
    const INVOICE_NUMBER = 'invoiceNumber';
    const ISSUER_RESULT = 'issuerResult';
    const ISSUER_TRANSACTION_ID = 'issuerTransactionId';
    const ONE_TIME = 'oneTime';
    const PAYMENT_ENTRY_MODE = 'paymentEntryMode';
    const PAYMENT_METHOD_KEY = 'paymentMethodKey';
    const PAYMENT_TYPE = 'paymentType';
    const REFERENCE_NUMBER = 'referenceNumber';
    const SETTLEMENT_AMOUNT = 'settlementAmount';
    const SCHEDULE_ID = 'scheduleId';
    const SITE_TRACE = 'siteTrace';
    const START_DATE = 'startDate';
    const TOKEN_FIRST_SIX = 'tokenFirstSix';
    const TOKEN_LAST_FOUR = 'tokenLastFour';
    const TRANSACTION_STATUS = 'transactionStatus';
    const DISPUTE_STAGE = 'disputeStage';
    const DISPUTE_STATUS = 'disputeStatus';
    const UNIQUE_DEVICE_ID = 'uniqueDeviceId';
    const USER_NAME = 'username';
    const CARDHOLDER_NAME = 'name';
    const DEPOSIT_ID = 'depositId';
    const FROM_TIME_LAST_UPDATED = 'fromTimeLastUpdated';
    const TO_TIME_LAST_UPDATED = 'toTimeLastUpdated';
    const STORED_PAYMENT_METHOD_ID = 'storedPaymentMethodId';
    const STORED_PAYMENT_METHOD_STATUS = 'storedPaymentMethodStatus';
    const ACTION_TYPE = 'actionType';
    const ACTION_ID = 'actionId';
    const RESOURCE = 'resource';
    const RESOURCE_STATUS = 'resourceStatus';
    const RESOURCE_ID = 'resourceId';
    const MERCHANT_NAME = 'merchantName';
    const APP_NAME = 'appName';
    const VERSION = 'version';
    const RESPONSE_CODE = 'responseCode';
    const HTTP_RESPONSE_CODE = 'httpResponseCode';
}
