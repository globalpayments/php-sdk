$(document).ready(function () {
    $("form").submit(function (event) {
        var formData = {
            cardCvv: $("#cardCvv").val(),
            cardExpiration: $("#cardExpiration").val(),
            cardNumber: $("#cardNumber").val(),
            address1: $("#address1").val(),
            address2: $("#address2").val(),
            city: $("#city").val(),
            zip: $("#zip").val(),
            state: $("#state").val(),
            country: $("#country").val()
        };

        $.ajax({
            type: "POST",
            url: "charge.php",
            data: formData,
            dataType: "json",
            encode: true,
        }).done(function (data) {
            if (data['error'] == "true") {
                $("#challenge").contents().find('body').html(data['message']);
                return;
            }

            let form = document.createElement("form");
            form.setAttribute("method", "POST");
            form.setAttribute("action", data.issuerAcsUrl);
            form.setAttribute("target", "challenge");

            let creqObj = document.createElement("input");
            creqObj.setAttribute("type", "hidden");
            creqObj.setAttribute("name", "creq");
            creqObj.setAttribute("value", data.payerAuthenticationRequest);

            form.appendChild(creqObj);
            $("#challenge").append(form);
            form.submit();
        });

        event.preventDefault();
    });
    window.addEventListener('message', function (e) {
        // Get the sent data
        const data = e.data;
        let formData = {
            cardCvv: $("#cardCvv").val(),
            cardExpiration: $("#cardExpiration").val(),
            cardNumber: $("#cardNumber").val(),
            ThreeDSData: e.data
        };
        $("#challenge").css("visibility","hidden");
        $.ajax({
            type: "POST",
            url: "charge.php",
            data: formData,
            dataType: "json",
            encode: true,
        }).done(function (data) {
            if (data.responseCode == "00") {
                document.write('Success! Transaction Id: ' + data.transactionReference.transactionId)
            } else {
                document.write('Fail!');
            }
        })
    })
});