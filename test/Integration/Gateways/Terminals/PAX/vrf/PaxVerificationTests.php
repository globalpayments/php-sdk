<?php
namespace GlobalPayments\Api\Tests\Integration\Gateways\Terminals\PAX\VRF;

use GlobalPayments\Api\Terminals\ConnectionConfig;
use GlobalPayments\Api\Terminals\Enums\ConnectionModes;
use GlobalPayments\Api\Terminals\Enums\DeviceType;
use GlobalPayments\Api\Services\DeviceService;
use GlobalPayments\Api\Tests\Data\TestCards;
use PHPUnit\Framework\TestCase;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\RequestIdProvider;
use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\Entities\Address;
use GlobalPayments\Api\Tests\Integration\Gateways\Terminals\LogManagement;
use GlobalPayments\Api\Terminals\Enums\CurrencyType;
use GlobalPayments\Api\Terminals\Enums\SafMode;
use GlobalPayments\Api\Terminals\Enums\SafUpload;
use GlobalPayments\Api\Entities\Enums\TaxType;
use GlobalPayments\Api\Terminals\Enums\SafDelete;

class PaxVerificationTests extends TestCase
{

    private $device;
    protected $card;
    protected $address;
    private $config;

    public function setup()
    {
        $this->device = DeviceService::create($this->getConfig());
        
        $this->card = new CreditCardData();
        $this->card->number = '5473500000000014';
        $this->card->expMonth = 12;
        $this->card->expYear = TestCards::validCardExpYear();
        $this->card->cvn = '123';
        $this->card->cardHolderName = 'Joe Smith';
        
        $this->address = new Address();
        $this->address->streetAddress1 = '6860 Dallas Pkwy';
        $this->address->postalCode = '76321';
    }
    
    public function tearDown()
    {
        sleep(3);
    }

    protected function getConfig()
    {
        $this->config = new ConnectionConfig();
        $this->config->ipAddress = '192.168.42.219';
        $this->config->port = '10009';
        $this->config->deviceType = DeviceType::PAX_S300;
        $this->config->connectionMode = ConnectionModes::TCP_IP;
        $this->config->timeout = 30;
        $this->config->requestIdProvider = new RequestIdProvider();
        $this->config->logManagementProvider = new LogManagement();

        return $this->config;
    }

    private function printReceipt($response, $message = '')
    {
        print("\n\n Gateway Txn ID: " . $response->transactionId);

        $receipt = "x_trans_type=" . $response->transactionType;
        $receipt .= "&x_application_label=" . $response->applicationPreferredName;
        $receipt .= "&x_masked_card=" . $response->maskedCardNumber;
        $receipt .= "&x_application_id=" . $response->applicationId;
        $receipt .= "&x_cryptogram_type=" . $response->applicationCryptogramType;
        $receipt .= "&x_application_cryptogram=" . $response->applicationCryptogram;
        $receipt .= "&x_expiration_date=" . $response->expirationDate;
        $receipt .= "&x_entry_method=" . $response->entryMethod;
        $receipt .= "&x_approval=" . $response->approvalCode;
        $receipt .= "&x_transaction_amount=" . $response->transactionAmount;
        $receipt .= "&x_amount_due=" . $response->balanceAmount;
        $receipt .= "&x_customer_verification_method=" . $response->customerVerificationMethod;
        $receipt .= "&x_response_text=" . $response->responseText;
        $receipt .= "&x_signature_status=" . $response->signatureStatus;
        
        print("\n Receipt: ".$receipt);
        
        $log = "\n $message Gateway Txn ID: " . $response->transactionId
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
        References
            PAX POSLink (Windows) API Guide, Section 4.5: Class PaymentRequest
            PAX Interface Between ECR PC and Terminal, Section 5.2.1: Do Credit
    */
    
    public function testCase01()
    {
        $response = $this->device->creditSale(4)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->deviceResponseCode);
        $this->assertEquals('00', $response->responseCode);
        
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
		References
			-  PAX POSLink (Windows) API Guide, Section 4.5: Class PaymentRequest
			-  PAX POSLink (Windows) API Guide, Section 4.9: Class PaymentResponse
			-  PAX Interface Between ECR PC and Terminal, Section 5.2.1: Do Credit
			-  PAX Interface Between ECR PC and Terminal, Section 5.6: Response Sub-groupInformation
    */
    
    public function testCase02()
    {
        $response = $this->device->creditSale(7)
                ->execute();
                
        $this->printReceipt($response, 'testCase02 creditSale $7');
                
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->deviceResponseCode);
        $this->assertEquals('00', $response->responseCode);
                
        $response = $this->device->creditSale(155)
                ->withAddress($this->address)
                ->execute();
                
        $this->printReceipt($response, 'testCase02 creditSale $155');
                
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->deviceResponseCode);
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
				(PAX HREF) from the 1st Credit Sale in Test Case 2 when prompted.
			b.Select Credit, same as Test Case 2
		Pass Criteria
			Transaction receives Gateway Response Code of 0, Issuer Response Code of 00.
		References
			-  PAX POSLink (Windows) API Guide, Section 4.5: Class PaymentRequest
			-  PAX POSLink (Windows) API Guide, Appendix 5.1: Payment TransType
			-  PAX Interface Between ECR PC and Terminal, Section 5.2.1: Do Credit
	
	*/
    
    public function testCase03()
    {
        $response = $this->device->creditSale(7)
                ->withAddress($this->address)
                ->execute();
        
        $this->printReceipt($response, 'testCase03 creditSale');
        
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->deviceResponseCode);
        $this->assertNotNull($response->transactionId);
        $this->assertEquals('00', $response->responseCode);
         
        $voidResponse = $this->device->creditVoid()
                ->withTransactionId($response->transactionId)
                ->execute();
                
        $this->printReceipt($voidResponse, 'testCase03 creditVoid');
        
        $this->assertNotNull($voidResponse);
        $this->assertEquals('0', $voidResponse->deviceResponseCode);
        $this->assertEquals('00', $voidResponse->responseCode);
    }
    
    /*
		TEST CASE 4 - Manually Entered Sale with AVS & CVV2/CID (If AVS is supported)
		Objective
			Process a keyed sale, with PAN & exp date, along with Address Verification and Card Security Code to 
			confirm the application can support any or all of these.
		Test Card
			Card #5 Magnetic stripe MasterCard
		Procedure
			1.Select sale function and manually key Test Card #5 for the amount of $118.00.
				a.Enter PAN & expiration date.
				b.Enter 321 for Card Security Code (CVV2, CID), if supporting this feature.
				Enter 76321 for AVS, if supporting this feature.
		Pass Criteria
			1.Transaction must be approved online.
			2.AVS Result Code: Y
			CVV Result Code: M
		References
			-  PAX POSLink (Windows) API Guide, Section 4.5: Class PaymentRequest
			-  PAX Interface Between ECR PC and Terminal, Section 5.2.1: Do Credit

	*/
    
    public function testCase04()
    {
        $response = $this->device->creditSale(118)
                ->execute();
         
        $this->printReceipt($response, 'testCase04 creditSale AVS');
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertNotNull($response->transactionId);
    }
    
    /*
	TEST CASE 5 - Sale with Tokenization (Required if tokenization is supported)
	Objective
		Complete Sale and receive Token
	Test Card
		Card #3 EMV Visa w/ Signature CVM
		Card #5 MSD only MasterCard
	Procedure
		1.Select a SALE transaction for $15.01 using Card #3 with tokenization enabled.
		1.Select a SALE transaction for $15.02 using Card #5 with tokenization enabled.
	Pass Criteria
		1.Transaction # 1 receives Gateway Response Code of 0, Issuer Response Code of 00.
		2.Transaction # 2 receives Gateway Response Code of 0, Issuer Response Code of 00.
	References
		-  PAX POSLink (Windows) API PAX Interface Between ECR PC and Terminal,Section 5.5.9: 
		"Request Additional Information" and Section 5.6.10: ResponseAdditional Information	
	*/
    
    public function testCase05()
    {
        //generate token for EMV visa card
        $response = $this->device->creditSale(15.01)
                ->withRequestMultiUseToken(1)
                ->execute();
                
        $this->printReceipt($response, 'testCase05 creditSale $15.01');
        
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->deviceResponseCode);
        $this->assertEquals('00', $response->responseCode);
        $this->assertNotNull($response->token);
        
        //write the token in file for later use
        file_put_contents('EMVVisaToken', $response->token);
        
        //generate token for master card
        
        $response = $this->device->creditSale(15.02)
                ->withRequestMultiUseToken(1)
                ->execute();
        
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->deviceResponseCode);
        $this->assertEquals('00', $response->responseCode);
        $this->assertNotNull($response->token);
        
        $this->printReceipt($response, 'testCase05 creditSale $15.02');
        
        //write the token in file for later use
        file_put_contents('MSDMasterToken', $response->token);
    }
    
    /*
     * 
     * TEST CASE 6 - Sale with Tokenization (Required if Test Case 5 is executed)
       Objective
            Complete Sale using a Token on File
        Test Card
            Card #3 EMV Visa w/ Signature CVM
            Card #5 MSD only MasterCard
        Procedure
            1.Select a SALE transaction for $15.02 using the token generated for Card #3 in testcase #5.
            2.Select a SALE transaction for $15.03 using the token generated for Card #5 in testcase #5.
        Pass Criteria
            1.Transaction # 1 receives Gateway Response Code of 0, Issuer Response Code of 00
            2.Transaction # 2 receives Gateway Response Code of 0, Issuer Response Code of 00
        References
            -  PAX POSLink (Windows) API PAX Interface Between ECR PC and Terminal,Section 5.5.9: 
            "Request Additional Information" and Section 5.6.10: ResponseAdditional Information
     * 
     */
    
    public function testCase06()
    {
        $emvVisaToken = @file_get_contents('EMVVisaToken');
        $msdMasterToken = @file_get_contents('MSDMasterToken');
        
        if (empty($emvVisaToken) || empty($msdMasterToken)) {
            $this->markTestSkipped('Token values not captured in testCase05');
        }
        
        //Visa card token sale
        $emvVisaCard = new CreditCardData();
        $emvVisaCard->token = trim($emvVisaToken);
        
        $response = $this->device->creditSale(15.02)
        ->withPaymentMethod($emvVisaCard)
        ->execute();
        
        $this->printReceipt($response, 'testCase06 creditSale $15.02');
        
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->deviceResponseCode);
        $this->assertEquals('00', $response->responseCode);
        
        //master card token sale
        $msdMaster = new CreditCardData();
        $msdMaster->token = trim($msdMasterToken);
                
        $response = $this->device->creditSale(15.03)
        ->withPaymentMethod($msdMaster)
        ->execute();
        
        $this->printReceipt($response, 'testCase06 creditSale $15.03');
        
        $this->assertNotNull($response);
        $this->assertEquals('0', $response->deviceResponseCode);
        $this->assertEquals('00', $response->responseCode);
    }
       
    
    /*
     * 
     * TEST CASE 7 - ECRRefNum - Duplicate Transaction Check
       Objective
            To produce a CREDIT_SALE Fail!, ResultTxt= DUP TRANSACTION
       Test Card
            Card #5 Magnetic stripe MasterCard
       Procedure
            1.Process a Credit Sale for $2.00 utilizing any ECRRefNum
            2.Reprocess the Credit Sale using same amount and the same ECRRefNum
       Pass Criteria
            Provide Debug Logs showing the two Credit Sales for $2.00. Both must to be using the same ECRRefNum. 
            The Log should reflect the CREDIT_SALE Fail! DUP TRANSACTION
       References
            -   PAX POSLink (Windows) API PAX Interface Between ECR PC and Terminal
            Section 5.5.4

     */
    
    /**
     * @expectedException GlobalPayments\Api\Entities\Exceptions\GatewayException
     * @expectedExceptionMessage HOST DECLINE: DUPLICATE TRANSACTION
     */
    public function testCase07()
    {
        $clientTransactionId = 10000 + random_int(0, 99999);
        
        $response = $this->device->creditSale(2.00)
        ->withClientTransactionId($clientTransactionId)
        ->execute();
        
        $this->printReceipt($response, 'testCase07 creditSale $2.00');
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('0', $response->responseCode);
        
        $response = $this->device->creditSale(2.00)
        ->withClientTransactionId($clientTransactionId)
        ->execute();
    }
        
    
    /*
     * 
     * TEST CASE 8 - POSLink Debugging
       Objective
            To produce error or debug log during the investigation of Support Cases.
            It is expected that the Error Log is turned on by default in customer sites.
       Test Card
            NA
       Procedure
            When using PAX POSLink, demonstrate that the Application has the ability to turn on the
            LogManagement() Class and set the LogLevel to either Error Mode or Debug Mode.
            If using Low Level Integration demonstrate the ability to produce equivalent Log Files.
       Pass Criteria
            Creation of the Report
            Submit the Log File via email along with this VRF and the Receipts.
       References
            -   PAX POSLink (Windows) API PAX Interface Between ECR PC and Terminal
            Section 4.3.1 Constructors, 4.3.2 Methods, 4.3.3 Properties

     */
    
    
    
    
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
       References
            -  PAX POSLink (Windows) API Guide, Section 4.5: Class PaymentRequest
            -  PAX Interface Between ECR PC and Terminal, Section 5.2.1: Do Credit
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
        $this->assertNotNull($response->transactionId);
        
        $refundResponse = $this->device->creditRefund(4)
        ->withTransactionId($response->transactionId)
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
       References
            -  PAX POSLink (Windows) API Guide, Section 4.5: Class PaymentRequest
            PAX Interface Between ECR PC and Terminal, Section 5.2.2: Do Debit
     */
    
    public function testCase09()
    {
        $response = $this->device->debitSale(10)
        ->withAllowDuplicates(1)
        ->execute();
        
        $this->printReceipt($response, 'testCase09 debitSale $10');
        
        $this->assertNotNull($response);
        $this->assertEquals('OK', $response->deviceResponseText);
        
        $response = $this->device->debitSale(11)
        ->withAllowDuplicates(1)
        ->execute();
        
        $this->printReceipt($response, 'testCase09 debitSale $11');
        
        $this->assertNotNull($response);
        $this->assertEquals('OK', $response->deviceResponseText);
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
       References
            -  PAX POSLink (Windows) API Guide, Section 4.5: Class PaymentRequest
            -  PAX Interface Between ECR PC and Terminal, Section 5.2.1: Do Credit
     * 
     */
    
    public function testCase11()
    {
        $response = $this->device->creditSale(15.12)
        ->withGratuity(3)
        ->withAllowDuplicates(1)
        ->withAddress($this->address)
        ->execute();
        
        $this->printReceipt($response, 'testCase11 Tip');
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('18.12', $response->transactionAmount);
    }
    
        
    /*
     * CONDITIONAL TEST CASE 12 HMS Gift
       Objective
            Transactions: Gift Balance Inquiry, Gift Load, Gift Sale/Redeem, Gift Replace
       Test Card
            Gift Card (Card Present/Card Swipe)
       Procedure
            Test System is a Stateless Environment, the responses are Static.
            1.Gift Load (GiftCardAddValue):
                a.Initiate a Sale and swipe
                b.Enter $8.00 as the amount
            2.Gift Balance Inquiry (GiftCardBalance):
                 a.Should respond with a BalanceAmt of $10            
            3.Gift Sale/Redeem (GiftCardSale):
                a.Initiate a Sale and swipe
                b.Enter $1.00 as the amount
       Pass Criteria
            1.Gift Load (GiftCardAdd Value):            
            2.Gift Balance Inquiry (GiftCardBalance):
            3.Gift Sale/Redeem (GiftCardSale):
       References
            -  PAX Interface Between ECR PC and Terminal, Section 4.4: Transaction TypeDefinition
            -  PAX Interface Between ECR PC and Terminal, Section 5.2.4: Do Gift (T06) (T07)
     * 
     */
    
    public function testCase12()
    {
        $response = $this->device->giftAddValue(8)
        ->execute();
        
        $this->printReceipt($response, 'testCase12 giftAddValue');
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
        
        $response = $this->device->giftBalance()
        ->execute();
        
        $this->printReceipt($response, 'testCase12 giftBalance');
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
                
        $response = $this->device->giftSale(1)
        ->execute();
        
        $this->printReceipt($response, 'testCase12 giftSale');
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
    }
    
    
    /*
     * CONDITIONAL TEST CASE 13 EBT Food Stamp
       Objective
            Transactions: Food Stamp Purchase, Food Stamp Return and Food Stamp Balance Inquiry
       Test Card
            Card #4 MSD only Visa
       Procedure
            5.Food Stamp Purchase (EBTFSPurchase):
                c.Initiate an EBT sale transaction and swipe Test Card #4
                d.Select EBT Food Stamp if prompted.
                e.Enter $101.01 as the amount
            6.Food Stamp Return (EBTFSReturn):
                b.Intitiate an EBT return and manually enter Test Card #4
                c.Select EBT Food Stamp if prompted
                d.Enter $104.01 as the amount
            7.Food Stamp Balance Inquiry (EBTBalanceInquiry):
                c.Initiate an EBT blance inquiry transaction and swipe Test Card #4 Settleall transactions.
       Pass Criteria
            5.Food Stamp Purchase (EBTFSPurchase)
            6.Food Stamp Return (EBTFSReturn)
            7.Food Stamp Balance Inq (EBTBalanceInquiry)
       References
            -  PAX POSLink (Windows) API Guide, Section 4.5: Class PaymentRequest
            -  PAX Interface Between ECR PC and Terminal, Section 5.2.3: Do EBT
     */
    
    public function testCase13()
    {
        $response = $this->device->ebtPurchase(101.01)
        ->execute();
        
        $this->printReceipt($response, 'testCase13 ebtPurchase');
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
        
        $response = $this->device->ebtRefund(104.01)
        ->withCurrency(CurrencyType::FOODSTAMPS)
        ->execute();
        
        $this->printReceipt($response, 'testCase13 ebtRefund');
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
        
        $response = $this->device->ebtBalance()
        ->withCurrency(CurrencyType::FOODSTAMPS)
        ->execute();
        
        $this->printReceipt($response, 'testCase13 ebtBalance');
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
    }
    
    
    /*
     * CONDITIONAL TEST CASE 14 EBT Cash Benefits
       Objective
            Transactions: EBT Cash Benefits with Cash Back, EBT Cash Benefits Balance Inquiry and EBT Cash 
            Benefits Withdraw
       Test Card
            Card #4 MSD only Visa
       Procedure
            EBT Cash Benefits w Cash Back (EBTCashBackPurchase):
                a.Initiate an EBT sale transaction and swipe Test Card #4
                b.Select EBT Cash Benefits if prompted
                c.Enter $101.01 as the amount
                d.Enter $5.00 as the cash back amount
                e.The settlement amount is $106.01
            2.EBT Cash Benefits Balance Inquiry (EBTBalanceInquiry):
                a.Initiate an EBT cash benefit balance inquiry transaction and swipe Test Card #4
            3.EBT Cash Benefits Withdraw (EBTCashBenefitWithdrawal):
                a.Initiate an EBT cash benefits withdraw transaction and manually enter Test Card #4.
                b.Select EBT Cash Benefits Withdraw if prompted
                c.Enter $111.01 as the amount.
                d.The settlement amount is $111.01
                e.Settle all transactions.
            Pass Criteria
                1.Cash Back (EBTCashBackPurchase)
                2.Balance Inquiry (EBTBalanceInquiry)
                3.Withdraw (EBTCashBenefitWithdrawal)
            Host Reference Number:
                References
                -  PAX POSLink (Windows) API Guide, Section 4.5: Class PaymentRequest
                -  PAX Interface Between ECR PC and Terminal, Section 5.2.3: Do EBT
     * 
     */
    
    public function testCase14()
    {
        $response = $this->device->ebtPurchase(101.01)
        ->execute();
        
        $this->printReceipt($response, 'testCase14 ebtPurchase');
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
        
        $response = $this->device->ebtBalance()
        ->withCurrency(CurrencyType::CASH_BENEFITS)
        ->execute();
        
        $this->printReceipt($response, 'testCase14 ebtBalance');
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
        
        $response = $this->device->ebtWithdrawl(111.01)
        ->withCurrency(CurrencyType::CASH_BENEFITS)
        ->execute();
        
        $this->printReceipt($response, 'testCase14 ebtWithdrawl');
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
    }
    
    
    /*
     * CONDITIONAL TEST CASE 15 Level II Corporate Card
       Objective
            Process the 3 types of Corporate Card transactions: No Tax, Tax Amount, and Tax Exempt; including 
            the passing of PO Number.
       Test Card
            Card #4 Magnetic stripe Visa
       Procedure
            1.Select sale function for the amount of $112.34.
                a.Swipe Test Card #4
                b.Receive CPC Indicator of B
                c.Continue with CPCEdit transaction to account for Tax Type of Not Used.
                d.Enter the PO Number of 98765432101234567 on the device.
            2.Select sale function for the amount of $123.45.
                a.Swipe Test Card #4
                b.Receive CPC Indicator of R
                c.Continue with CPCEdit transaction to account for Tax Type of Tax Amount for$1.00.
            3.Select sale function for the amount of $134.56.
                a.Swipe test Card #4
                b.Receive CPC Indicator of S.
                c.Continue with CPCEdit transaction to account for Tax Type of Tax Exempt.
                d.Enter the PO Number of 98765432101234567 on device.
       Pass Criteria
            1.Transactions will approve online.
            References
            -  PAX POSLink (Windows) API Guide, Section 4.5: Class PaymentRequest
            -  PAX Interface Between ECR PC and Terminal, Section 5.2.1: Do Credit
            -  PAX Interface Between ECR PC and Terminal, Section 5.5.7: Request CommercialInformation
    */
            
    public function testCase15()
    {
        $response = $this->device->creditSale(112.34)
        ->withTaxType(TaxType::NOT_USED)
        ->withPoNumber("98765432101234567")
        ->execute();
        
        $this->printReceipt($response, 'testCase15 No Tax');
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
        
        $response = $this->device->creditSale(123.45)
        ->withTaxAmount(1)
        ->withCustomerCode("987654321")
        ->withTaxType(TaxType::SALES_TAX)
        ->execute();
        
        $this->printReceipt($response, 'testCase15 withTax');
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
        
        $response = $this->device->creditSale(134.5)
        ->withTaxType(TaxType::TAX_EXEMPT)
        ->withPoNumber("98765432101234567")
        ->execute();
        
        $this->printReceipt($response, 'testCase15 TAX_EXEMPT');
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
    }
    
       /*
       CONDITIONAL TEST CASE 17 Approved Store and Forward sale and Upload
       Objective
            Process credit sale in Store and Forward, upload transaction, and close batch.
            In order to access parameters and functions related to SAF, 
            Allow Store&Forward needs to be enabled on BroadPOS TMS. 
            SETSAFPARAMETERS commands are available as well.
       Test Card
            Card #3 EMV Visa w/ Signature CVM
       Procedure
            1.Select Sale function for an amount of $4.00. Response Approved.
            2.Send SAFUPLOAD command
                a.SAF Indicator = 2
                b.Result OK
            3.Initiate a Batch Close
       Pass Criteria
            Transaction approve in SAF and settles in a batch.            
       References
            -  PAX POSLink (Windows) API Guide, Section 4.6: Class ManageRequest
            -  PAX Interface Between ECR PC and Terminal, Section 5.1.28: Set SAF Parameters
            -  PETE Store and Forward QRG

*/
    
    public function testCase17()
    {
        //set to saf mode
        $response = $this->device->setSafMode(SafMode::STAY_OFFLINE);
        
        $this->assertNotNull($response);
        $this->assertEquals('OK', $response->deviceResponseText);
        
        //credit sale in offline mode
        $response = $this->device->creditSale(4.00)
        ->withAllowDuplicates(1)
        ->execute();
        
        $this->printReceipt($response, 'testCase17 SAF creditSale');
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        $this->assertEquals('APPROVED OFFLINE', $response->deviceResponseText);
        
        //SAF upload
        $response = $this->device->safUpload(SafUpload::ALL_TRANSACTION);
        
        $this->printReceipt($response, 'testCase17 safUpload');
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
        
        //batch Close
        $response = $this->device->batchClose();
        
        $this->printReceipt($response, 'testCase17 batchClose');
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);

        //reset to online mode
        $response = $this->device->setSafMode(SafMode::STAY_ONLINE);
        
        $this->assertNotNull($response);
        $this->assertEquals('OK', $response->deviceResponseText);
    }
    
    /*
        CONDITIONAL TEST CASE 18 Declined Store and Forward transaction and Delete
            (Mandatory if Conditional Test Case 17 is ran)
        Objective
            Process credit sale in Store and Forward, upload transaction, and delete declined transaction from terminal.
            In order to access parameters and functions related to SAF, Allow Store&Forward needs to be enabled 
            on BroadPOS TMS. SETSAFPARAMETERS commands are available as well.
        Test Card
            Card #3 EMV Visa w/ Signature CVM
        Procedure
            1.Select Sale function for an amount of $10.25. Response Approved.
            2.Send SAFUPLOAD command
                a.SAF Indicator = 2
                b.Transaction will decline
            3.Perform DELETESAFFILE
                a.SAF Indicator = 2
        Pass Criteria
            Transaction approved in SAF and declines when uploaded. Delete record from terminal.
        References
            -  PAX POSLink (Windows) API Guide, Section 4.6: Class ManageRequest
            -  PAX Interface Between ECR PC and Terminal, Section 5.1.28: Set SAF Parameters
            -  PETE Store and Forward QRG
*/
    
    public function testCase18()
    {
        
        //set to saf mode
        $response = $this->device->setSafMode(SafMode::STAY_OFFLINE);
        
        $this->assertNotNull($response);
        $this->assertEquals('OK', $response->deviceResponseText);
        
        //credit auth in offline mode
        $response = $this->device->creditAuth(10.25)
        ->withAllowDuplicates(1)
        ->execute();
        
        $this->printReceipt($response, 'testCase18 SAF creditSale');
        
        $this->assertNotNull($response);
        $this->assertEquals('00', $response->deviceResponseCode);
        
        //SAF upload
        $response = $this->device->safUpload(SafUpload::ALL_TRANSACTION);
        
        $this->printReceipt($response, 'testCase18 safUpload');
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
        
        //SAF Delete
        $response = $this->device->safDelete(SafDelete::FAILED_TRANSACTION);
        
        $this->printReceipt($response, 'testCase18 safDelete');
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
        
        //reset to online mode
        $response = $this->device->setSafMode(SafMode::STAY_ONLINE);
        
        $this->assertNotNull($response);
        $this->assertEquals('OK', $response->deviceResponseText);
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
     References
        -  PAX POSLink (Windows) API Guide, Section 4.5: Class BatchRequest
        -  PAX Interface Between ECR PC and Terminal, Section 5.3.1: Batch Close
     * 
     */
    
    public function testCase19()
    {
        $response = $this->device->batchClose();
        
        $this->printReceipt($response, 'testCase19 batchClose');
        
        $this->assertNotNull($response);
        $this->assertEquals("00", $response->deviceResponseCode);
    }
}
