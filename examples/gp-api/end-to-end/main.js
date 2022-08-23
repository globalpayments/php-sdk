// Configure account
GlobalPayments.configure({
    accessToken,
    env: "sandbox", // or "production"
    apiVersion: "2021-03-22"
});

// Create Form
const cardForm = GlobalPayments.creditCard.form("#credit-card", {style: "gp-default"});
// form-level event handlers. examples:
cardForm.ready(() => {
    console.log("Registration of all credit card fields occurred");
    cardForm.addStylesheet({
        '#secure-payment-field-wrapper': {
            'display': 'block !important'
        },
        /* Card number Field error messages*/
        /* Display error if card is not valid */
        '#secure-payment-field.card-number.invalid + .extra-div-1::before': {
            'content': '"The Card Number is not valid"',
            'color': 'red',
            'height': '1em',
            'min-height': '1em',
            'width': '100%'
        },
        /* Display error if card length is not reached*/
        '#secure-payment-field.card-number.possibly-valid.invalid + .extra-div-1::before': {
            'content': '"The Card Number is not valid"',
            'color': 'red',
            'height': '1em',
            'min-height': '1em',
            'width': '100%'
        },
        /* Display error if card used is Diners*/
        '#secure-payment-field.card-number.valid.card-type-diners + .extra-div-1::before': {
            'content': '"Cannot use Diners Card. Please enter another card"',
            'color': 'red',
            'height': '1em',
            'min-height': '1em',
            'width': '100%'
        },
        /* Display error if card used is Discover*/
        '#secure-payment-field.card-number.valid.card-type-discover + .extra-div-1::before': {
            'content': '"Cannot use Discover Card. Please enter another card"',
            'color': 'red',
            'height': '1em',
            'min-height': '1em',
            'width': '100%'
        },
        /* Display error if card used is JCB*/
        '#secure-payment-field.card-number.valid.card-type-jcb + .extra-div-1::before': {
            'content': '"Cannot use JCB Card. Please enter another card"',
            'color': 'red',
            'height': '1em',
            'min-height': '1em',
            'width': '100%'
        },
        /* Display error if card used is unknown*/
        '#secure-payment-field.card-number.possibly-valid.invalid.card-type-unknown + .extra-div-1::before': {
            'content': '"Cannot use unknown Card. Please enter another card"',
            'color': 'red',
            'height': '1em',
            'min-height': '1em',
            'width': '100%'
        },

        /* Expiry Date error messages*/
        /* Display error if expiry date is not valid*/
        '#secure-payment-field.card-expiration.possibly-valid.invalid + .extra-div-1::before': {
            'content': '"Please enter a valid month/year"',
            'color': 'red',
            'height': '1em',
            'min-height': '1em',
            'width': '100%'
        },
        /* Display error if expiry date is in past*/
        '#secure-payment-field.card-expiration.invalid + .extra-div-1::before': {
            'content': '"The Expiry Date is not valid"',
            'color': 'red',
            'height': '1em',
            'min-height': '1em',
            'width': '100%'
        },

        /* Security Code error messages*/
        /* Display error if security code is too short*/
        '#secure-payment-field.card-cvv.possibly-valid.invalid + .extra-div-1::before': {
            'content': '"Security Code is too short"',
            'color': 'red',
            'height': '1em',
            'min-height': '1em',
            'width': '100%'
        },
        /* Display error if security code too long for Visa*/
        '#secure-payment-field.card-cvv.invalid.card-type-visa + .extra-div-1::before': {
            'content': '"Security Code must be 3 digits"',
            'color': 'red',
            'height': '1em',
            'min-height': '1em',
            'width': '100%'
        },
        /* Display error if security code too long for MC*/
        '#secure-payment-field.card-cvv.invalid.card-type-mastercard + .extra-div-1::before': {
            'content': '"Security Code must be 3 digits"',
            'color': 'red',
            'height': '1em',
            'min-height': '1em',
            'width': '100%'
        },
        /* Display error if security code too short for Amex*/
        '#secure-payment-field.card-cvv.card-type-amex.possibly-valid.invalid + .extra-div-1::before': {
            'content': '"Security Code for Amex must be 4 digits"',
            'color': 'red',
            'height': '1em',
            'min-height': '1em',
            'width': '100%'
        },
    });
});

cardForm.on("token-success", async (resp) => {

    //start 3DS2 Flow
    await start3DS(resp.paymentReference);

    // add payment token to form as a hidden input
    const token = document.createElement("input");
    token.type = "hidden";
    token.name = "payment_token";
    token.value = resp.paymentReference;

    // Submit data to the integration's backend for processing
    const form = document.getElementById("payment-form");
    form.appendChild(token);
});

cardForm.on("token-error", (resp) => {
    // show error to the consumer
    console.log(resp);

});

async function start3DS(token){
    const {
        checkVersion,
        getBrowserData,
        initiateAuthentication,
        AuthenticationSource,
        AuthenticationRequestType,
        MessageCategory,
        ChallengeRequestIndicator,
        ChallengeWindowSize,
        postToIframe,
        handleInitiateAuthentication,
    } = GlobalPayments.ThreeDSecure;

    try {
        versionCheckData = await checkVersion('check3dsVersion.php', {
            tokenResponse:token
        });

        console.log ('Version Check Data: ', versionCheckData)
    }
    catch (e) {
        console.log ('Version Check error: ', e.reasons || e.message);
    }
    try {
        // 3DS2 Flow
        if (versionCheckData.messageVersion == 'TWO') {
            console.log("3ds2 flow")
            authenticateData = await initiateAuthentication('initiateAuthentication.php', {
                challengeWindow: {
                    windowSize: ChallengeWindowSize.Windowed600x400,
                    displayMode: 'lightbox',
                },
                authenticationRequestType: AuthenticationRequestType.PaymentTransaction,
                serverTransactionId: versionCheckData.serverTransactionId,
                methodUrlComplete: true,
                tokenResponse: token,
                order: {
                    currency: 'EUR',
                    amount: '100'
                }
            });
            console.log('Authentication Data:', authenticateData);
            // frictionless authentication success and authorization success
            if (authenticateData.result == "SUCCESS_AUTHENTICATED" && authenticateData.liabilityShift == 'YES') {
                var form = document.getElementById("payment-form");
                form.setAttribute("action", "authorization.php");
                var formServerTransId = document.createElement("input");
                formServerTransId.setAttribute("type", "hidden");
                formServerTransId.setAttribute("name", "serverTransactionId");
                formServerTransId.setAttribute("value", authenticateData.serverTransactionId);
                var paymentToken = document.createElement("input");
                paymentToken.setAttribute("type", "hidden");
                paymentToken.setAttribute("name", "tokenResponse");
                paymentToken.setAttribute("value", token);
                console.log('PMT:', token);
                form.appendChild(formServerTransId);
                form.appendChild(paymentToken);
                form.submit();
            }

            // frictionless authentication success and authorization failure
            else if (authenticateData.result == "AUTHORIZATION_FAILURE") {
                // TODO: proceed to failure page or display decline information
                responseDiv.style.display = "block";
                responseDiv.innerHTML+= "<strong>Oh Dear! Frictionless authentication but your transaction was not authorized successfully.</strong>";
                responseDiv.innerHTML+= "<br><br>Server Trans ID :" + authenticateData.serverTransId;
                responseDiv.innerHTML+= "<br><br>Authentication Value :" + authenticateData.authenticationValue;
                responseDiv.innerHTML+= "<br><br>DS Trans ID :" + authenticateData.dsTransId;
                responseDiv.innerHTML+= "<br><br>Message Version :" + authenticateData.messageVersion;
                responseDiv.innerHTML+= "<br><br>ECI :" + authenticateData.eci;
                responseDiv.innerHTML+= "<br><br>Result :" + authenticateData.resultCode;
                responseDiv.innerHTML+= "<br><br>Message :" + authenticateData.resultMessage;
                responseDiv.innerHTML+= "<br><br>Order ID :" + authenticateData.orderId;
                responseDiv.innerHTML+= "<br><br>Pasref :" + authenticateData.pasref;
            }

            // frictionless authentication success and authorization failure
            else if (authenticateData.result == "AUTHENTICATION_FAILURE") {
                // TODO: proceed to failure page or display failed authentication information
                responseDiv.style.display = "block";
                responseDiv.innerHTML+= "<strong>Oh No! Your transaction failed authentication.</strong>";
                responseDiv.innerHTML+= "<br><br>Server Trans ID: " + authenticateData.serverTransId;
                if(authenticateData.authenticationValue) {responseDiv.innerHTML+= "<br><br>Authentication Value: " + authenticateData.authenticationValue;}
                else{responseDiv.innerHTML+= "<br><br>The 3D Secure 2 Solution threw an error.";}
                if(authenticateData.dsTransId){responseDiv.innerHTML+= "<br><br>DS Trans ID: " + authenticateData.dsTransId;}
                if(authenticateData.messageVersion){responseDiv.innerHTML+= "<br><br>Message Version: " + authenticateData.messageVersion;}
                if(authenticateData.eci){responseDiv.innerHTML+= "<br><br>ECI: " + authenticateData.eci;}
                if(authenticateData.status){responseDiv.innerHTML+= "<br><br>Status: " + authenticateData.status;}
                if(authenticateData.statusReason){responseDiv.innerHTML+= "<br><br>Status Reason: " + authenticateData.statusReason;}
            }
                // challenge success
            else if (authenticateData.challenge.response.data.transStatus == "Y") {
                var serverTransactionId = authenticateData.challenge.response.data.threeDSServerTransID;
                console.log('Challenge:', serverTransactionId);
                var form = document.getElementById("payment-form");
                form.setAttribute("action", "authorization.php");

                var formServerTransId = document.createElement("input");
                formServerTransId.setAttribute("type", "hidden");
                formServerTransId.setAttribute("name", "serverTransactionId");
                formServerTransId.setAttribute("value", serverTransactionId);
                var paymentToken = document.createElement("input");
                paymentToken.setAttribute("type", "hidden");
                paymentToken.setAttribute("name", "tokenResponse");
                paymentToken.setAttribute("value", token);
                console.log('PMT:', token);
                form.appendChild(formServerTransId);
                form.appendChild(paymentToken);
                form.submit();
            }
            // challenge failure
            else {
                // TODO: proceed to failure page or display failed authentication information
                console.log ('else error')
                return false;
            }
        } else {
            console.log('3DS version not implemented!')
            return false;
        }
    }
    catch (e) {
        console.log('Initiate Authentication Error: ', e.reasons || e.message);
        return;
    }
    return false;
};
