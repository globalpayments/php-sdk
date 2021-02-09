<?php


namespace GlobalPayments\Api\Entities\Reporting;


use GlobalPayments\Api\Entities\Enum;

class DataServiceCriteria extends Enum
{
    const ADJUSTMENT_FUNDING = 'adjustmentFunding';  // Data Services
    const AMOUNT = 'amount';  // Data Services
    const BANK_ACCOUNT_NUMBER = 'bankAccountNumber';  // Data Services
    const CASE_ID = 'caseId';  // Data Services
    const CARD_NUMBER_FIRST_SIX = 'cardNumberFirstSix';  // Data Services
    const CARD_NUMBER_LAST_FOUR = 'cardNumberLastFour';  // Data Services
    const CASE_NUMBER = 'caseNumber';  // Data Services
    const COUNTRY = 'country';  //Data Services
    const CURRENCY = 'currency';  // Data Services
    const DEPOSIT_REFERENCE = 'depositReference';  // Data Services
    const END_ADJUSTMENT_DATE = 'endAdjustmentDate';  // Data Services
    const END_DEPOSIT_DATE = 'endDepositDate';  // Data Services
    const END_STAGE_DATE = 'endStageDate';  // Data Services
    const HIERARCHY = 'hierarchy';  // Data Services
    const LOCAL_TRANSACTION_END_TIME = 'localTransactionEndTime';  // Data Services
    const LOCAL_TRANSACTION_START_TIME = 'localTransactionStartTime';  // Data Services
    const MERCHANT_ID = 'merchantId';  // Data Services
    const ORDER_ID = 'orderId';  // Data Services
    const START_ADJUSTMENT_DATE = 'startAdjustmentDate';  // Data Services
    const START_DEPOSIT_DATE = 'startDepositDate';  // Data Services
    const START_STAGE_DATE = 'startStageDate';  // Data Services
    const SYSTEM_HIERARCHY = 'systemHierarchy';  // Data Services
    const TIMEZONE = 'timezone'; // Data Services
    const START_BATCH_DATE = 'startBatchDate';
    const END_BATCH_DATE = 'endBatchDate';


}