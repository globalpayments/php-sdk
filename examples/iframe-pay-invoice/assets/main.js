// Configure account
GlobalPayments.configure({
    publicApiKey: "pkapi_cert_P6dRqs1LzfWJ6HgGVZ"
});

// Create Form
const cardForm = GlobalPayments.creditCard.form("#credit-card", { style: "blank" });

// form-level event handlers. examples:
cardForm.ready(() => {
    console.log("Registration of all credit card fields occurred");
});

cardForm.on("token-success", (resp) => {
    document.querySelector("input[name=token_value]").value = resp.paymentReference;
    document.querySelector("input[name=cardholder_name]").value = resp.details.cardholderName;
    document.getElementById('payment_form').submit();
});

cardForm.on("token-error", (resp) => {
// show error to the consumer
});

// field-level event handlers. example:
cardForm.on("card-number", "register", () => {
    console.log("Registration of Card Number occurred");
});