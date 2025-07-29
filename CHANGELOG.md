<a href="https://github.com/globalpayments" target="_blank">
    <img src="https://developer.globalpay.com/static/media/logo.db1c4126172e20a5c31cf9d5150cc88a.svg" alt="Global Payments logo" title="Global Payments" align="right" width="225" />
</a>

# Changelog
## Latest Version v13.3.3 (07/29/25)
### Enhancements:
- Security enhancements for some terminal loggers
- [UPA] - Correct reference mapping

## v13.3.2 (07/24/25)
### Bug Fixes:
- Fix Prod issue Gateway is deprecated.

## v13.3.1 (07/14/25)
### Bug Fixes:
- Fix macroscope reported Privacy Violation issue

## v13.3.0 (07/10/25)
### Enhancements:
- [GPAPI] - Added SDK support for PayU as a payment APM.

## v13.2.2 (06/26/25)
### Enhancements:
- [GPAPI] - Added SDK support for Blik as a payment APM.

## v13.2.1 (06/10/25)
### Bug Fixes:
- [GP-API] HotFix for Installment class naming convention issue
### Enhancements:
- [Portico] - Apply improvements in PorticoReportingTests suite

## v13.2.0 (06/05/25)
### New Feature:
- [GPAPI] - Mexico GP API Create Installment

## v13.1.2 (06/03/25)
### Enchancements:
- [Portico] Added AmountIndicator element
### New Feature:
- [GPApi] - Added Installment Object in Create Sale API (POST /transactions)
- [GPApi] - Added Installment Object in Reporting API (GET /transactions & GET /transactions/{id})

## v13.1.1 (05/29/25)
### Bug Fixes:
- [TSYS/Merchantware] Correction to partial-auth response handling

## v13.1.0 (03/21/25)
### New Feature:
- [Billpay] Implemented Billpay functionality

## v13.0.8 (02/08/25)
### Enhancements:
- [Portico] Added Portico client txn id response

## v13.0.7 (12/03/24)
### Enhancements:
- Add console logger feature
- [GP-API/GP-ECOM] Unit tests enhancements

## v13.0.6 (11/19/24)
### Enhancements:
- [GP-API] - Add new mapping fields on get transaction list: "funding", "authentication"

## v13.0.5 (11/14/24)
### Enhancements:
- [GP-API] - Add new mapping fields on digital wallet transaction response: masked_number_last4, brand, brand_reference
- [MITC UPA] - Add new commands:  getAppInfo, getParam, setTimeZone,clearDataLake, reset, returnToIdle, getDeviceConfig, 
              print, scan, getDebugInfo, setDebugLevel, getDebugLevel, getSignatureFile, communicationCheck, logon, 
              findBatches, getBatchDetails, getBatchReport, displayMessage

## v13.0.4 (11/07/24)
### Enhancements:
- [Portico] Added 'GatewayTxnId' value to GatewayException message when available

## v13.0.3 (10/15/24)
### Enhancements:
- [Portico] Added support for 'CreditIncrementalAuth' transaction type

## v13.0.2 (10/03/24)
### Enhancements:
- [Portico] Added support for 'CardHolderPhone' element
- [GP-API] Update 3DS Object fields in transaction endpoint ("server_trans_ref" and "ds_trans_ref")
- [GP-API] Cleanup and refacto on the GpApiConnector.

## v13.0.1 (09/19/24)
### Enhancements:
- [GP-API] Send "cvv" in create transaction request with a tokenized card

## v13.0.0 (08/22/24)
### Enhancements:
- [UPA] Add new UPA commands

## v12.0.9 (08/14/24)
### Enhancements:
- [PAX] Portico - Added support for HSA/FSA
- [MEET-IN-THE-CLOUD][UPA] -  Add new mapping response fields for "/devices" endpoint

##  v12.0.8 (07/23/24)
### Bug Fixes:
- [GP-API] Fix re-sign in after token expiration

## v12.0.7 (07/16/24)
### Enhancements:
- [GP-API] Adds avs data to "/transaction" request for digital wallet
- [GP-API] Adds brand reference and stage time to the DisputeSummary

### Bug Fixes:
- [PAX] Correction to tip/gratuity handling in the request to device

## v12.0.6 (06/18/24)
### Enhancements:
- [GP-ECOM] Add Multi-Capture

## v12.0.5 (06/07/24)
### Bug Fixes:
- [PAX] Corrected "partial auth" response handling
- [GP-ECOM] Add HPP additional field "HPP_REMOVE_SHIPPING"
- [GP-API] Unit tests enhancements

## v12.0.4 (05/30/24)
### Enhancements:
- [GP-ECOM] Added additional fee to a card transaction (surchargeamount).
- [GP-API] Add mapping for "message_received " and "message_sent " on get a Single Action response
- [GP-API] Add "Payers" feature

## v12.0.3 (05/23/24)
### Enhancements:
- [GP-API] Add "payer->email" property on 3DS "/initiate" request
- [GP-API] Improvements on access token request
- [GP-API] Unit tests enhancements

## v12.0.2 (05/09/24)
### Enhancements:
- [Portico] Added support for 'TokenParameters' element
- [Portico] Added support for 'CategoryInd' element
- [Portico] Added support for 'DebitReversal' by transactionId using 'fromId' method. 
- [Pax Devices] Added ability to send 'CardBrandTransactionId' element

## v12.0.1 (04/16/24)
### Enhancements:
- [GP-API] Unit tests enhancements

#### Bug Fixes:
- [GP-API] Fix mapping for "authCode"
- [GP-API] Fix merchant_id in the request on "/device" endpoint for partner mode 

## v12.0.0 (03/14/24)
### Enhancements:
- [UPA] Change "ecrId" type from int to string

## v11.0.9 (02/27/24)
### Enhancements:
- [GP-API] End-to-end 3DS example update

## v11.0.8 (02/15/24)
### Enhancements:
- [GP-API] Unit tests enhancements
- [PayPlan] Add AccountNumberLast4 in recurring payment method response 

## v11.0.7 (01/22/24)
### Enhancements:
- [GP-ECOM] Update parseResponse for HostedService

#### Bug Fixes:
- [MEET-IN-THE-CLOUD][UPA] - Fix endOfDay

## v11.0.6 (01/16/24)
### Enhancements:
- [GP-API] Update QR code payment example for WeChat

#### Bug Fixes:
[GP-ECOM] Fix parseResponse on HostedService when TIMESTAMP is not returned from API

## v11.0.5 (01/09/24)
#### Bug Fixes:
- [Portico] Fixed null CustomerData exception
- [A35_PAX] Fixed long transaction processing times

## v11.0.4 (12/07/23)
### Enhancements:
- [GP-API] Add QR code payment example with Alipay
- [GP-API] File Processing
- Security vulnerability fixes

## v11.0.3 (11/07/23)
### Enhancements:
- [Terminals/Devices] Pulled LogManagement.php into src as TerminalLogManagement.php 

## v11.0.2 (11/01/23)
### Enhancements:
- [PAX Devices] Improvements to decline-response handling
- [DiamondCloud] Add production endpoints
- [MEET-IN-THE-CLOUD][UPA] Remove duplicate ConnectionMode const "MIC"

## v11.0.1 (10/24/23)
### Enhancements:
- [GP-API] Add stored credentials to verify request
- [GP-API & GP-ECOM] Enhancements on unit tests
- [MEET-IN-THE-CLOUD][UPA] Remove QA endpoint

#### Bug Fixes:
-[GP-API]: Fix 3DS example

## v11.0.0 (10/19/23)
### Enhancements:
- [DiamondCloud] Add support for Diamond Cloud provider payment terminals.
- [Breaking] Terminals: Architecture update

## v10.1.2 (10/17/23)
### Enhancements:
- [Portico] Added cardholder email support. 

## v10.1.1 (10/10/23)
#### Enhancements:
- [GP-API] Add a new alternative payment method, ALIPAY
- [GP-ECOM] Limit what card types to accept for payment or storage (HPP & API)
    * https://developer.globalpay.com/hpp/card-blocking
    * https://developer.globalpay.com/api/card-blocking

## v10.1.0 (09/21/23)
#### Enhancements:
- [Verifone] P400: added initial Meet-In-The-Cloud connectivity support for this device
- [GP-API]: Upload Merchant Documentation - https://developer.globalpay.com/api/merchants
- [GP-API]: Credit Or Debit a Funds Management Account (FMA) - https://developer.globalpay.com/api/funds
 
## v10.0.3 (09/13/23)
#### Enhancements:
- [GP-API]: Update onboarding merchant requests
- Security vulnerabilities fixes

## v10.0.2 (09/07/23)
#### Enhancements:
- [GP-ECOM] Support parseResponse for status_url on HostedService (HPP APMs)
- [GP-ECOM] Added "custnum" from Customer on "payer_new" request

## v10.0.1 (08/29/23)
#### Enhancements:
- Enhance logs based on environment (GP-API & GP-ECOM)
- Security vulnerabilities fixes
- [GP-API] Add missing properties to authentication->three_ds (message_version, eci,server_trans_reference, 
ds_trans_reference,value)
- Unit test updates  

## v10.0.0 (08/22/23)
#### Enhancements:
- [GP-API] Rename PayLink to PayByLink

## v9.0.2 (08/10/23)
#### Enhancements:
- [GP-API] Improve Open Banking and 3DS tests

#### Bug Fixes:
-[PAX Devices]: Fix PAX controller

## v9.0.1 (07/11/23)
#### Enhancements:
- Add appsec.properties for Macroscope

## v9.0.0 (07/06/23)
#### Enhancements:
- Add support for PHP8.2
- Drop support for PHP lower than 8.0
- [UPA MiC]: Add MiC connector for UPA via GP-API
- [GP-ECOM]: Add refund for transaction with open banking

#### Bug Fixes:
- [GP-ECOM]: Send the correct "message_version" in the initiate step on 3DS2

## v8.0.2 (06/27/23)
#### Enhancements:
- [Profac]: Additional transaction support added | Account Management | Spilt Fund | Network Transaction
- [PAX Devices]: Improved some tests

#### Bug Fixes:
- [GP-ECOM]: Fix type confusion vulnerability on sha1hash for hppResponse

## v8.0.1 (05/30/23)
#### Bug Fixes:
- Portico/Heartland: fix 'AllowDup' flag not included with some CreditReturn transactions

## v8.0.0 (05/23/23)
#### Enhancements:
- Propay: Change file encoding for: AccountPermissions, BeneficialOwnerData, BusinessData, OwnersData, SignificantOwnerData, UploadDocumentData
- [Breaking] Terminals (HPA, PAX, UPA): Architecture update
- GP-API: Unit tests updates on: GpApiMerchantAccountsTest, GpApiDigitalWalletTest, GpApiMerchantsOnboardTest

## v7.0.3 (05/09/23)
#### Enhancements:
- GP-API: Manage fund transfers, splits and reverse splits in your partner network. 
    - https://developer.globalpay.com/api/transfers
    - https://developer.globalpay.com/api/transactions#/Split%20a%20Transaction%20Amount/splitTransaction
- Updates on unit tests for: PayLink, 3DS1 and RiskAssessment

#### Bug Fixes:
- Portico/Heartland: fix to allow CreditAuth transaction type with wallet data

## v7.0.2 (05/02/23)
#### Enhancements:
- GP-API: Manage merchant accounts for partner solution
    - https://developer.globalpay.com/api/accounts
- GP-ECOM: Add to the mapping response fields: acs_reference_number & acs_signed_content for the authentication source MOBILE_SDK

## v7.0.1 (04/04/23)
#### Enhancements:
- Portico/Heartland: improvements to transaction request building logic
- GP-API: Unit tests update on fraud management and APMs
- GP-ECOM: Unit test update on 3DS


## v7.0.0 (03/21/23)
#### Enhancements:
- Add payer information on Transaction object
- GP-API: Update request for boarding merchants
- GP-ECOM: Change recurring entity return type

## v6.1.7 (03/09/23)
#### Bug Fixes:
- GP-API: fix issue on PHP8.1 with strtoupper and str_pad
- GP-API: fix unit tests on PHP8.1

## v6.1.6 (03/07/23)
#### Bug Fixes:
- Portico/Heartland: fix 'withStartDate()' reporting method

#### Enhancements:
- Portico/Heartland: improvements to GooglePay and ApplePay token handling
- Portico/Heartland: simple GooglePay example added

## v6.1.5 (02/28/23)
#### Bug Fixes:
- TSYS/Merchantware: correction to service endpoints

## v6.1.4 (02/21/23)
#### Enhancements:
- GP-API: Add account_id on all requests

#### Bug Fixes:
- PAX A35: Fixed device-response handling

## v6.1.3 (02/16/23)
#### Enhancements:
- GP-API: Create open banking transactions
- GPI Transactions: Reporting tests refactor

## v6.1.2 (02/13/23)
#### Enhancements:
- GP-API: BNPL unit tests update

## v6.1.1 (02/09/23)
#### Bug Fixes:
- Portico Gateway: fix DigitalPaymentToken handling

## v6.1.0 (02/02/23)
#### Enhancements:
- GP-API: add risk assessment feature
- Refacto the Secure3DBuilder
- GP-API: update CTP transaction request

## v6.0.6 (01/26/23)
#### Enhancements:
- GPI Transactions : added support for credit, ach & reporting transactions 
- GP-API: add to generateXGPSignature to GenerationUtils
- Portico Gateway: Fix incorrect date handling in schedule response

## v6.0.5 (01/12/23)
#### Enhancements:
- GP-API: add exemption status on "/transaction" endpoint
- Add enum classes: HostedPaymentMethods, IntervalToExpire
- Portico: added support for SDKNameVersion field

#### Bug Fixes:
- GP-API: Fix issue on mapping o transaction report

## v6.0.4 (12/13/22)
#### Enhancements:
- GP-API: Add BNPL feature
- GP-API: Click-to-Pay

## v6.0.3 (12/06/22)
#### Enhancements:
- PAX: Adding tip after the Sale
- Portico: APPLE PAY / GOOGLE PAY fix for token format

## v6.0.2 (11/17/22)
#### Enhancements:
- GP-API: Onboard merchants feature
- GP-API: Decoupled Authentication

#### Bug Fixes:
- GP-API: Fix phone country code for ISO code "DO"
- GP-API/GP-ECOM: Fix end-to-end examples
- Portico: APPLE PAY / GOOGLE PAY fix

## v6.0.1 (11/03/22)
#### Enhancements:
- Security vulnerabilities fixes

## v6.0.0 (10/20/22)
#### Enhancements:
- GP-API/GP-ECOM: Sunset 3DS1
- Add method on CountryUtil to extract phone country code based on ISO-2/ISO-3/the name of the country 
- GP-API Update unit tests with new set of credentials for GP-API
- Genius - Fix Configuration for service URL

## v5.0.3 (10/04/22)
#### Enhancements:
- GP-API: PayLink enhancements

## v5.0.2 (09/29/22)
#### Enhancements:
- GP-API: Add fraud management feature 
- GP-ECOM: Billing/Shipping country value should be ISO2 country code
- GP-API: Add missing request properties for /transactions and /initiate endpoints

## v5.0.1 (09/08/22)
#### Enhancements:
- GP-API: Add new mapping for card issuer avs/cvv result 
- GP-ECOM: Add srd tag to card storage request

## v5.0.0 (08/23/22)
#### Enhancements:
- Support PHP v. >= 8.0
- GP_API: Update PayLink unit tests

## v4.0.5 (07/28/22)
#### Enhancements:
- GP-API: Add PayLink service

#### Bug Fixes:
- GP-API: Fix mapping issue on APMs

## v4.0.4 (07/14/22)
#### Enhancements:
- GP-API: Add mapping for some missing fields on response 3DS2 initiate step
- GP-ECOM: Add missing optional fields HPP_CUSTOMER_PHONENUMBER_HOME and HPP_CUSTOMER_PHONENUMBER_WORK
- Update Open Banking endpoints

## v4.0.3 (06/28/22)
#### Enhancements:
- Add autoloader standalone
- Add end-to-end example for GP-API with HF and 3DS2
- Refacto on the folder structure in examples

## v4.0.2 (06/14/22)
#### Bug Fixes:
- Fix issue with recurring payment schedule edits (Portico)

## v4.0.1 (06/09/22)
#### Enhancements:
- HPP Exemption Optimization Service
- Update timestamp on the Logger

## v4.0.0 (06/07/22)
#### Enhancements:
- GP-ECOM: Add payment scheduler 
- GP-ECOM/GP-API: Structure refacto  
- Upgrade to min PHP 7.1 
- GP-API: Add example with Google Pay
- GP-API: Add Dynamic Descriptor for authorize and charge

## v3.1.1 (05/17/22)
#### Enhancements:
- GP-ECOM: Add HPP capture billing/shipping address
- Add intl and mbstring extensions on composer
- GP-API: Refacto reporting for disputes / search stored payment methods / LodgingData

## v3.1.0 (05/05/22)
#### Enhancements:

- GP-ECOM: Add bank payment (open banking) service
- GP-API: Update usage mode, cardholder name and card number on a stored payment method
- Portico: Updated code for Secure3D and WalletData Element

## v3.0.5 (04/21/22)
#### Enhancements:
- GP-API: Increment an Auth: increment the amount for an existing transaction.
- GP-API: Map multiCapture on the transaction response
- GP-API: Update unit tests
- Deprecate verifyEnrolled and verifySignature from CreditCardData

## v3.0.4 (04/12/22)
#### Enhancements:
- UPA devices: add support for batch summary report, batch detail report, and open tab details report
- UPA devices: various modifications to account for latest UPA version's changes 

## v3.0.3 (03/21/22)
#### Enhancements:
- add "MOBILE_SDK" source in the 3DS2 flow initiate step (GP-API)
- Adjust a CP Sale (GP-API)
- Search [POST] for a Payment Method (GP-API)
- Stored Payment Methods - POST Search (GP-API)
- Get a Document associated with a Dispute (GP-API)


## v3.0.2 (02/17/22)
#### Enhancements:

- use "IN_APP" entry_mode when creating a transaction with digital wallets (GP-API)
- add new unit tests for dcc and others

## v3.0.1 (01/27/22)
#### Enhancements:
- Add fingerprint feature (GP-API)
- Add Payment Link Id in the request for authorize (GP-API)
- Add new unit tests on DCC CNP (GP-API)

#### Bug Fixes:
- Fix issue for Fleet cards (GP-ECOM)
- Fix issue for Diners card type (GP-ECOM)

## v3.0.0 (12/16/21)

- Add Dynamic Currency Conversion feature for GP-API
- Show exceptions on updateTokenExpiry() & deleteToken()
- DOMDocument data encoded before serialization
- CardUtils MC regex updated

## v2.4.4 (12/07/21)

#### Enhancements:
- Added avs/cvv mapping and support for findTransaction method
- Added batch Close response to return GSAP-specific data
- Added support for split tender GiftCardSale transactions
- Fix some GP-API unit tests

## v2.4.3 (11/17/21)

#### Enhancements:
- Add Unified Payments Application support

## v2.4.2 (11/16/21)

#### Enhancements:
- Add LPMs HPP on GP-ECOM
- Add PAYPAL on GP-ECOM

## v2.4.1 (11/12/21)

#### Enhancements:
- Add reporting service to get transaction by id on GP-ECOM
- Add HPP_POST_DIMENSIONS and HPP_POST_RESPONSE to serialize on GP-ECOM

## v2.4.0 (11/04/21)

- Add PAYPAL alternative payment method on GP-API

## v2.3.15 (10/21/21)

- Added ach-transaction details test block for Portico

## v2.3.14 (09/30/21)

- Add "paybybankapp" APM (GP-ECOM)
- Add AVS missing mapping to response when creating a transaction (GP-API)
- Refacto on enum classes (GP-API)
- Update "entry_mode" functionality and add manual entry methods: MOTO, PHONE, MAIL (GP-API)
- Add merchantId on GpApiConfig for partnership active

## v2.3.13 (09/23/21)

#### Bug Fixes:

- Removed unwanted artefacts files

## v2.3.12 (09/09/21)

- Add sanitize data

## v2.3.11 (08/26/21)

- Add the amount and currency to hash generation (GP-ECOM)
- Digital wallets unencrypted and encrypted for GP-API with Google Pay and Apple Pay:
    - sale
    - linked refund
    - reverse
- GP-API ACH feature: 
     - sale
     - refund
     - linked refund
     - reauthorize
- Add recurring payment with stored credentials functionality to GP-API
- Add unit tests for multi-config on GP-API
- Add payment_method filter on report transaction 
- Add depositDate and depositReference mapping response for settlement disputes
- Support findSettlementDisputes by deposit_id, from_deposit_time_created and to_deposit_time_created
- Add optional parameters to tokenize() method
- Add amount and currency to hash generation for Apple PAY (GP-ECOM)

## v2.3.10 (08/19/21)

- Set Fraud Management Rules for GP-ECOM
- Portico tokenization example update

## v2.3.9 (08/03/21)

- Send "x-gp-sdk" in the header with the SDK programming language and release version used
- Send headers to GP-API that are dynamically set through configuration, like:
     - x-gp-platform: "prestashop;version=1.7.2"
     - x-gp-extension: "coccinet;version=2.4.1"
- Fix some GP-ECOM unit tests for APM, certifications and add Secure3dServiceTest to realex test suite
- Add support for Propay timezone and device details

## v2.3.8 (07/27/21)

#### Enhancements:
- Add new HPP example for GP-ECOM
- Add file medatada.xml

## v2.3.7 (07/20/21)

#### Enhancements:
- Replace in create transaction request authentication.three_ds with authentication.id)
- Add liability shift checks in the 3DS GP-API flow / update unit tests
- add new mappings on 3DS GP-API: authenticationSource, authenticationType, acsInfoIndicator, whitelistStatus, messageExtension

## v2.3.6 (07/13/21)

#### Enhancements:
- Send the numeric version in the three_ds.message_version in the create transaction request
- Map the ACS challenge redirect URL only if the status is "CHALLENGE_REQUIRED"

## v2.3.5 (07/08/21)

#### Enhancements:
- Add "Netherlands Antilles" to our mapping for country codes
- Strip all non-numeric characters for phone number and phone country code on 3DS2 flow GP-ECOM

## v2.3.4 (06/15/21)

#### Enhancements:
 
- Add RequestLogger to GP-ECOM
- Fix message_extension issue for 3DS2 on GP-ECOM
- Update logo image on Readme and Changelog files
- Add depositDate and depositReference mappings for settlement disputes report on GP-API
- Change property name from "storage_model" to "storage_mode" on GP-API


## v2.3.3 (05/27/21)

#### Enhancements:

- enhance GP-ECOM error handling
- update GP-ECOM unit test for APPLE PAY and GOOGLE PAY


## v2.2.16 (05/20/21)

#### Enhancements:

Add GP-ECOM dynamic descriptor functionality


## v2.2.15 (05/18/21)

#### Bug Fixes:

- GP-ECOM fix 3DS recurring data fields: recurring expiry date format and max_number_of_instalments

## v2.2.14 (05/11/21)

#### Enhancements:

- Add portico connector - sanitize Data
- Update GP-API to 2021-03-22 version
- 3DS Status Mapping - Missed Mapping and Revise some mappings
- Update ACS simulator for 3DS2 to use values from initiate response for the form fields name required in the POST redirect
- Change position of fields: "source", "preference", "message_version" need to exist in the "three_ds" sub-object in the 3DS2 initiate call
- Remove "/detokenize" endpoint from GP-API
- Update GP-API production endpoint

## v2.2.13 (04/29/21)

#### Enhancements:

- Set global merchant country configuration where required for GP-API
- Add GP-API 3DS new tests
- Add additional GP-API 3DS mappings
- Add additional GP-API transaction summary mappings
- Add GP-API close batch functionality
- Add GP-API stored payment methods report
- Add GP-API actions report
- Add GP-API reauthorization functionality
- Add GP-API EBT new tests
- Add Exemption Optimization service for GP-ECOM

#### Bug Fixes:

- None

---
