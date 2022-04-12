<?php
namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals\UPA\VRF;

use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\DeviceType;
use GlobalPayments\Api\Services\DeviceService;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;
use GlobalPayments\Api\Entities\Enums\StoredCredentialInitiator;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\LogManagement;
use GlobalPayments\Api\Entities\AutoSubstantiation;

class UpaVerificationTests extends TestCase
{

    private $device;
    private $config;

    public function setup()
    {
        $this->device = DeviceService::create($this->getConfig());
    }
    
    public function tearDown()
    {
        sleep(3);
    }

    protected function getConfig()
    {
        $this->config = new ConnectionConfig();
        $this->config->ipAddress = '192.168.0.198';
        $this->config->port = '8081';
        $this->config->deviceType = DeviceType::UPA_VERIFONE_T650P;
        $this->config->connectionMode = ConnectionModes::TCP_IP;
        $this->config->timeout = 60;
        $this->config->requestIdProvider = new RequestIdProvider();
        $this->config->logManagementProvider = new LogManagement();
        $this->config->logManagementProvider->logLocation = 'upavrf.log';
        
        return $this->config;
    }

    private function printReceipt($response, $message = '')
    {
        print("\n\n Gateway Txn ID: " . $response->terminalRefNumber);

        $receipt = "x_trans_type=" . $response->transactionType;
        $receipt .= "&x_application_label=" . $response->applicationPreferredName;
        $receipt .= "&x_masked_card=" . $response->maskedCardNumber;
        $receipt .= "&x_application_id=" . $response->applicationId;
        $receipt .= "&x_cryptogram_type=" . $response->applicationCryptogramType;
        $receipt .= "&x_application_cryptogram=" . $response->applicationCryptogram;
        $receipt .= "&x_expiration_date= ";
        $receipt .= "&x_entry_method=" . $response->entryMethod;
        $receipt .= "&x_approval=" . $response->approvalCode;
        $receipt .= "&x_transaction_amount=" . $response->transactionAmount;
        $receipt .= "&x_amount_due=" . $response->balanceAmount;
        $receipt .= "&x_customer_verification_method=" . $response->customerVerificationMethod;
        $receipt .= "&x_response_text=" . $response->responseText;
        $receipt .= "&x_signature_status=" . $response->signatureStatus;
        
        print("\n Receipt: ".$receipt);
        
        $log = "\n $message Gateway Txn ID: " . $response->terminalRefNumber
                . "\n Receipt: ".$receipt
                . "\n =======================================================";
        
        $this->config->logManagementProvider->setLog($log);
    }

    /**
     * Objective    Process an EMV contact sale with offline PIN.
     * Test Card    EMV Mastercard
     * Procedure
     *      1. Select Sale function for an amount of $4.00.
     *          a. Insert Test Card and select application if prompted.
     *          b. Terminal will respond approved.
     * Pass Criteria
     *      1. Transaction must be approved. Receipt must conform to EMV Receipt Requirements
     *
     */
    
    public function test001EMVContactSale()
    {
        $response = $this->device->creditSale(4)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        
        //EMV
        $this->assertNotNull($response->applicationPreferredName);
        $this->assertNotNull($response->applicationLabel);
        $this->assertNotNull($response->applicationId);
        $this->assertNotNull($response->applicationCryptogramType);
        $this->assertNotNull($response->applicationCryptogram);
        $this->assertNotNull($response->customerVerificationMethod);
        $this->assertNotNull($response->terminalVerificationResults);
        
        $this->printReceipt($response, 'testCase01 creditSale');
    }
    
    /*
     * Objective    Ensure application can handle non-EMV swiped transactions.
     * Test Card    Magnetic stripe Mastercard
     * Procedure
     *      1. Select Sale function and swipe for the amount of $7.00.
     *          a. Insert Test Card and select application if prompted.
     *          b. Terminal will respond approved.
     * Pass Criteria
     *      1. Transaction must be approved. Receipt must conform to non-EMV Receipt Requirements
     *
    */
    
    public function test002MSRContactSale()
    {
        $response = $this->device->creditSale(7)
                ->execute();
                
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->printReceipt($response, 'test002MSRContactSale creditSale $7');
        
        /**
         * Objective    Process an online void.
         * Test Card    MSD only Mastercard
         * Procedure
         *      1. Select Void function to remove the previous Sale of $7.00.
         *          a. Retrieve the Portico Gateway Software TxnId from Credit Sale
         *             in Test Case 2.
         * Pass Criteria
         *      1. Transaction successfully returns a voided response
         *
         */
        // test004TransactionVoid
        $voidResponse = $this->device->creditVoid()
        ->withTerminalRefNumber($response->terminalRefNumber)
        ->execute();
        
        $this->assertNotNull($voidResponse);
        $this->assertEquals('00', $voidResponse->deviceResponseCode);
        $this->printReceipt($voidResponse, 'creditVoid');
    }

    /**
     * Objective    Process a keyed sale, with PAN & exp date, along this Address Verification and Card
     *              Security Code to confirm the application can support any or all of these.
     * Test Card    Magnetic stripe Mastercard
     * Procedure
     *      1. Select Sale function and manually key for the amount of $118.00.
     *          a. Enter PAN & expiration date.
     *          b. Enter 321 for Card Security Code (CVV2, CID), if supporting this feature. Enter 76321
     *             for AVS, if supporting this feature
     * Pass Criteria
     *      1. Transaction must be approved online. AVS Result Code: YCVV Result Code: M
     *
     */
    
    public function test003ManualSale()
    {
        $response = $this->device->creditSale(118)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->terminalRefNumber);
        $this->printReceipt($response, 'test003ManualSale creditSale');
    }
    
    /**
     * Objective    Ensure application can handle Partial Approval from our host.
     * Test Card    Magnetic stripe Mastercard
     * Procedure
     *      1. Select Sale function and swipe for the amount of $155.00.
     *          a. Insert Test Card and select application if prompted.
     *          b. Receive an approved amount less than requested. Finalize open ticket with remaining
     *             balance using a different card or tender.
     * Pass Criteria
     *      1. Transaction must be approved. Receipt must conform to non-EMV Receipt Requirements
     *
     */
    
    public function test005PartialApproval()
    {
        $response = $this->device->creditSale(155)
        ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('100', $response->transactionAmount);
        $this->assertEquals('10', $response->responseCode);
        $this->printReceipt($response, 'test005PartialApproval creditSale $155');
    }
    
    /**
     * Objective    Complete Sale request and then attempt a duplicate Sale transaction
     * Test Card    MSR credit card
     * Procedure
     *      1. Process a Credit Sale for $2.00 using any ECRRefNum
     *      2. Reprocess the Credit Sale using same amount and the same ECRRefNum
     * Pass Criteria
     *      1. Provide Debug logs showing the two Credit Sales for $2.00. Both must to be using the same
     *         ECRRefNum. The Log should reflect the Credit Sale failure due to a duplicate transaction.
     *
     */
    
    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     * @expectedExceptionMessage Unexpected Gateway Response: HOST001 - HOST ERROR
     */
    
    public function test006DuplicateTransaction()
    {
        $response = $this->device->creditSale(2)
        ->withRequestId(22)
        ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->printReceipt($response, 'test006DuplicateTransaction creditSale $2');
        
        $response = $this->device->creditSale(2)
        ->withRequestId(22)
        ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('2', $response->deviceResponseCode);
        $this->printReceipt($response, 'test006DuplicateTransaction creditSale $2'); // if cancelled via device prompt
    }
    
    /**
     * Objective    Confirm support of a Return transaction for credit/debit using the gateway TxnId
     * Test Card    Magnetic stripe Visa
     * Procedure
     *      1. Select sale function for the amount of $4.00
     *      2. Swipe or key test card #4 through the MSR, record the TxnId
     *      3. Select Refund function to refund the previous sale of $4.00, use the TxnId from the previous sale
     * Pass Criteria
     *      1. Transaction must be approved using the TxnId
     *
     */
    
    public function test007CreditReturn()
    {
        $response = $this->device->creditSale(4)
        ->withAllowDuplicates(1)
        ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->terminalRefNumber);
        $this->printReceipt($response, 'test007CreditReturn creditSale $4');
        
        $refundResponse = $this->device->creditRefund(4)
        ->withTransactionId($response->transactionId)
        ->execute();
        
        $this->assertNotNull($refundResponse);
        $this->assertEquals('00', $refundResponse->deviceResponseCode);
        $this->printReceipt($refundResponse, 'test007CreditReturn creditRefund $4');
    }
    
    /**
     * Objective    Complete Token request and then use that token for a Sale transaction
     * Test Card    MSR credit card
     * Procedure
     *      1. Perform a Verify transaction
     *          a. Review the response back from our host and locate the token value that is returned.
     *             Store this within your localized token value and be able to retrieve it for the next
     *             transaction
     *      2. Perform a Sale transaction
     *          a. Use the token value that you received and process a transaction for $15.01
     * Pass Criteria
     *      1. TokenValue returned in response
     *      2. Transaction #2 receives response code of 00
     *
     */
    
    public function test008TokenPayment()
    {
        $response = $this->device->creditVerify()
        ->withRequestMultiUseToken(1)
        ->withCardOnFileIndicator(StoredCredentialInitiator::MERCHANT)
        ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->token);
        $this->assertNotNull($response->cardBrandTransId);
        $this->printReceipt($response, 'test008TokenPayment creditVerify');
        
        $saleResponse = $this->device->creditSale(15.01)
        ->withToken($response->token)
        ->withCardOnFileIndicator(StoredCredentialInitiator::MERCHANT)
        ->withCardBrandTransId($response->cardBrandTransId)
        ->execute();
        
        $this->assertNotNull($saleResponse);
        $this->assertEquals('00', $saleResponse->deviceResponseCode);
        $this->printReceipt($response, 'test008TokenPayment creditSale');
    }
      
    
    /**
     * Objective    Confirm support of EMV PIN Debit sale
     * Test Card    EMV PIN Debit Card (not provided by Heartland)
     * Procedure
     *      1. Select Sale function and Select Debit for the card type OR select DEBIT function. For the test
     *         amount, use $10.00
     * Pass Criteria
     *      1. Transaction must be approved online
     *
     */
    
    public function test009DebitSale()
    {
        $response = $this->device->debitSale(10)
        ->withAllowDuplicates(1)
        ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->printReceipt($response, 'test009DebitSale debitSale $10');
    }
    
    /**
     * Objective    Complete Sale with Tip Adjustment
     * Pick One Gratuity Approach
     *      1. Credit Auth + Credit Capture
     *      2. Credit Sale + Tip Adjust // this one
     * Test Card    Mastercard
     * Procedure
     *      1. Select Sale function and process for the amount of $15.12
     *      2. Add a $3.00 tip at settlement
     * Pass Criteria
     *      1. Transaction must be approved.
     *
     * Note - Confirmed there is a bug in V1.30 regarding how the tip adjust amount is handled; that amount
     * isn't correctly added to the total transaction amount; earlier software versions did not have this bug
     *
     */
    
    public function test010aAdjustment()
    {
        $response = $this->device->creditSale(15.12)
        ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->printReceipt($response, 'test010aAdjustment Tip Sale');
        
        $response = $this->device->creditTipAdjust(3)
        ->withTerminalRefNumber($response->terminalRefNumber)
        ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->printReceipt($response, 'test010aAdjustment Tip Adjust');
        
        /* Note - Confirmed there is a bug in V1.30 regarding how the tip adjust amount is handled; that amount
         * isn't correctly added to the total transaction amount; earlier software versions did not have this bug
         */
        //$this->assertEquals('18.12', $response->transactionAmount);
        
    }
    
    /**
     * Objective    Complete Sale with Tip Adjustment
     * Pick One Gratuity Approach
     *      1. Credit Auth + Credit Capture // this one
     *      2. Credit Sale + Tip Adjust
     * Test Card    Mastercard
     * Procedure
     *      1. Select Sale function and process for the amount of $15.12
     *      2. Add a $3.00 tip at settlement
     * Pass Criteria
     *      1. Transaction must be approved.
     *
     */
    public function test010bAuthCapture()
    {
        $authResponse = $this->device->creditAuth(15.12)
        ->withAllowDuplicates(1)
        ->execute();
        
        $this->assertNotNull($authResponse);
        $this->assertEquals('00', $authResponse->deviceResponseCode);
        $this->assertNotNull($authResponse->transactionId);
        $this->printReceipt($authResponse, 'test010bAuthCapture Auth');

        $captureResponse = $this->device->creditCapture(18.12)
        ->withTransactionId($authResponse->transactionId)
        ->execute();
        
        $this->assertNotNull($captureResponse);
        $this->assertEquals('00', $captureResponse->deviceResponseCode);
        $this->assertNotNull($captureResponse->transactionId);
        $this->printReceipt($authResponse, 'test010bAuthCapture Capture');
    }
    
    /**
     * Objective    Transactions: Gift Balance Inquiry, Gift Load, Gift Sale/Redeem, Gift Replace
     * Test Card    Heartland Test Gift Cards
     * Procedure
     *      1. Gift Balance Inquiry
     *          a. Should respond with a balance amount of $10
     *      2. Gift Add Value
     *          a. Initiate a load and swipe
     *          b. Enter $8.00 as the amount
     *      3. Gift Sale
     *          a. Initiate a Sale and swipe
     *          b. Enter $1.00 as the amount
     *      4. Gift card replace
     *          a. Initiate a gift card replace
     * Pass Criteria
     *      1. All transactions must be approved.
     *
     */
    public function test011GiftCard()
    {
    }
    
    /**
     * Objective    Transactions: Food Stamp Purchase, Food Stamp Return, Food Stamp Balance Inquiry
     * Test Card    MSD only Visa
     * Procedure
     *      1. Food Stamp Purchase
     *          a. Initiate an EBT Sale and swipe
     *          b. Select EBT Food Stamp if prompted
     *          c. Enter $101.01 as the amount
     *      2. Food Stamp Return
     *          a. Initiate an EBT return and manually enter
     *          b. Select EBT Food Stamp if prompted
     *          c. Enter $104.01 as the amount
     *      4. Food Stamp Balance Inquiry
     *          a. Initiate an EBT balance inquiry transaction
     * Pass Criteria
     *      1. All transactions must be approved.
     *
     */
    public function test012EBT()
    {
    }
    
    /**
     * Objective    Transactions: Food Stamp Purchase, Food Stamp Return, Food Stamp Balance Inquiry
     * Test Card    MSD only Visa
     * Procedure
     *      1. Food Stamp Purchase
     *          a. Initiate an EBT Sale and swipe
     *          b. Select EBT Food Stamp if prompted
     *          c. Enter $101.01 as the amount
     *      2. Food Stamp Return
     *          a. Initiate an EBT return and manually enter
     *          b. Select EBT Food Stamp if prompted
     *          c. Enter $104.01 as the amount
     *      3. Food Stamp Balance Inquiry
     *          a. Initiate an EBT balance inquiry transaction
     * Pass Criteria
     *      1. All transactions must be approved.
     *
     */
    public function test013EBTCash()
    {
    }
    
    /**
     * Objective    Send extended healthcare (Rx, Vision, Dental, Clinical)
     * Test Card    Visa
     * Procedure
     *      1. Process a Sale for $100.00, with $50 being qualified for healthcare. Choose any of the groups
     *         (Rx, Vision, Dental, Clinical)
     * Pass Criteria
     *      1. Transaction must be approved online
     *
     */
    public function test014Healthcare()
    {
        $autoSubAmounts = new AutoSubstantiation();
        $autoSubAmounts->setPrescriptionSubTotal(12.50);
        $autoSubAmounts->setDentalSubTotal(12.50);
        $autoSubAmounts->setVisionSubTotal(12.50);
        
        $response = $this->device->creditSale(100)
        ->withAllowDuplicates(1)
        ->withAutoSubstantiation($autoSubAmounts)
        ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
    }
    
    /**
     * Objective    Process the 3 types of Corporate Card transactions: No Tax, Tax Amount, and Tax
     *              Exempt, including the passing of PO Number
     * Test Card    Magnetic stripe Visa
     * Procedure
     *      1. Select Sale function for the amount of $112.34
     *          a. Receive CPC Indicator of B
     *          b. Continue with CPC Edit transaction to account for Tax Type of Not Used
     *          c. Enter the PO Number of 98765432101234567 on the device
     *      2. Select Sale function for the amount of $123.45
     *          a. Receive CPC Indicator of R
     *          b. Continue with CPC Edit transaction to account for Tax Type of Sales Tax, Tax Amount for $1.00
     *      3. Select Sale function for the amount of $134.56
     *          a. Receive CPC Indicator of S
     *          b. Continue with CPC Edit transaction to account for Tax Type of Tax Exempt
     *          c. Enter the PO Number of 98765432101234567 on the device
     * Pass Criteria
     *      1. Transactions must be approved online
     *
     * note - Lvl2 doesn't seem to be fully supported as of V1.30
     */
    public function test015Level2()
    {
        $response = $this->device->creditSale(112.34)
        ->withAllowDuplicates(1)
        ->execute();
    }
    
    /**
     * Objective    Process credit sale in Store and Forward, upload transaction, close batch
     * Test Card    EMV Visa
     * Procedure
     *      1. Select Sale function for an amount of $4.00
     *          a. Response approved
     *      2. Send SAF command
     *          a. SAF Indicator = 2
     *          b. Result OK
     *      3. Initiate a Batch Close
     * Pass Criteria
     *      1. Transaction must approve in SAF and settles in a batch
     *
     */
    public function test016StoreAndForwardWithApproval()
    {
    }
    
    /**
     * Objective    Process credit sale in Store and Forward, upload transaction, delete declined
     *              transaction from terminal
     * Procedure
     *      1. Select Sale function for an amount of $10.25
     *          a. Response approved
     *      2. Send SAF command
     *          a. SAF Indicator = 2
     *          b. Transaction will decline
     *      3. Perform delete SAF file
     *          a. SAF Indicator = 2
     * Pass Criteria
     *      1. Transaction must approve in SAF and settles in a batch
     *
     */
    public function test017StoreAndForwardWithDecline()
    {
    }
    
    /**
     * Objective    Apply a surcharge to a transaction. You will need to make sure that you have worked with
     *              the Heartland team to set a surcharge amount for all qualifying transactions.
     * Test Card    EMV Mastercard // I used and configured EMV Amex for surcharge
     * Procedure
     *      1. Process a Credit Sale transaction for $50.00 with a 3.5% surcharge
     * Pass Criteria
     *      1. Printed receipt shows that a surcharge was added to the total amount and that the total amount
     *         processed matches the principle amount plus the surcharge
     *
     * Tony note: current device programming doesn't seem to be setup for Surcharging; I tried configuring for
     * Surcharging in the Device Manager, but it did not work     *
     *
     */
    public function test018Surcharge()
    {
        $response = $this->device->creditSale(50)
        ->execute();
    }
    
    
    /**
     * Objective    Close the batch, ensuring all approved transactions (offline or online) are settled.
     *              Integrators are automatically provided accounts with auto-close enabled, so if manual batch transmission
     *              will not be performed in the production environment, it does not need to be tested.
     * Test Card    N/A
     * Procedure
     *      1. Initiate a Batch Close request
     * Pass Criteria
     *      1. Batch submission must be successful
     *
     */
    
    public function test019BatchClose()
    {
        $response = $this->device->batchClose();
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
        $this->assertNotNull($response->batchId);
        
        $response = $this->device->batchReport($response->batchId);
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
        $this->assertNotNull($response->batchSummary);
        $this->assertNotNull($response->batchTransactions);
    }
}
