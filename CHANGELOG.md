<a href="https://github.com/globalpayments" target="_blank">
    <img src="https://developer.globalpay.com/static/media/logo.dab7811d.svg" alt="Global Payments logo" title="Global Payments" align="right" width="225" />
</a>

# Changelog

## Latest Version
#### Enhancements:
- Add payer information on Transaction object
- GP-API: Update request for boarding merchants
- GP-ECOM: Change recurring entity return type

## v6.1.7 (03/09/2023)
#### Bug Fixes:
- GP-API: fix issue on PHP8.1 with strtoupper and str_pad
- GP-API: fix unit tests on PHP8.1

## v6.1.6 (03/07/2023)
#### Bug Fixes:
- Portico/Heartland: fix 'withStartDate()' reporting method

#### Enhancements:
- Portico/Heartland: improvements to GooglePay and ApplePay token handling
- Portico/Heartland: simple GooglePay example added

## v6.1.5 (02/28/2023)
#### Bug Fixes:
- TSYS/Merchantware: correction to service endpoints

## v6.1.4 (02/21/2023)
#### Enhancements:
- GP-API: Add account_id on all requests

#### Bug Fixes:
- PAX A35: Fixed device-response handling

## v6.1.3 (02/16/2023)
#### Enhancements:
- GP-API: Create open banking transactions
- GPI Transactions: Reporting tests refactor

## v6.1.2 (02/13/2023)
#### Enhancements:
- GP-API: BNPL unit tests update

## v6.1.1 (02/09/2023)
#### Bug Fixes:
- Portico Gateway: fix DigitalPaymentToken handling

## v6.1.0 (02/02/2023)
#### Enhancements:
- GP-API: add risk assessment feature
- Refacto the Secure3DBuilder
- GP-API: update CTP transaction request

## v6.0.6 (01/26/2023)
#### Enhancements:
- GPI Transactions : added support for credit, ach & reporting transactions 
- GP-API: add to generateXGPSignature to GenerationUtils
- Portico Gateway: Fix incorrect date handling in schedule response

## v6.0.5 (01/12/2023)
#### Enhancements:
- GP-API: add exemption status on "/transaction" endpoint
- Add enum classes: HostedPaymentMethods, IntervalToExpire
- Portico: added support for SDKNameVersion field

#### Bug Fixes:
- GP-API: Fix issue on mapping o transaction report

## v6.0.4 (12/13/2022)
#### Enhancements:
- GP-API: Add BNPL feature
- GP-API: Click-to-Pay

## v6.0.3 (12/06/2022)
#### Enhancements:
- PAX: Adding tip after the Sale
- Portico: APPLE PAY / GOOGLE PAY fix for token format

## v6.0.2 (11/17/2022)
#### Enhancements:
- GP-API: Onboard merchants feature
- GP-API: Decoupled Authentication

#### Bug Fixes:
- GP-API: Fix phone country code for ISO code "DO"
- GP-API/GP-ECOM: Fix end-to-end examples
- Portico: APPLE PAY / GOOGLE PAY fix

## v6.0.1 (11/03/2022)
#### Enhancements:
- Security vulnerabilities fixes

## v6.0.0 (10/20/2022)
#### Enhancements:
- GP-API/GP-ECOM: Sunset 3DS1
- Add method on CountryUtil to extract phone country code based on ISO-2/ISO-3/the name of the country 
- GP-API Update unit tests with new set of credentials for GP-API
- Genius - Fix Configuration for service URL

## v5.0.3 (10/04/2022)
#### Enhancements:
- GP-API: PayLink enhancements

## v5.0.2 (09/29/2022)
#### Enhancements:
- GP-API: Add fraud management feature 
- GP-ECOM: Billing/Shipping country value should be ISO2 country code
- GP-API: Add missing request properties for /transactions and /initiate endpoints

## v5.0.1 (09/08/2022)
#### Enhancements:
- GP-API: Add new mapping for card issuer avs/cvv result 
- GP-ECOM: Add srd tag to card storage request

## v5.0.0 (08/23/2022)
#### Enhancements:
- Support PHP v. >= 8.0
- GP_API: Update PayLink unit tests

## v4.0.5 (07/28/2022)
#### Enhancements:
- GP-API: Add PayLink service

#### Bug Fixes:
- GP-API: Fix mapping issue on APMs

## v4.0.4 (07/14/2022)
#### Enhancements:
- GP-API: Add mapping for some missing fields on response 3DS2 initiate step
- GP-ECOM: Add missing optional fields HPP_CUSTOMER_PHONENUMBER_HOME and HPP_CUSTOMER_PHONENUMBER_WORK
- Update Open Banking endpoints

## v4.0.3 (06/28/2022)
#### Enhancements:
- Add autoloader standalone
- Add end-to-end example for GP-API with HF and 3DS2
- Refacto on the folder structure in examples

## v4.0.2 (06/14/2022)
#### Bug Fixes:
- Fix issue with recurring payment schedule edits (Portico)

## v4.0.1 (06/09/2022)
#### Enhancements:
- HPP Exemption Optimization Service
- Update timestamp on the Logger

## v4.0.0 (06/07/2022)
#### Enhancements:
- GP-ECOM: Add payment scheduler 
- GP-ECOM/GP-API: Structure refacto  
- Upgrade to min PHP 7.1 
- GP-API: Add example with Google Pay
- GP-API: Add Dynamic Descriptor for authorize and charge

## v3.1.1 (05/17/2022)
#### Enhancements:
- GP-ECOM: Add HPP capture billing/shipping address
- Add intl and mbstring extensions on composer
- GP-API: Refacto reporting for disputes / search stored payment methods / LodgingData

## v3.1.0 (05/05/2022)
#### Enhancements:

- GP-ECOM: Add bank payment (open banking) service
- GP-API: Update usage mode, cardholder name and card number on a stored payment method
- Portico: Updated code for Secure3D and WalletData Element

## v3.0.4 (04/21/2022)
#### Enhancements:
- GP-API: Increment an Auth: increment the amount for an existing transaction.
- GP-API: Map multiCapture on the transaction response
- GP-API: Update unit tests
- Deprecate verifyEnrolled and verifySignature from CreditCardData

## v3.0.4 (04/12/2022)
#### Enhancements:
- UPA devices: add support for batch summary report, batch detail report, and open tab details report
- UPA devices: various modifications to account for latest UPA version's changes 

## v3.0.3 (03/21/2022)
#### Enhancements:
- add "MOBILE_SDK" source in the 3DS2 flow initiate step (GP-API)
- Adjust a CP Sale (GP-API)
- Search [POST] for a Payment Method (GP-API)
- Stored Payment Methods - POST Search (GP-API)
- Get a Document associated with a Dispute (GP-API)


## v3.0.2 (02/17/2022)
#### Enhancements:

- use "IN_APP" entry_mode when creating a transaction with digital wallets (GP-API)
- add new unit tests for dcc and others

## v3.0.1 (01/27/2022)
#### Enhancements:
- Add fingerprint feature (GP-API)
- Add Payment Link Id in the request for authorize (GP-API)
- Add new unit tests on DCC CNP (GP-API)

#### Bug Fixes:
- Fix issue for Fleet cards (GP-ECOM)
- Fix issue for Diners card type (GP-ECOM)

## v3.0.0 (12/16/2021)

- Add Dynamic Currency Conversion feature for GP-API
- Show exceptions on updateTokenExpiry() & deleteToken()
- DOMDocument data encoded before serialization
- CardUtils MC regex updated

## v2.4.4 (12/07/2021)

#### Enhancements:
- Added avs/cvv mapping and support for findTransaction method
- Added batch Close response to return GSAP-specific data
- Added support for split tender GiftCardSale transactions
- Fix some GP-API unit tests

## v2.4.3 (11/17/2021)

#### Enhancements:
- Add Unified Payments Application support

## v2.4.2 (11/16/2021)

#### Enhancements:
- Add LPMs HPP on GP-ECOM
- Add PAYPAL on GP-ECOM

## v2.4.1 (11/12/2021)

#### Enhancements:
- Add reporting service to get transaction by id on GP-ECOM
- Add HPP_POST_DIMENSIONS and HPP_POST_RESPONSE to serialize on GP-ECOM

## v2.4.0 (11/04/2021)

- Add PAYPAL alternative payment method on GP-API

## v2.3.15 (10/21/2021)

- Added ach-transaction details test block for Portico

## v2.3.14 (09/30/2021)

- Add "paybybankapp" APM (GP-ECOM)
- Add AVS missing mapping to response when creating a transaction (GP-API)
- Refacto on enum classes (GP-API)
- Update "entry_mode" functionality and add manual entry methods: MOTO, PHONE, MAIL (GP-API)
- Add merchantId on GpApiConfig for partnership active

## v2.3.13 (09/23/2021)

#### Bug Fixes:

- Removed unwanted artefacts files

## v2.3.12 (09/09/2021)

- Add sanitize data

## v2.3.11 (08/26/2021)

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

## v2.3.10 (08/19/2021)

- Set Fraud Management Rules for GP-ECOM
- Portico tokenization example update

## v2.3.9 (08/03/2021)

- Send "x-gp-sdk" in the header with the SDK programming language and release version used
- Send headers to GP-API that are dynamically set through configuration, like:
     - x-gp-platform: "prestashop;version=1.7.2"
     - x-gp-extension: "coccinet;version=2.4.1"
- Fix some GP-ECOM unit tests for APM, certifications and add Secure3dServiceTest to realex test suite
- Add support for Propay timezone and device details

## v2.3.8 (07/27/2021)

#### Enhancements:
- Add new HPP example for GP-ECOM
- Add file medatada.xml

## v2.3.7 (07/20/2021)

#### Enhancements:
- Replace in create transaction request authentication.three_ds with authentication.id)
- Add liability shift checks in the 3DS GP-API flow / update unit tests
- add new mappings on 3DS GP-API: authenticationSource, authenticationType, acsInfoIndicator, whitelistStatus, messageExtension

## v2.3.6 (07/13/2021)

#### Enhancements:
- Send the numeric version in the three_ds.message_version in the create transaction request
- Map the ACS challenge redirect URL only if the status is "CHALLENGE_REQUIRED"

## v2.3.5 (07/08/2021)

#### Enhancements:
- Add "Netherlands Antilles" to our mapping for country codes
- Strip all non-numeric characters for phone number and phone country code on 3DS2 flow GP-ECOM

## v2.3.4 (06/15/2021)

#### Enhancements:
 
- Add RequestLogger to GP-ECOM
- Fix message_extension issue for 3DS2 on GP-ECOM
- Update logo image on Readme and Changelog files
- Add depositDate and depositReference mappings for settlement disputes report on GP-API
- Change property name from "storage_model" to "storage_mode" on GP-API


## v2.3.3 (05/27/2021)

#### Enhancements:

- enhance GP-ECOM error handling
- update GP-ECOM unit test for APPLE PAY and GOOGLE PAY


## v2.2.16 (05/20/2021)

#### Enhancements:

Add GP-ECOM dynamic descriptor functionality


## v2.2.15 (05/18/2021)

#### Bug Fixes:

- GP-ECOM fix 3DS recurring data fields: recurring expiry date format and max_number_of_instalments

## v2.2.14 (05/11/2021)

#### Enhancements:

- Add portico connector - sanitize Data
- Update GP-API to 2021-03-22 version
- 3DS Status Mapping - Missed Mapping and Revise some mappings
- Update ACS simulator for 3DS2 to use values from initiate response for the form fields name required in the POST redirect
- Change position of fields: "source", "preference", "message_version" need to exist in the "three_ds" sub-object in the 3DS2 initiate call
- Remove "/detokenize" endpoint from GP-API
- Update GP-API production endpoint

## v2.2.13 (04/29/2021)

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
