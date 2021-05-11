<a href="https://github.com/globalpayments" target="_blank">
    <img src="https://globalpayments.github.io/images/globapaymentsLogo.png" alt="Global Payments logo" title="Global Payments" align="right" width="225" />
</a>

# Changelog

## Latest version

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
