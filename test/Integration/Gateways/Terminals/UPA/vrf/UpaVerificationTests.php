<?php
namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals\UPA\VRF;

use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\CurrencyType;
use GlobalPayments\Api\Terminals\Enums\DeviceType;
use GlobalPayments\Api\Services\DeviceService;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\LogManagement;

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
        $this->config->ipAddress = '192.168.47.79';
        $this->config->port = '8081';
        $this->config->deviceType = DeviceType::UPA_SATURN_1000;
        $this->config->connectionMode = ConnectionModes::TCP_IP;
        $this->config->timeout = 30;
        $this->config->requestIdProvider = new RequestIdProvider();
        $this->config->logManagementProvider = new LogManagement();
        
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

    /*
        TEST CASE 1 - EMV Contact Sale with Offline PIN
        Objective   
            1.Process an EMV contact sale with offline PIN.
        Test Card   
            Card #1 EMV MasterCard w Offline PIN
        Procedure   
			1.Select Sale function for an amount of $4.00.
				a.Insert Test Card #1 and select application if prompted.
					- On CVM Kernel Terminal will prompt for PIN; enter 4315.
					- On No CVM Kernel Terminal, No PIN Prompt will occur
				b.Terminal will prompt for PIN; enter 4315.
				c.Terminal will respond approved.
        Pass Criteria
			1.Transaction must be approved.
			2.Receipt must conform to EMV Receipt Requirements (see pg 2).
    */
    
    public function testCase01()
    {
        $response = $this->device->creditSale(4)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        
        $this->printReceipt($response, 'testCase01 creditSale');
    }
    
    /*
        TEST CASE 2 - Non EMV Swiped Sale
		Objective
			1.Ensure application can handle non-EMV swiped transactions.
			2.Validate partial approval support.
		Test Card
			Card #4 Magnetic stripe Visa
		Procedure
			1.Select sale function and swipe Test Card #4 for the amount of $7.00
			2.Select sale function and swipe Test Card #4 for the amount of $155.00
			a.Receive an approved amount less than requested.
		Pass Criteria
			1.Transactions must be approved online.
			2.For 2nd Credit Sale, provide:
			Approved Amount: $ 100.00
			Issuer Response Code: 10
			3.Receipt must conform to Mag Stripe Receipt Requirements (see pg 2).
    */
    
    public function testCase02()
    {
        $response = $this->device->creditSale(7)
                ->execute();
                
        $this->printReceipt($response, 'testCase02 creditSale $7');
                
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
                
        $response = $this->device->creditSale(155)
                ->execute();
                
        $this->printReceipt($response, 'testCase02 creditSale $155');
                
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('100', $response->transactionAmount);
        $this->assertEquals('10', $response->responseCode);
    }

    /*
		TEST CASE 3 - Mag Stripe Online Void
		Objective
			Process an online void.
		Test Card
			Card #5 MSD only MasterCard
		Procedure
			1.Select Void function to remove the previous Sale of $7.00.
				a.Enter on the Portico Gateway software Txnld
				(UPA HREF) from the 1st Credit Sale in Test Case 2 when prompted.
			b.Select Credit, same as Test Case 2
		Pass Criteria
			Transaction receives Gateway Response Code of 0, Issuer Response Code of 00.	
	*/
    
    public function testCase03()
    {
        $response = $this->device->creditSale(7)
                ->execute();
        
        $this->printReceipt($response, 'testCase03 creditSale');
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->terminalRefNumber);
         
        $voidResponse = $this->device->creditVoid()
                ->withTerminalRefNumber($response->terminalRefNumber)
                ->execute();
                
        $this->printReceipt($voidResponse, 'testCase03 creditVoid');
        
        $this->assertNotNull($voidResponse);
        $this->assertEquals('00', $voidResponse->deviceResponseCode);
    }
    
    
    /*
     * CONDITIONAL TEST CASE 8 Credit Return
       Objective
            Confirm support of a Return transaction for credit/debit using the HREF or GatewayTxnID.
       Test Card
            Card #4 Magnetic stripe Visa
       Procedure
            1.Select sale function for the amount of $4.00
            2.Swipe or Key Test card #4 through the MSR, record the HREF
            3.Select Refund function to refund the previous sale of $4.00, use the HREF from the previous sale
       Pass Criteria
            1.Transaction must be approved using the HREF.
     * 
     */
    
    public function testCase08()
    {
        $response = $this->device->creditSale(4)
        ->withAllowDuplicates(1)
        ->execute();
        
        $this->printReceipt($response, 'testCase08 creditSale $4');
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->terminalRefNumber);
        
        $refundResponse = $this->device->creditRefund(4)
        ->execute();
        
        $this->printReceipt($refundResponse, 'testCase08 creditRefund $4');
        
        $this->assertNotNull($refundResponse);
        $this->assertEquals('00', $refundResponse->deviceResponseCode);
    }
    
    
    /*
     * CONDITIONAL TEST CASE 9 EMV PIN Debit Sale/ Swiped MSR Debit Sale
       Objective
            Confirm support of PIN Debit sale
       Test Card
            Card #4 EMV MasterCard w/ Offline PIN & Magnetic stripe Visa
       Procedure
            1.Select sale function for the amount of $10.00.
                a.Insert EMV Test Card (For this test case you will need to use your own EMV PIN Debit Card)
                b.Select Debit on the card type prompt.
                c.Enter PIN of 1234 when pad prompts for it, record the HREF.
            2.Select sale function for the amount of $11.00.
                a.Swipe Test Card #4
                b.Select Debit on the card type prompt.
                c.Enter PIN of 1234 when pad prompts for it.
       Pass Criteria
            1. Transaction must be approved online. Logs must be provided reflecting this transaction. 
            Provide 1st Inserted EMV Debit Sale:Host Reference Number:2nd Swiped Debit Sale:
     */
    
    public function testCase09()
    {
        $response = $this->device->debitSale(10)
        ->withAllowDuplicates(1)
        ->execute();
        
        $this->printReceipt($response, 'testCase09 debitSale $10');
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        
        $response = $this->device->debitSale(11)
        ->withAllowDuplicates(1)
        ->execute();
        
        $this->printReceipt($response, 'testCase09 debitSale $11');
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
    }
    
    /*
     * CONDITIONAL TEST CASE 11 Tip Adjustment
       Objective
            Complete Sale with Tip Adjustment
            Pick One Gratuity Approach:
            1) CREDIT AUTH + CREDIT POSTAUTH (Portico CreditAddToBatch)
            2) CREDIT SALE + CREDIT ADJUST (Portico CreditTxnEdit)
       Test Card
            Card #5 MSD only MasterCard (AVS required)
       Procedure
            1.Select SALE function and swipe Card #5 for the amount of $15.12
            2.Add a $3.00 tip at Settlement.
       Pass Criteria
            1.Transaction must be approved. Using HREF.
            2.Adjusted Tip:
            Approved Amount: $18.12
     * 
     */
    
    public function testCase11()
    {
        $response = $this->device->creditSale(15.12)
        ->execute();
        
        $this->printReceipt($response, 'testCase11 Tip Sale');
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        
        $response = $this->device->creditTipAdjust(3)
        ->withTerminalRefNumber($response->terminalRefNumber)
        ->execute();
        
        $this->printReceipt($response, 'testCase11 Tip Adjust');
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('18.12', $response->transactionAmount);
    }
    
    /*
        CONDITIONAL TEST CASE 19 Batch Close
        (Mandatory if Conditional Test Cases are ran)
     Objective
        Close the batch, ensuring all approved transactions (offline or online) are settled.
        Integrators are automatically provided accounts with auto-close enabled, so if manual batch transmission will 
        not be performed in the production environment then it does not need to be tested.
     Test Card
        N/A
     Procedure
        Initiate a Batch Close command
     Pass Criteria
        Batch submission must be successful.
     * 
     */
    
    public function testCase19()
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
