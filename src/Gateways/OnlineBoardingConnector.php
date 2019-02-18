<?php

namespace GlobalPayments\Api\Gateways;

use GlobalPayments\Api\Entities\Exceptions\GatewayException;
use GlobalPayments\Api\Entities\Exceptions\ApiException;
use GlobalPayments\Api\Entities\Exceptions\ValidationException;
use GlobalPayments\Api\Entities\OnlineBoarding\BoardingApplication;
use GlobalPayments\Api\Entities\OnlineBoarding\BoardingResponse;
use GlobalPayments\Api\Utils\GenerationUtils;

class OnlineBoardingConnector extends Gateway
{
    public $portal;
    
    public function __construct()
    {
        parent::__construct('multipart/form-data');
    }

    /**
     * @param string $portal
     * @param string $accessToken
     *
     * @throws GatewayException
     * @return GatewayResponse
     */
    public function authenticate($portal, $accessToken)
    {
        $response = $this->sendRequest(
            'GET',
            sprintf("https://onlineboarding.heartlandpaymentsystems.com/%s/Invitation/%s", $portal, $accessToken)
        );
        
        if (200 == $response->statusCode) {
            if (!in_array($accessToken, explode('/', $response->requestUrl))) {
                throw new GatewayException(
                    sprintf(
                        'Invalid invitation token.'
                    )
                );
            }
            return $response;
        } else {
            throw new GatewayException(
                sprintf(
                    'Invalid invitation token.'
                )
            );
        }
    }
    
    /**
     * @param string $portal
     *
     * @throws GatewayException
     * @return GatewayResponse
     */
    public function getPortalUrl($portal)
    {
        $response = $this->sendRequest(
            'GET',
            sprintf("https://onlineboarding.heartlandpaymentsystems.com/%s", $portal),
            null,
            null,
            'application/json'
        );

        if (200 == $response->statusCode) {
            return $response;
        } else {
            throw new GatewayException(
                sprintf(
                    'Invalid portal'
                )
            );
        }
    }
    
    /**
     * @param string $url
     * @param BoardingApplication $application
     *
     * @throws ValidationException
     * @return GatewayResponse
     */
    public function validateApplication($url, $application)
    {
        $content = $application->buildForm();
        
        $validationResponse = $this->sendRequest(
            'POST',
            $url . '/ValidateFieldValues',
            $content->toJson(),
            null,
            'application/json'
        );

        if (200 == $validationResponse->statusCode) {
            $validationErrors = $application->processValidationResult($validationResponse->rawResponse);
            if (count($validationErrors) > 0) {
                throw new ValidationException(
                    sprintf(
                        'Validation Error'
                    )
                );
            } else {
                return $content;
            }
        } else {
            throw new GatewayException(
                sprintf(
                    'Unable to validate form for submission.'
                )
            );
        }
    }
    
    /**
     * @param string $invitation
     * @param BoardingApplication $application
     *
     * @throws ValidationException
     * @return GatewayResponse
     */
    public function sendApplication($invitation, BoardingApplication $application = null)
    {
        if ($application == null) {
            throw new GatewayException("Application cannot be null.");
        }
        
        // authorize session with the invitation
        $authSession = ($invitation != null) ?
                        $this->authenticate($this->portal, $invitation) :
                        $this->getPortalUrl($this->portal);
        
        // validate
        $content = $this->validateApplication($authSession->requestUrl, $application);

        try {
            $response = $this->sendRequest(
                'POST',
                $authSession->requestUrl,
                $content->toRequest(),
                null,
                'multipart/form-data; boundary="--GlobalPaymentsSDK"'
            );

            return $this->buildResponse($response);
        } catch (GatewayException $ex) {
            /** */
        }
    }
    
    /**
     * @param GatewayResponse $response
     * @param String $message
     *
     * @throws GatewayException
     * @return boardingResponse
     */
    public function buildResponse($response, $message = null)
    {
        if (200 == $response->statusCode || 302 == $response->statusCode) {
            $boardingResponse = new BoardingResponse();
            $url = 200 == $response->statusCode ? $response->requestUrl : $response->redirectUrl;
            if (in_array('ThankYou', explode('/', $url))) {
                $boardingResponse->applicationId = substr($url, strpos($url, '=') + 1);
                $boardingResponse->message = parseResponse($response->rawResponse);
            } elseif (in_array('sign.myhpy.com', explode('/', $url))) {
                $boardingResponse->signatureUrl = $url;
                $boardingResponse->message = "Thank you for your submission.";
            }
            return $boardingResponse;
        } else {
            throw new GatewayException(
                sprintf(
                    'Unknown application submission error.'
                )
            );
        }
    }
    
    public function parseResponse($raw)
    {
        function scrubHtml(string $input)
        {
            $step1 = preg_replace('@"<[^>]+>|&nbsp;|[\r\n]?', '', $input);
            $step2 = preg_replace('@"\s{2,}', ' ', $step1);
            return $step2;
        }

        $sb = '';

        $tags = ['h1', 'div'];
        $start = 0;
        foreach ($tags as $tag) {
            $startTag = sprintf('<%s>', $tag);
            $endTag = sprintf('</%s>', $tag);

            $start = strpos($raw, $startTag, $start) + strlen($startTag);
            $length = strpos($raw, $endTag, $start) - $start;

            if ($length > 0) {
                $message = $raw.substr($start, $length);
                $sb .= scrubHtml($message);
            }
        }

        return $sb;
    }
}
