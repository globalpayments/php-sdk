// Configure account
GlobalPayments.configure({
    accessToken,
    env: "sandbox", // or "production"
    apiVersion: "2021-03-22",
    account: account,
    // merchantId: merchantId,
    apms: {
        currencyCode: "USD",
        allowedCardNetworks: [GlobalPayments.enums.CardNetwork.Visa, GlobalPayments.enums.CardNetwork.Mastercard, GlobalPayments.enums.CardNetwork.Amex, GlobalPayments.enums.CardNetwork.Discover],
        qrCodePayments: {
            enabled: true
        }
    }
});

// Create Form
const cardForm = GlobalPayments.creditCard.form(
    '#credit-card',
    {
        amount: "20",
        style: "gp-default",
        apms: [
            GlobalPayments.enums.Apm.QRCodePayments,
        ],
    });

cardForm.on("token-success", function (resp) { console.log(resp); });
cardForm.on("token-error", function (resp) { console.log(resp); });

cardForm.on(GlobalPayments.enums.QRCodePaymentsMerchantInteractionEvents.PaymentMethodSelection, function (qrCodePaymentProviderData) {
    const { provider } = qrCodePaymentProviderData;
    let initiatePaymentResponse = null;
    const xmlhttp = new XMLHttpRequest();
    xmlhttp.onload = function() {
        initiatePaymentResponse = JSON.parse(this.responseText);
        console.log(initiatePaymentResponse);
        const merchantCustomEventProvideDetails = new CustomEvent(GlobalPayments.enums.QRCodePaymentsMerchantInteractionEvents.ProvideQRCodeDetailsMerchantEvent, {
            detail: initiatePaymentResponse,
        });
        window.dispatchEvent(merchantCustomEventProvideDetails);
    }

    xmlhttp.open("GET", "initiatePayment.php?provider=" + provider);
    xmlhttp.send();
});
