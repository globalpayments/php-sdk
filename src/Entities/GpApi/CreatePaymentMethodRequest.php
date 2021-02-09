<?php


namespace GlobalPayments\Api\Entities\GpApi;


use GlobalPayments\Api\Builders\AuthorizationBuilder;
use GlobalPayments\Api\Builders\ManagementBuilder;
use GlobalPayments\Api\Entities\GpApi\DTO\Card;
use GlobalPayments\Api\Utils\AccessTokenInfo;
use GlobalPayments\Api\Utils\GenerationUtils;

class CreatePaymentMethodRequest
{
    public $account_name;
    public $reference;
    public $name;
    /**
     * @var $card Card
     */
    public $card;

    public static function createFromAuthorizationBuilder(
        AuthorizationBuilder $builder,
        AccessTokenInfo $accessTokenInfo
    ) {
        $createPaymentMethodRequest = new CreatePaymentMethodRequest();
        $createPaymentMethodRequest->account_name = $accessTokenInfo->getTokenizationAccountName();
        $createPaymentMethodRequest->name = $builder->description ? $builder->description : "";
        $createPaymentMethodRequest->reference = $builder->clientTransactionId ?
            $builder->clientTransactionId : GenerationUtils::generateOrderId();
        $card = new Card();
        $builderCard = $builder->paymentMethod;
        $card->setNumber($builderCard->number);
        $card->setExpireMonth((string)$builderCard->expMonth);
        $card->setExpireYear(substr(str_pad($builderCard->expYear, 4, '0', STR_PAD_LEFT), 2, 2));
        $createPaymentMethodRequest->card = GenerationUtils::convertObjectToArray($card);

        return $createPaymentMethodRequest;
    }

    public static function createFromManagementBuilder(ManagementBuilder $builder)
    {
        $request = new CreatePaymentMethodRequest();
        $card = new Card();
        $builderCard = $builder->paymentMethod;
        $card->setExpireMonth((string)$builderCard->expMonth);
        $card->setExpireYear(substr(str_pad($builderCard->expYear, 4, '0', STR_PAD_LEFT), 2, 2));
        $request->card = GenerationUtils::convertObjectToArray($card);

        return $request;
    }

}
