<a href="https://github.com/globalpayments" target="_blank">
    <img src="https://developer.globalpay.com/static/media/logo.dab7811d.svg" alt="Global Payments logo" title="Global Payments" align="right" width="225" />
</a>

# Changelog

## Latest version
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
- Map the ACS challenge redirect url only if the status is "CHALLENGE_REQUIRED"

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

Add GP-ECOM dynamic descriptor functionaSlity

## v2.2.15 (05/18/2021)

#### Bug Fixes:

- GP-ECOM fix 3DS recurring data fields: recurring expiry date format and max_number_of_instalments

## v2.2.14 (05/11/2021)

#### Enhancements:

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
