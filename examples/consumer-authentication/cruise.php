<?php
require_once ('../../vendor/autoload.php');
require 'JWT.php';

use GlobalPayments\Api\PaymentMethods\CreditCardData;
use GlobalPayments\Api\ServicesContainer;
use GlobalPayments\Api\Entities\EcommerceInfo;
use GlobalPayments\Api\ServiceConfigs\Gateways\PorticoConfig;

$_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
$_GET = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!empty($_GET)) {

    print '<pre><code>';
    print_r($_GET);
    print '</code></pre>';

    $config = new PorticoConfig();
    $config->secretApiKey = 'skapi_cert_MT2PAQB-9VQA5Z1mOXQbzZcH6O5PpdhjWtFhMBoL4A';

    ServicesContainer::configureService($config);

    $card = new CreditCardData();
    $card->token = $_GET['heartlandToken'];

    $secureEcommerce = new EcommerceInfo();
    $secureEcommerce->paymentDataSource = $_GET['cardType'];
    $secureEcommerce->cavv = $_GET['cavv'];
    $secureEcommerce->eci = substr($_GET['eciflag'], 1);
    $secureEcommerce->xid = $_GET['xid'];

    $response = $card->charge(15)
            ->withCurrency('USD')
            ->withEcommerceInfo($secureEcommerce)
            ->execute();

    print '<pre><code>';
    print_r($response);
    print '</code></pre>';
} else {
    $orderNumber = str_shuffle('abcdefghijklmnopqrstuvwxyz');
    //$apiIdentifier = 'Merchant-uatmerchant-Key';
    //$orgUnitId = '55ef3e43f723aa431c9969ae';
    //$apiKey = 'ac848959-f878-4f62-a0a2-4b2a648446c3';
    $apiIdentifier = '579bc985da529378f0ec7d0e';
    $orgUnitId = '5799c3c433fadd4cf427d01a';
    $apiKey = 'a32ed153-3759-4302-a314-546811590b43';

    $data = array(
        'jti' => str_shuffle('abcdefghijklmnopqrstuvwxyz'),
        'iat' => time(),
        'iss' => $apiIdentifier,
        'OrgUnitId' => $orgUnitId,
        'Payload' => array(
            'OrderDetails' => array(
                'OrderNumber' => $orderNumber,
                'Amount' => '1500',
                'CurrencyCode' => '840',
            ),
        ),
    );
    $jwt = JWT::encode($apiKey, $data);
    ?>
    <div id="button-container"></div>
    <form id="form">
        <div id="cardNumber"></div>
        <div id="cardExpiration"></div>
        <div id="cardCvv"></div>
        <div id="submit"></div>
        <input type="hidden" id="cardinalToken" name="cardinalToken">
        <input type="hidden" id="heartlandToken" name="heartlandToken">
        <input type="hidden" id="cavv" name="cavv">
        <input type="hidden" id="eciflag" name="eciflag">
        <input type="hidden" id="enrolled" name="enrolled">
        <input type="hidden" id="paresstatus" name="paresstatus">
        <input type="hidden" id="xid" name="xid">
        <input type="hidden" id="jwt" name="jwt">
        <input type="hidden" id="cardType" name="cardType">
    </form>
    <br>
    <label>
        jwt
        <input value="<?php echo $jwt; ?>">
    </label>
    <br>
    <label>
        order number
        <input value="<?php echo $orderNumber; ?>">
    </label>
    <script type="text/javascript" src="https://api2.heartlandportico.com/SecureSubmit.v1/token/2.1/securesubmit.js"></script>
    <script src="https://includestest.ccdc02.com/cardinalcruise/v1/songbird.js"></script>
    <script>

        new Heartland.HPS({
            cca: {
                jwt: '<?php print $jwt; ?>',
                orderNumber: '<?php print $orderNumber; ?>'
            },
            publicKey: 'pkapi_cert_dNpEYIISXCGDDyKJiV',
            type: 'iframe',
            fields: {
                cardNumber: {
                    target: 'cardNumber',
                    placeholder: '•••• •••• •••• ••••'
                },
                cardExpiration: {
                    target: 'cardExpiration',
                    placeholder: 'MM / YYYY'
                },
                cardCvv: {
                    target: 'cardCvv',
                    placeholder: 'CVV'
                },
                submit: {
                    target: 'submit'
                }
            },
            style: {
                'input': {
                    'background': '#fff',
                    'border': '1px solid',
                    'border-color': '#bbb3b9 #c7c1c6 #c7c1c6',
                    'box-sizing': 'border-box',
                    'font-family': 'serif',
                    'font-size': '16px',
                    'line-height': '1',
                    'margin': '0 .5em 0 0',
                    'max-width': '100%',
                    'outline': '0',
                    'padding': '0.5278em',
                    'vertical-align': 'baseline',
                    'height': '50px',
                    'width': '100% !important'
                },
                '#heartland-field': {
                    'font-family': 'sans-serif',
                    'box-sizing': 'border-box',
                    'display': 'block',
                    'height': '50px',
                    'padding': '6px 12px',
                    'font-size': '14px',
                    'line-height': '1.42857143',
                    'color': '#555',
                    'background-color': '#fff',
                    'border': '1px solid #ccc',
                    'border-radius': '0px',
                    '-webkit-box-shadow': 'inset 0 1px 1px rgba(0,0,0,.075)',
                    'box-shadow': 'inset 0 1px 1px rgba(0,0,0,.075)',
                    '-webkit-transition': 'border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s',
                    '-o-transition': 'border-color ease-in-out .15s,box-shadow ease-in-out .15s',
                    'transition': 'border-color ease-in-out .15s,box-shadow ease-in-out .15s',
                    'width': '100%'
                },
                '#heartland-field[name=submit]': {
                    'background-color': '#36b46e',
                    'font-family': 'sans-serif',
                    'text-transform': 'uppercase',
                    'color': '#ffffff',
                    'border': '0px solid transparent'
                },
                '#heartland-field[name=submit]:focus': {
                    'color': '#ffffff',
                    'background-color': '#258851',
                    'outline': 'none'
                },
                '#heartland-field[name=submit]:hover': {
                    'background-color': '#258851'
                },
                '#heartland-field-wrapper #heartland-field:focus': {
                    'border': '1px solid #3989e3',
                    'outline': 'none',
                    'box-shadow': 'none',
                    'height': '50px'
                },
                'heartland-field-wrapper #heartland-field': {
                    'height': '50px'
                },
                'input[type=submit]': {
                    'box-sizing': 'border-box',
                    'display': 'inline-block',
                    'padding': '6px 12px',
                    'margin-bottom': '0',
                    'font-size': '14px',
                    'font-weight': '400',
                    'line-height': '1.42857143',
                    'text-align': 'center',
                    'white-space': 'nowrap',
                    'vertical-align': 'middle',
                    '-ms-touch-action': 'manipulation',
                    'touch-action': 'manipulation',
                    'cursor': 'pointer',
                    '-webkit-user-select': 'none',
                    '-moz-user-select': 'none',
                    '-ms-user-select': 'none',
                    'user-select': 'none',
                    'background-image': 'none',
                    'border': '1px solid transparent',
                    'border-radius': '4px',
                    'color': '#fff',
                    'background-color': '#337ab7',
                    'border-color': '#2e6da4'
                },
                '#heartland-field[placeholder]': {
                    'letter-spacing': '3px'
                },
                '#heartland-field[name=cardCvv]': {
                    'background': 'transparent url(https://github.com/hps/heartland-php/blob/master/examples/end-to-end/assets/images/cvv1.png?raw=true) no-repeat right',
                    'background-size': '63px 40px',
                },
                'input#heartland-field[name=cardNumber]': {
                    'background': 'transparent url(https://github.com/hps/heartland-php/blob/master/examples/end-to-end/assets/images/ss-inputcard-blank@2x.png?raw=true) no-repeat right',
                    'background-size': '55px 35px'},
                '#heartland-field.invalid.card-type-visa': {
                    'background': 'transparent url(https://github.com/hps/heartland-php/blob/master/examples/end-to-end/assets/images/ss-saved-visa@2x.png?raw=true) no-repeat right',
                    'background-size': '83px 88px',
                    'background-position-y': '-44px'
                },
                '#heartland-field.valid.card-type-visa': {
                    'background': 'transparent url(https://github.com/hps/heartland-php/blob/master/examples/end-to-end/assets/images/ss-saved-visa@2x.png?raw=true) no-repeat right top',
                    'background-size': '82px 86px'
                },
                '#heartland-field.invalid.card-type-discover': {
                    'background': 'transparent url(https://github.com/hps/heartland-php/blob/master/examples/end-to-end/assets/images/ss-saved-discover@2x.png?raw=true) no-repeat right',
                    'background-size': '85px 90px',
                    'background-position-y': '-44px'
                },
                '#heartland-field.valid.card-type-discover': {
                    'background': 'transparent url(https://github.com/hps/heartland-php/blob/master/examples/end-to-end/assets/images/ss-saved-discover@2x.png?raw=true) no-repeat right',
                    'background-size': '85px 90px',
                    'background-position-y': '1px'
                },
                '#heartland-field.invalid.card-type-amex': {
                    'background': 'transparent url(https://github.com/hps/heartland-php/blob/master/examples/end-to-end/assets/images/ss-savedcards-amex@2x.png?raw=true) no-repeat right',
                    'background-size': '50px 90px',
                    'background-position-y': '-44px'
                },
                '#heartland-field.valid.card-type-amex': {
                    'background': 'transparent url(https://github.com/hps/heartland-php/blob/master/examples/end-to-end/assets/images/ss-savedcards-amex@2x.png?raw=true) no-repeat right top',
                    'background-size': '50px 90px'
                },
                '#heartland-field.invalid.card-type-mastercard': {
                    'background': 'transparent url(https://github.com/hps/heartland-php/blob/master/examples/end-to-end/assets/images/ss-saved-mastercard.png?raw=true) no-repeat right',
                    'background-size': '62px 105px',
                    'background-position-y': '-52px'
                },
                '#heartland-field.valid.card-type-mastercard': {
                    'background': 'transparent url(https://github.com/hps/heartland-php/blob/master/examples/end-to-end/assets/images/ss-saved-mastercard.png?raw=true) no-repeat right',
                    'background-size': '62px 105px',
                    'background-position-y': '-1px'
                },
                '#heartland-field.invalid.card-type-jcb': {
                    'background': 'transparent url(https://github.com/hps/heartland-php/blob/master/examples/end-to-end/assets/images/ss-saved-jcb@2x.png?raw=true) no-repeat right',
                    'background-size': '55px 94px',
                    'background-position-y': '-44px'
                },
                '#heartland-field.valid.card-type-jcb': {
                    'background': 'transparent url(https://github.com/hps/heartland-php/blob/master/examples/end-to-end/assets/images/ss-saved-jcb@2x.png?raw=true) no-repeat right top',
                    'background-size': '55px 94px',
                    'background-position-y': '2px'
                },
                'input#heartland-field[name=cardNumber]::-ms-clear': {
                    'display': 'none'
                }
            },
            onTokenSuccess: function (response) {
                console.log(response);
                document.getElementById('cardinalToken').value = response.cardinal.token_value;
                document.getElementById('heartlandToken').value = response.heartland.token_value;
                document.getElementById('cardType').value = response.heartland.card_type;
                cca();
            },
            onTokenError: function (response) {
                console.log(response);
            }
        });

        function cca() {
            Cardinal.setup('init', {
                jwt: '<?php echo $jwt ?>'
            });
            Cardinal.on('payments.validated', function (data, jwt) {
                console.log(data);
                switch (data.ActionCode) {
                    case 'SUCCESS':
                    case 'NOACTION':
                        // Handle successful authentication scenario
                        document.getElementById('cavv').value =
                                data.Payment.ExtendedData.CAVV
                                ? data.Payment.ExtendedData.CAVV
                                : '';
                        document.getElementById('eciflag').value =
                                data.Payment.ExtendedData.ECIFlag
                                ? data.Payment.ExtendedData.ECIFlag
                                : '';
                        document.getElementById('enrolled').value =
                                data.Payment.ExtendedData.Enrolled
                                ? data.Payment.ExtendedData.Enrolled
                                : '';
                        document.getElementById('paresstatus').value =
                                data.Payment.ExtendedData.PAResStatus
                                ? data.Payment.ExtendedData.PAResStatus
                                : '';
                        document.getElementById('xid').value =
                                data.Payment.ExtendedData.XID
                                ? data.Payment.ExtendedData.XID
                                : '';
                        document.getElementById('jwt').value =
                                jwt
                                ? jwt
                                : '';
                        document.getElementById('form').submit();
                        break;

                    case 'FAILURE':
                        // Handle authentication failed or error encounter scenario
                        break;

                    case 'ERROR':
                        // Handle service level error
                        break;
                }
            });
            Cardinal.start('cca', {
                OrderDetails: {
                    OrderNumber: '<?php echo $orderNumber ?>cca'
                },
                Token: {
                    Token: document.getElementById('cardinalToken').value,
                    ExpirationMonth: '01',
                    ExpirationYear: '2099'
                }
            });
        }
    </script>
    <?php
}
