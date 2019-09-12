<?php
namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals\HPA;

use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\DeviceType;
use GlobalPayments\Api\Services\DeviceService;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;

class HpaVerificationTests extends TestCase
{
    public function setup()
    {
        $this->device = DeviceService::create($this->getConfig());
        
        //open lane for credit transactions
        $this->device->openLane();
    }

    protected function getConfig()
    {
        $config = new ConnectionConfig();
        $config->ipAddress = '10.138.141.5';
        $config->port = '12345';
        $config->deviceType = DeviceType::HPA_ISC250;
        $config->connectionMode = ConnectionModes::TCP_IP;
        $config->timeout = 300;
        $config->requestIdProvider = new RequestIdProvider();

        return $config;
    }

    public function tearDown()
    {
        sleep(3);
        $this->device->reset();
    }

    private function printReceipt($response)
    {
        $receipt = "x_trans_type=" . $response->response;
        $receipt .= "&x_application_label=" . $response->emvApplicationName;
        $receipt .= "&x_masked_card=" . $response->maskedCardNumber;
        $receipt .= "&x_application_id=" . $response->emvApplicationId;
        $receipt .= "&x_cryptogram_type=" . $response->emvCryptogramType;
        $receipt .= "&x_application_cryptogram=" . $response->emvCryptogram;
        $receipt .= "&x_expiration_date=" . $response->expirationDate;
        $receipt .= "&x_entry_method=" . $response->entryMethod;
        $receipt .= "&x_approval=" . $response->approvalCode;
        $receipt .= "&x_transaction_amount=" . $response->transactionAmount;
        $receipt .= "&x_amount_due=" . $response->balanceAmountDue;
        $receipt .= "&x_customer_verification_method=" . $response->emvCardHolderVerificationMethod;
        $receipt .= "&x_response_text=" . $response->responseText;
        $receipt .= "&x_signature_status=" . $response->signatureStatus;
        print($receipt);
    }

    /*
        TEST CASE #1 – Contact Chip and Signature – Offline
        Objective   Process a contact transaction where the CVM’s supported are offline chip and signature
        Test Card   Card #1 - MasterCard EMV
        Procedure   Perform a complete transaction without error..
                    Enter transaction amount $23.00.
    */
    
    public function testCase01()
    {
        $response = $this->device->creditSale(23)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);

        print("Response: " . print_r($response, true));
        print("Gateway Txn ID: " . $response->transactionId);
        
        $this->printReceipt($response);
    }

    /*
        TEST CASE #2 - EMV Receipts 
        Objective       1. Verify receipt image conforms to EMV Receipt Requirements.
                        2. Verify that signature capture functionality works.
        Test Card	Any card brand – Visa, MC, Discover, AMEX.
        Procedure	Run an EMV insert sale using any card brand.
                        The device should get an Approval.
                        Cardholder is prompted to sign on the device.
    */
    
    public function testCase02()
    {
        // print receipt for TestCase01
        $this->testCase01();
    }

    /*
        TEST CASE #3 - Approved Sale with Offline PIN
        Objective	Process an EMV contact sale with offline PIN.
        Test Card	Card #1 - MasterCard EMV
        Procedure	Insert the card in the chip reader and follow the instructions on the device.
                        Enter transaction amount $25.00.
                        When prompted for PIN, enter 4315.
                        If no PIN prompt, device could be in QPS mode with limit above transaction amount.
    */
    
    public function testCase03()
    {
        $response = $this->device->creditSale(25)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);

        print("Response: " . print_r($response, true));
        print("Gateway Txn ID: " . $response->transactionId);
        
        $this->printReceipt($response);
    }

    /*
        TEST CASE #4 -  Manually Entered Sale with AVS & CVV2/CID (If AVS is supported)
        Objective	Process a keyed sale, with PAN & exp date, along with Address Verification 
                        and Card Security Code to confirm the application can support any or all of these.
        Test Card	Card #5 – MSD only MasterCard
        Procedure	1. Select sale function and manually key Test Card #5 for the amount of $90.08.
                            a.	Enter PAN & expiration date.
                            b.	Enter 321 for Card Security Code (CVV2, CID), if supporting this feature.
                        Enter 76321 for AVS, if supporting this feature.
    */
    
    public function testCase04()
    {
        $response = $this->device->creditSale(90.08)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
        
        $this->assertEquals("Zip and address match.", $response->avsResponseText);
        $this->assertEquals("Not Processed.", $response->cvvResponseText);

        print("Response: " . print_r($response, true));
        print("Gateway Txn ID: " . $response->transactionId);
        
        $this->printReceipt($response);
    }
    
    /*
        TEST CASE #5 - Partial Approval
        Objective	1. Ensure application can handle non-EMV swiped transactions.
                        2. Validate partial approval support.
        Test Card	Card #4 – MSD only Visa
        Procedure	Run a credit sale and follow the instructions on the device to complete the transaction.
                        Enter transaction amount $155.00 to receive a partial approval.
                        Transaction is partially approved online with an amount due remaining.
    */
    
    public function testCase05()
    {
        $response = $this->device->creditSale(155)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals("10", $response->responseCode);
        $this->assertEquals(55, $response->balanceAmountDue);

        print("Response: " . print_r($response, true));
        print("Gateway Txn ID: " . $response->transactionId);
        
        $this->printReceipt($response);
    }

    /*
        TEST CASE #6 - Online Void
        Objective	Process an online void.
        Test Card	Card #3 – EMV Visa w/ Signature CVM
        Procedure	Enter the Transaction ID to void.
        Pass Criteria   Transaction has been voided.
    */
    
    public function testCase06()
    {
        $response = $this->device->creditSale(10)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
        
        $this->device->reset();

        $voidResponse = $this->device->creditVoid()
                ->withTransactionId($response->transactionId)
                ->execute();
        
        $this->assertNotNull($voidResponse);
        $this->assertEquals("00", $voidResponse->responseCode);

        print("Response: " . print_r($voidResponse, true));
        print("Gateway Txn ID: " . $voidResponse->transactionId);
    }

    /*
        TEST CASE #7 -  Debit Sale (with Cash Back) & Debit Void
        Objective	Confirm support of PIN debit sale, cash back, and debit void.
        Test Card	Confirm support of PIN debit sale, cash back, and debit void.
        Procedure	Debit Sale with Cash Back:
            Run a debit sale for $10.00 and follow the instructions on the device to complete the transaction.
            When prompted for Cash Back, enter $5.00 for the cash back amount.
            When prompted for PIN, enter 1234.
            Transaction is approved online.

        Void:
            Enter the Transaction ID to void.
            Transaction has been voided.

        Pass Criteria	The transaction is approved online.
        The transaction has been voided.
    */
    
    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\UnsupportedTransactionException
     */
    public function testCase07()
    {
        $response = $this->device->debitSale(10)
          ->execute();

        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
        
        print("Response: " . print_r($response, true));
        print("Gateway Txn ID: " . $response->transactionId);

        $voidResponse = $this->device->debitVoid()
          ->withTransactionId($response->transactionId)
          ->execute();
    }

    /*
        TEST CASE  #8 – Process Lane Open on SIP
        Objective	Display line items on the SIP.
        Test Card	NA
        Procedure	Start the process to open a lane on the POS.
    */
    
    public function testCase08()
    {
        $response = $this->device->openLane();
        
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
    }

    /*
        TEST CASE #9 – Credit Return
        Objective	Confirm support of a Return transaction for credit.
        Test Card	Card #4 – MSD only Visa
        Procedure	1.	Select return function for the amount of $9.00
        2.	Swipe or Key Test card #4 through the MSR
        3.	Select credit on the device
    */
    
    public function testCase09()
    {
        $response = $this->device->creditRefund(9)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);

        print("Response: " . print_r($response, true));
        print("Gateway Txn ID: " . $response->transactionId);
    }

    /*
        TEST CASE #10 – HMS Gift 
        Objective	Transactions: Gift Balance Inquiry,  Gift Load,  Gift Sale/Redeem, Gift Replace
        Test Card	Gift Card (Card Present/Card Swipe)
        Procedure	Test System is a Stateless Environment, the responses are Static.
        1.	Gift Balance Inquiry (GiftCardBalance):
            a.	Should respond with a BalanceAmt of $10
        2.	Gift Load (GiftCardAddValue):
            a.	Initiate a Sale and swipe
            b.	Enter $8.00 as the amount
        3.	Gift Sale/Redeem (GiftCardSale):
            a.	Initiate a Sale and swipe
            b.	Enter $1.00 as the amount
        4.	Gift Card Replace (GiftCardReplace)
            a.	Initiate a Gift Card Replace
            b.	Swipe Card #1 – (Acct #: 5022440000000000098)
            c.	Manually enter  Card #2 –  (Acct #: “5022440000000000007”)
    */
    public function testCase10a()
    {
        $response = $this->device->giftBalance()
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);
        $this->assertEquals('10', $response->availableBalance);

        print("Response: " . print_r($response, true));
        print("Gateway Txn ID: " . $response->transactionId);
    }

    public function testCase10b()
    {
        $response = $this->device->giftAddValue(8)
        ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);

        print("Response: " . print_r($response, true));
        print("Gateway Txn ID: " . $response->transactionId);
    }

    public function testCase10c()
    {
        $response = $this->device->giftSale(1)
        ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);

        print("Response: " . print_r($response, true));
        print("Gateway Txn ID: " . $response->transactionId);
    }
        
    /*
        TEST CASE #11 – EBT Food Stamp
        Objective	Transactions: Food Stamp Purchase, Food Stamp Return and Food Stamp Balance Inquiry
        Test Card	Card #4 – MSD only Visa
        Procedure	Test System is a Stateless Environment, the responses are Static.
        5.	Food Stamp Purchase (EBTFSPurchase):
            c. Initiate an EBT sale transaction and swipe Test Card #4
            d. Select EBT Food Stamp if prompted.
            e. Enter $101.01 as the amount
        6.	Food Stamp Return (EBTFSReturn):
            b. Intitiate an EBT return and manually enter Test Card #4
            c. Select EBT Food Stamp if prompted
            d. Enter $104.01 as the amount
        7.	Food Stamp Balance Inquiry (EBTBalanceInquiry):
            c. Initiate an EBT blance inquiry transaction and swipe Test Card #4 Settle all transactions.
    */
    
    public function testCase11a()
    {
        $response = $this->device->ebtPurchase(101.01)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);

        print("Response: " . print_r($response, true));
        print("Gateway Txn ID: " . $response->transactionId);
    }
    
    public function testCase11b()
    {
        $response = $this->device->ebtRefund(104.01)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);

        print("Response: " . print_r($response, true));
        print("Gateway Txn ID: " . $response->transactionId);
    }
    
    public function testCase11c()
    {
        $response = $this->device->ebtBalance()
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);

        print("Response: " . print_r($response, true));
        print("Gateway Txn ID: " . $response->transactionId);
    }
    
    /*
        TEST CASE #12 – EBT Cash Benefits
        Objective	Transactions: EBT Cash Benefits with Cash Back, EBT Cash Benefits Balance Inquiry
and EBT Cash Benefits Withdraw
        Test Card	Card #4 – MSD only Visa
        Procedure	
        1.	EBT Cash Benefits w Cash Back (EBTCashBackPurchase):
            a. Initiate an EBT sale transaction and swipe Test Card #4
            b. Select EBT Cash Benefits if prompted
            c. Enter $101.01 as the amount
            d. Enter $5.00 as the cash back amount
            e. The settlement amount is $106.01
        2.	EBT Cash Benefits Balance Inquiry (EBTBalanceInquiry):
            a. Initiate an EBT cash benefit balance inquiry transaction and swipe Test Card #4
        3.	EBT Cash Benefits Withdraw (EBTCashBenefitWithdrawal):
            a. Initiate an EBT cash benefits withdraw transaction and manually
enter Test Card #4.
            b. Select EBT Cash Benefits Withdraw if prompted
            c. Enter $111.01 as the amount
            d. The settlement amount is $111.01
            e. Settle all transactions
    */
    
    public function testCase12a()
    {
        $response = $this->device->ebtPurchase(101.01)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);

        print("Response: " . print_r($response, true));
        print("Gateway Txn ID: " . $response->transactionId);
    }
    
    public function testCase12b()
    {
        $response = $this->device->ebtBalance()
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->resultCode);

        print("Response: " . print_r($response, true));
        print("Gateway Txn ID: " . $response->transactionId);
    }
    
    /*
        TEST CASE #13 – Batch Close 
        (Mandatory if Conditional Test Cases are ran)
        Objective	Close the batch, ensuring all approved transactions (offline or online) are settled.
        Integrators are automatically provided accounts with auto-close enabled, so if manual batch transmission 
        will not be performed in the production environment then it does not need to be tested.
        Test Card	N/A
        Procedure	Initiate a Batch Close command
        Pass Criteria	Batch submission must be successful.
        Batch Sequence #:
        References		HPA Specifications.
    */
    public function testCase13()
    {
        $this->device->closeLane();
        $this->device->reset();

        $response = $this->device->batchClose();
        $this->assertNotNull($response);
        
        $this->assertEquals('0', $response->resultCode);

        print("Response: " . print_r($response, true));
        print("Gateway Txn ID: " . $response->transactionId);
    }
}
