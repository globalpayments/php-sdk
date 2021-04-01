<?php

use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Mapping\GpApiMapping;
use \GlobalPayments\Api\Utils\StringUtils;

class MappingTest extends TestCase
{
     public function testMapResponseTest()
     {
         $rawJson = "{\"id\":\"TRN_BHZ1whvNJnMvB6dPwf3znwWTsPjCn0\",\"time_created\":\"2020-12-04T12:46:05.235Z\",\"type\":\"SALE\",\"status\":\"PREAUTHORIZED\",\"channel\":\"CNP\",\"capture_mode\":\"LATER\",\"amount\":\"1400\",\"currency\":\"USD\",\"country\":\"US\",\"merchant_id\":\"MER_c4c0df11039c48a9b63701adeaa296c3\",\"merchant_name\":\"Sandbox_merchant_2\",\"account_id\":\"TRA_6716058969854a48b33347043ff8225f\",\"account_name\":\"Transaction_Processing\",\"reference\":\"15fbcdd9-8626-4e29-aae8-050f823f995f\",\"payment_method\":{\"result\":\"00\",\"message\":\"[ test system ] AUTHORISED\",\"entry_mode\":\"ECOM\",\"card\":{\"brand\":\"VISA\",\"masked_number_last4\":\"XXXXXXXXXXXX5262\",\"authcode\":\"12345\",\"brand_reference\":\"PSkAnccWLNMTcRmm\",\"brand_time_created\":\"\",\"cvv_result\":\"MATCHED\"}},\"batch_id\":\"\",\"action\":{\"id\":\"ACT_BHZ1whvNJnMvB6dPwf3znwWTsPjCn0\",\"type\":\"PREAUTHORIZE\",\"time_created\":\"2020-12-04T12:46:05.235Z\",\"result_code\":\"SUCCESS\",\"app_id\":\"Uyq6PzRbkorv2D4RQGlldEtunEeGNZll\",\"app_name\":\"sample_app_CERT\"}}";
         $doc = json_decode($rawJson);
         $transaction = GpApiMapping::mapResponse($doc);

         $this->assertEquals($doc->id, $transaction->transactionId);
         $this->assertEquals(StringUtils::toAmount($doc->amount), $transaction->balanceAmount);
         $this->assertEquals($doc->time_created, $transaction->timestamp);
         $this->assertEquals($doc->status, $transaction->responseMessage);
         $this->assertEquals($doc->reference, $transaction->referenceNumber);
         $this->assertEquals($doc->action->result_code, $transaction->responseCode);

         if (!empty($transaction->batchSummary)) {
             $this->assertEquals($doc->batch_id, $transaction->batchSummary->sequenceNumber);
         }

         if (!empty($doc->payment_method)) {
             $paymentMethod = $doc->payment_method;
             $this->assertEquals($paymentMethod->result, $transaction->authorizationCode);
             $this->assertEquals($paymentMethod->card->brand, $transaction->cardType);
             $this->assertEquals($paymentMethod->card->masked_number_last4, $transaction->cardLast4);
         }

        if (isset($doc->card)) {
            $card = $doc->card;
            $this->assertEquals($card->number, $transaction->cardNumber);
            $this->assertEquals($card->brand, $transaction->cardType);
            $this->assertEquals($card->expiry_month, $transaction->cardExpMonth);
            $this->assertEquals($card->expiry_year, $transaction->cardExpYear);
        }
     }

     public function testMapTransactionSummaryTest()
     {
         $rawJson = "{\"id\":\"TRN_TvY1QFXxQKtaFSjNaLnDVdo3PZ7ivz\",\"time_created\":\"2020-06-05T03:08:20.896Z\",\"time_last_updated\":\"\",\"status\":\"PREAUTHORIZED\",\"type\":\"SALE\",\"merchant_id\":\"MER_c4c0df11039c48a9b63701adeaa296c3\",\"merchant_name\":\"Sandbox_merchant_2\",\"account_id\":\"TRA_6716058969854a48b33347043ff8225f\",\"account_name\":\"Transaction_Processing\",\"channel\":\"CNP\",\"amount\":\"10000\",\"currency\":\"CAD\",\"reference\":\"My-TRANS-184398775\",\"description\":\"41e7877b-da90-4c5f-befe-7f024b96311e\",\"order_reference\":\"\",\"time_created_reference\":\"\",\"batch_id\":\"\",\"initiator\":\"\",\"country\":\"\",\"language\":\"\",\"ip_address\":\"97.107.232.5\",\"site_reference\":\"\",\"payment_method\":{\"result\":\"00\",\"message\":\"SUCCESS\",\"entry_mode\":\"ECOM\",\"name\":\"NAME NOT PROVIDED\",\"card\":{\"funding\":\"CREDIT\",\"brand\":\"VISA\",\"authcode\":\"12345\",\"brand_reference\":\"TQ76bJf7qzkC30U0\",\"masked_number_first6last4\":\"411111XXXXXX1111\",\"cvv_indicator\":\"PRESENT\",\"cvv_result\":\"MATCHED\",\"avs_address_result\":\"MATCHED\",\"avs_postal_code_result\":\"MATCHED\"}},\"action_create_id\":\"ACT_TvY1QFXxQKtaFSjNaLnDVdo3PZ7ivz\",\"parent_resource_id\":\"TRN_TvY1QFXxQKtaFSjNaLnDVdo3PZ7ivz\",\"action\":{\"id\":\"ACT_kLkU0qND7wyuW0Br76ZNyAnlPTjHsb\",\"type\":\"TRANSACTION_SINGLE\",\"time_created\":\"2020-11-24T15:43:43.990Z\",\"result_code\":\"SUCCESS\",\"app_id\":\"JF2GQpeCrOivkBGsTRiqkpkdKp67Gxi0\",\"app_name\":\"test_app\"}}";

         $doc = json_decode($rawJson);

         $transactionSummary = GpApiMapping::mapTransactionSummary($doc);

        $this->assertEquals($doc->id, $transactionSummary->transactionId);
        $this->assertEquals(new \DateTime($doc->time_created), $transactionSummary->transactionDate);
        $this->assertEquals($doc->status, $transactionSummary->transactionStatus);
        $this->assertEquals($doc->type, $transactionSummary->transactionType);
        $this->assertEquals($doc->channel, $transactionSummary->channel);
        $this->assertEquals(StringUtils::toAmount($doc->amount), $transactionSummary->amount);
        $this->assertEquals($doc->currency, $transactionSummary->currency);
        $this->assertEquals($doc->reference, $transactionSummary->referenceNumber);
        $this->assertEquals($doc->reference, $transactionSummary->clientTransactionId);
        $this->assertEquals($doc->batch_id, $transactionSummary->batchSequenceNumber);
        $this->assertEquals($doc->country, $transactionSummary->country);
        $this->assertEquals($doc->parent_resource_id, $transactionSummary->originalTransactionId);

        if (isset($doc->payment_method)) {
            $paymentMethod = $doc->payment_method;
            $this->assertEquals($paymentMethod->message, $transactionSummary->gatewayResponseMessage);
            $this->assertEquals($paymentMethod->entry_mode, $transactionSummary->entryMode);
            $this->assertEquals($paymentMethod->name, $transactionSummary->cardHolderName);

            if (isset($paymentMethod->card)) {
                $card = $paymentMethod->card;
                $this->assertEquals($card->brand, $transactionSummary->cardType);
                $this->assertEquals($card->authcode, $transactionSummary->authCode);
                $this->assertEquals($card->brand_reference, $transactionSummary->brandReference);
                if (!empty($card->arn)) {
                    $this->assertEquals($card->arn, $transactionSummary->aquirerReferenceNumber);
                }
                $this->assertEquals($card->masked_number_first6last4, $transactionSummary->maskedCardNumber);
            }
        }
     }

     public function testMapTransactionSummaryTest_FromObject()
     {
         $obj = new stdClass();
         $obj->id = "TRN_TvY1QFXxQKtaFSjNaLnDVdo3PZ7ivz";
         $obj->time_created = (new \DateTime())->format('Y-m-d H:i:s');
         $obj->status = "PREAUTHORIZED";
         $obj->type = "SALE";
         $obj->channel = "CNP";
         $obj->amount = "10000";
         $obj->currency = "USD";
         $obj->reference = "My-TRANS-184398775";
         $obj->batch_id = "BATCH_123456";
         $obj->country = "US";
         $obj->parent_resource_id = "PARENT_456123";
         $obj->payment_method = new stdClass();
         $obj->payment_method->message = "SUCCESS";
         $obj->payment_method->entry_mode = "ECOM";
         $obj->payment_method->name = "James Mason";
         $obj->payment_method->card = new stdClass();
         $obj->payment_method->card->brand = "VISA";
         $obj->payment_method->card->authcode = "12345";
         $obj->payment_method->card->brand_reference = "TQ76bJf7qzkC30U0";
         $obj->payment_method->card->arn = "ARN_123456798";
         $obj->payment_method->card->masked_number_first6last4 = "411111XXXXXX1111";

         $rawJson = json_encode($obj);

         $transactionSummary = GpApiMapping::mapTransactionSummary(json_decode($rawJson));

         $this->assertEquals($obj->id, $transactionSummary->transactionId);
         $this->assertEquals(new \DateTime($obj->time_created), $transactionSummary->transactionDate);
         $this->assertEquals($obj->status, $transactionSummary->transactionStatus);
         $this->assertEquals($obj->type, $transactionSummary->transactionType);
         $this->assertEquals($obj->channel, $transactionSummary->channel);
         $this->assertEquals(StringUtils::toAmount($obj->amount), $transactionSummary->amount);
         $this->assertEquals($obj->currency, $transactionSummary->currency);
         $this->assertEquals($obj->reference, $transactionSummary->referenceNumber);
         $this->assertEquals($obj->reference, $transactionSummary->clientTransactionId);
         $this->assertEquals($obj->batch_id, $transactionSummary->batchSequenceNumber);
         $this->assertEquals($obj->country, $transactionSummary->country);
         $this->assertEquals($obj->parent_resource_id, $transactionSummary->originalTransactionId);

         if (isset($obj->payment_method)) {
             $paymentMethod = $obj->payment_method;
             $this->assertEquals($paymentMethod->message, $transactionSummary->gatewayResponseMessage);
             $this->assertEquals($paymentMethod->entry_mode, $transactionSummary->entryMode);
             $this->assertEquals($paymentMethod->name, $transactionSummary->cardHolderName);

             if (isset($paymentMethod->card)) {
                 $card = $paymentMethod->card;
                 $this->assertEquals($card->brand, $transactionSummary->cardType);
                 $this->assertEquals($card->authcode, $transactionSummary->authCode);
                 $this->assertEquals($card->brand_reference, $transactionSummary->brandReference);
                 $this->assertEquals($card->arn, $transactionSummary->aquirerReferenceNumber);
                 $this->assertEquals($card->masked_number_first6last4, $transactionSummary->maskedCardNumber);
             }
         }
     }

    public function testMapDepositSummaryTest()
    {
        $rawJson = "{\"id\":\"DEP_2342423423\",\"time_created\":\"2020-11-21\",\"status\":\"FUNDED\",\"funding_type\":\"CREDIT\",\"amount\":\"11400\",\"currency\":\"USD\",\"aggregation_model\":\"H-By Date\",\"bank_transfer\":{\"masked_account_number_last4\":\"XXXXXX9999\",\"bank\":{\"code\":\"XXXXX0001\"}},\"system\":{\"mid\":\"101023947262\",\"hierarchy\":\"055-70-024-011-019\",\"name\":\"XYZ LTD.\",\"dba\":\"XYZ Group\"},\"sales\":{\"count\":4,\"amount\":\"12400\"},\"refunds\":{\"count\":1,\"amount\":\"-1000\"},\"discounts\":{\"count\":0,\"amount\":\"\"},\"tax\":{\"count\":0,\"amount\":\"\"},\"disputes\":{\"chargebacks\":{\"count\":0,\"amount\":\"\"},\"reversals\":{\"count\":0,\"amount\":\"\"}},\"fees\":{\"amount\":\"\"},\"action\":{\"id\":\"ACT_TWdmMMOBZ91iQX1DcvxYermuVJ6E6h\",\"type\":\"DEPOSIT_SINGLE\",\"time_created\":\"2020-11-24T18:43:43.370Z\",\"result_code\":\"SUCCESS\",\"app_id\":\"JF2GQpeCrOivkBGsTRiqkpkdKp67Gxi0\",\"app_name\":\"test_app\"}}";
        $doc = json_decode($rawJson);

        $depositSummary = GpApiMapping::mapDepositSummary($doc);

        $this->assertEquals($doc->id, $depositSummary->depositId);
        $this->assertEquals(new \DateTime($doc->time_created), $depositSummary->depositDate);
        $this->assertEquals($doc->status, $depositSummary->status);
        $this->assertEquals($doc->funding_type, $depositSummary->type);
        $this->assertEquals(StringUtils::toAmount($doc->amount), $depositSummary->amount);
        $this->assertEquals($doc->currency, $depositSummary->currency);

        if (isset($doc->system)) {
            $system = $doc->system;
            $this->assertEquals($system->mid, $depositSummary->merchantNumber);
            $this->assertEquals($system->hierarchy, $depositSummary->merchantHierarchy);
            $this->assertEquals($system->name, $depositSummary->merchantName);
            $this->assertEquals($system->dba, $depositSummary->merchantDbaName);
        }

        if (isset($doc->sales)) {
            $this->assertEquals($doc->sales->count, $depositSummary->salesTotalCount);
            $this->assertEquals(StringUtils::toAmount($doc->sales->amount), $depositSummary->salesTotalAmount);
        }

        if (isset($doc->refunds)) {
            $this->assertEquals($doc->refunds->count, $depositSummary->refundsTotalCount);
            $this->assertEquals(StringUtils::toAmount($doc->refunds->amount), $depositSummary->refundsTotalAmount);
        }

        if (isset($doc->disputes)) {
            $disputes = $doc->disputes;
            $this->assertEquals($disputes->chargebacks->count, $depositSummary->chargebackTotalCount);
            $this->assertEquals(StringUtils::toAmount($disputes->chargebacks->amount), $depositSummary->chargebackTotalAmount);

            $this->assertEquals($disputes->reversals->count, $depositSummary->adjustmentTotalCount);
            $this->assertEquals(StringUtils::toAmount($disputes->reversals->amount), $depositSummary->adjustmentTotalAmount);
        }

        $this->assertEquals(StringUtils::toAmount($doc->fees->amount), $depositSummary->feesTotalAmount);
    }

    public function testMapDisputeSummaryTest()
    {
        $rawJson = "{\"id\":\"DIS_SAND_abcd1234\",\"time_created\":\"2020-11-12T18:50:39.721Z\",\"merchant_id\":\"MER_62251730c5574bbcb268191b5f315de8\",\"merchant_name\":\"TEST MERCHANT\",\"account_id\":\"DIA_882c832d13e04185bb6e213d6303ed98\",\"account_name\":\"testdispute\",\"status\":\"WITH_MERCHANT\",\"status_time_created\":\"2020-11-14T18:50:39.721Z\",\"stage\":\"RETRIEVAL\",\"stage_time_created\":\"2020-11-17T18:50:39.722Z\",\"amount\":\"1000\",\"currency\":\"USD\",\"payer_amount\":\"1000\",\"payer_currency\":\"USD\",\"merchant_amount\":\"1000\",\"merchant_currency\":\"USD\",\"reason_code\":\"104\",\"reason_description\":\"Other Fraud-Card Absent Environment\",\"time_to_respond_by\":\"2020-11-29T18:50:39.722Z\",\"result\":\"PENDING\",\"investigator_comment\":\"WITH_MERCHANT RETRIEVAL PENDING 1000 USD 1000 USD\",\"system\":{\"mid\":\"627384967\",\"hierarchy\":\"111-23-099-001-001\",\"name\":\"ABC INC.\"},\"last_adjustment_amount\":\"\",\"last_adjustment_currency\":\"\",\"last_adjustment_funding\":\"\",\"last_adjustment_time_created\":\"2020-11-20T18:50:39.722Z\",\"net_financial_amount\":\"\",\"net_financial_currency\":\"\",\"net_financial_funding\":\"\",\"payment_method_provider\":[{\"comment\":\"issuer comments 34523\",\"reference\":\"issuer-reference-0001\",\"documents\":[{\"id\":\"DOC_MyEvidence_234234AVCDE-1\"}]}],\"transaction\":{\"time_created\":\"2020-10-05T18:50:39.726Z\",\"type\":\"SALE\",\"amount\":\"1000\",\"currency\":\"USD\",\"reference\":\"my-trans-AAA1\",\"remarks\":\"my-trans-AAA1\",\"payment_method\":{\"card\":{\"number\":\"424242xxxxxx4242\",\"arn\":\"834523482349123\",\"brand\":\"VISA\",\"authcode\":\"234AB\",\"brand_reference\":\"23423421342323A\"}}},\"documents\":[],\"action\":{\"id\":\"ACT_5blBTHnIs4aOCIvGwG7KizYUpsGI0g\",\"type\":\"DISPUTE_SINGLE\",\"time_created\":\"2020-11-24T18:50:39.925Z\",\"result_code\":\"SUCCESS\",\"app_id\":\"JF2GQpeCrOivkBGsTRiqkpkdKp67Gxi0\",\"app_name\":\"test_app\"}}";
        $doc = json_decode($rawJson);

        $disputeSummary = GpApiMapping::mapDisputeSummary($doc);

        $this->assertEquals($doc->id, $disputeSummary->caseId);
        $this->assertEquals(new \DateTime($doc->time_created), $disputeSummary->caseIdTime);
        $this->assertEquals($doc->status, $disputeSummary->caseStatus);
        $this->assertEquals($doc->stage, $disputeSummary->caseStage);
        $this->assertEquals(StringUtils::toAmount($doc->amount), $disputeSummary->caseAmount);
        $this->assertEquals($doc->currency, $disputeSummary->caseCurrency);

        if (isset($doc->system)) {
            $system = $doc->system;
            $this->assertEquals($system->mid, $disputeSummary->caseMerchantId);
            $this->assertEquals($system->hierarchy, $disputeSummary->merchantHierarchy);
        }

        if (isset($doc->payment_method->card)) {
            $card = $doc->payment_method;
            $this->assertEquals($card->number, $disputeSummary->transactionMaskedCardNumber);
            $this->assertEquals($card->arn, $disputeSummary->transactionARN);
            $this->assertEquals($card->brand, $disputeSummary->transactionCardType);
        }

        $this->assertEquals($doc->reason_code, $disputeSummary->reasonCode);
        $this->assertEquals($doc->reason_description, $disputeSummary->reason);
        $this->assertEquals(new \DateTime($doc->time_to_respond_by), $disputeSummary->respondByDate);
        $this->assertEquals($doc->result, $disputeSummary->result);
        $this->assertEquals(StringUtils::toAmount($doc->last_adjustment_amount), $disputeSummary->lastAdjustmentAmount);
        $this->assertEquals($doc->last_adjustment_currency, $disputeSummary->lastAdjustmentCurrency);
        $this->assertEquals($doc->last_adjustment_funding, $disputeSummary->lastAdjustmentFunding);
    }
}