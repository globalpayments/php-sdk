<a href="https://github.com/globalpayments" target="_blank">
    <img src="https://developer.globalpay.com/static/media/logo.dab7811d.svg" alt="Global Payments logo" title="Global Payments" align="right" width="225" />
</a>

# Changelog


## Latest version

#### Enhancements:

- use "IN_APP" entry_mode when creating a transaction with digital wallets (GP-API)
- add new unit tests for dcc and others

## v3.0.1 (01/27/2022)

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
