<a href="https://github.com/globalpayments" target="_blank">
    <img src="https://globalpayments.github.io/images/globapaymentsLogo.png" alt="Global Payments logo" title="Global Payments" align="right" width="225" />
</a>

# Heartland & Global Payments PHP SDK

This SDK makes it easy to integrate your PHP application with our Card Not Present and Card Present APIs. 

## Solutions

### General / Omnichannel

* API Payment Processing
* Apple Pay & Google Pay
* Secure Card Storage & Customer Management
* Subscriptions / Recurring Billing Solutions
* Credit, Debit, Gift & Loyalty, and eCheck/ACH

### Card Not Present (Ecommerce & MOTO) Specific

* Minimize PCI compliance requirements with Hosted Payment Solutions 
* 140+ Authorization Currencies & 16 Settlement Currencies
* 150+ Local Payment Methods Worldwide
* Account Updater
* Inbuilt Fraud Prevention Rules
* 3D Secure, AVS and CVV Checks
* 260+ Global Enterprise Fraud Rules

### Card Present (Terminal & POS) Specific

* Secure End-To-End Encryption

## Requirements

- PHP 5.5.9+
- OpenSSL 1.0.1+
- PHP Curl extension
- PHP DOM extension
- PHP OpenSSL extension

## Installation

Installing the SDK into your solution is usually be done by either using Composer/Packagist, or by adding the project to your solution and referencing it directly.

To install via [Composer/Packagist](https://packagist.org/packages/globalpayments/php-sdk):

```
composer require globalpayments/php-sdk
```

To install via a direct download:

Download and unzip or, using Git, [clone the repository](https://github.com/globalpayments/php-sdk) from GitHub. See more on [how to clone repositories](https://help.github.com/articles/cloning-a-repository/).

```
git clone https://github.com/globalpayments/php-sdk
```

## Documentation and Examples

You can find the latest SDK documentation along with code examples and test cards on the [Global Payments](https://developer.realexpayments.com) and [Heartland](https://developer.heartlandpaymentsystems.com/documentation) Developer Hubs.

In addition you can find working examples in the our example code repository.

*Quick Tip*: The included [test suite](https://github.com/globalpayments/php-sdk/tree/master/test) can be a great source of code samples for using the SDK!

#### Process a Payment Example

```csharp
$card = new CreditCardData();
$card->number = "4111111111111111";
$card->expMonth = "12";
$card->expYear = "2025";
$card->cvn = "123";

try {
    $response = $card->charge(129.99)
        ->withCurrency("EUR")
        ->execute();

    $result = $response->responseCode; // 00 == Success
    $message = $response->responseMessage; // [ test system ] AUTHORISED
} catch (ApiException $e) {
    // handle errors
}
```

#### Test Card Data

Name        | Number           | Exp Month | Exp Year | CVN
----------- | ---------------- | --------- | -------- | ----
Visa        | 4263970000005262 | 12        | 2025     | 123
MasterCard  | 2223000010005780 | 12        | 2019     | 900
MasterCard  | 5425230000004415 | 12        | 2025     | 123
Discover    | 6011000000000087 | 12        | 2025     | 123
Amex        | 374101000000608  | 12        | 2025     | 1234
JCB         | 3566000000000000 | 12        | 2025     | 123
Diners Club | 36256000000725   | 12        | 2025     | 123

#### Testing Exceptions

During your integration you will want to test for specific issuer responses such as 'Card Declined'. Because our sandbox environments do not actually reach out to issuing banks for authorizations, there are specific transaction amounts and/or card numbers that will trigger gateway and issuing bank responses. Please contact your support representative for a complete listing of values used to simulate transaction AVS/CVV results, declines, errors, and other responses that can be caught in your code. Example error handling code:

```php
try {
    $response = $card->charge(129.99)
        ->withCurrency("EUR")
        ->execute();
} catch (BuilderException $e) {
    // handle builder errors
} catch (ConfigurationException $e) {
    // handle errors related to your services configuration
} catch (GatewayException $e) {
    // handle gateway errors/exceptions
} catch (UnsupportedTransactionException $e) {
    // handle errors when the configured gateway doesn't support
    // desired transaction
} catch (ApiException $e) {
    // handle all other errors
}
```

## Contributing

All our code is open sourced and we encourage fellow developers to contribute and help improve it!

1. Fork it
2. Create your feature branch (`git checkout -b my-new-feature`)
3. Ensure SDK tests are passing
4. Commit your changes (`git commit -am 'Add some feature'`)
5. Push to the branch (`git push origin my-new-feature`)
6. Create new Pull Request

## License

This project is licensed under the GNU General Public License v2.0. Please see [LICENSE.md](LICENSE.md) located at the project's root for more details.
