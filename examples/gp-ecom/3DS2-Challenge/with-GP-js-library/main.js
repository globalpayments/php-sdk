(function () {
    const {
    checkVersion,
    initiateAuthentication,
} = GlobalPayments.ThreeDSecure;
    // assign the button that will trigger authentication
    document.addEventListener('DOMContentLoaded', () => {
    const checkVersionButton = document.getElementById('startButton');
    if (!checkVersionButton) {
        return;
    }
    checkVersionButton.addEventListener('click', async (e) => {
        e.preventDefault();
        // check if the card is enrolled for 3D Secure 2
        try {
            versionCheckData = await checkVersion('./ThreeDSecure2/CheckVersion.php', {
            card: {
                number: document.getElementById('card-number').value,
                cardExpiration: document.getElementById('cardExpiration').value,
                securityCode: document.getElementById('securityCode').value,
                cardHolderName: document.getElementById('cardHolderName').value
                },
              timeout: 50*1000
            });
            if (versionCheckData.error) {
            // TODO: handle the scenario where the card is not enrolled for 3D Secure 2
                return;
            }
            console.log(versionCheckData.serverTransactionId);
        } catch (e) {
            console.log(e.reasons);
        }

    try {
        authenticationData = await initiateAuthentication('/ThreeDSecure2/InitiateAuthentication.php', {
        serverTransactionId: versionCheckData.serverTransactionId,
        methodUrlComplete: true,
        card: {
            number: document.getElementById('card-number').value,
            cardExpiration: document.getElementById('cardExpiration').value,
            securityCode: document.getElementById('securityCode').value,
            cardHolderName: document.getElementById('cardHolderName').value
            },
        challengeWindow: {
            windowSize: ChallengeWindowSize.FullScreen,
            displayMode: 'lightbox',
            }
        });

        if (authenticationData.result == "AUTHORIZATION_SUCCESS") {
        }
        else if (authenticationData.result == "AUTHORIZATION_FAILURE") {

        }
        else if (authenticationData.result == "AUTHENTICATION_FAILURE") {

        }
        else if (authenticationData.challenge.response.data.transStatus == "Y") {
            var serverTransactionId = authenticationData.challenge.response.data.threeDSServerTransID;

            var form = document.getElementById("myForm");
            form.setAttribute("action", "/ThreeDSecure2/Authorization");

            var formServerTransId = document.createElement("input");
            formServerTransId.setAttribute("type", "hidden");
            formServerTransId.setAttribute("name", "serverTransId");
            formServerTransId.setAttribute("value", serverTransactionId);
            form.appendChild(formServerTransId);
            form.submit();
        }
        else {
                return false;
            }
        } catch (e) {

    }
    });
});
})()
