<?php

namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Builders\PayFacBuilder;
use GlobalPayments\Api\Entities\Enums\TransactionModifier;
use GlobalPayments\Api\Entities\Enums\TransactionType;
use GlobalPayments\Api\Entities\Enums\UserStatus;
use GlobalPayments\Api\Entities\Enums\UserType;
use GlobalPayments\Api\Entities\Exceptions\ArgumentException;
use GlobalPayments\Api\Entities\GpApi\DTO\PaymentMethod;
use GlobalPayments\Api\Entities\PayFac\UserReference;

/**
 * User response.
 *
 * @property UserType $userType Indicates the type of organization entity being boarded (ex: MERCHANT)..
 * @property string $userId Unique Global Payments generated id.
 * @property UserStatus $userStatus Indicates whether the user is in its lifecycle.
 */
class User
{
    /**
     * This is a label to identify the user
     *
     * @var string
    */
    public $name;

    /**
     * Global Payments time indicating when the object was created in ISO-8601 format.
     *
     * @var \DateTime
     */
    public $timeCreate;

    /**
     * The date and time the resource object was last changed.
     *
     * @var \DateTime
     */
    public $timeLastUpdated;

    /**
     * @var string
     */
    public $email;

    /** @var array<Address> */
    public $addresses = [];

    /**
     * @var PhoneNumber
     */
    public $contactPhone;

    /**
     * A a further description of the status of merchant boarding.
     *
     * @var string
     */
    public $statusDescription;

    /**
     * The result of the action executed.
     *
     * @var string
     */
    public $responseCode;

    /** @var $userReference */
    public $userReference;

    /** @var PersonList */
    public $personList;

    /** @var  PaymentMethodList */
    public $paymentMethodList;

    public function __set($name, $value)
    {
        switch ($name) {
            case 'userId':
                if (!$this->userReference instanceof UserReference) {
                    $this->userReference = new UserReference();
                }
                $this->userReference->userId = $value;
                return;
            case 'userType':
                if (!$this->userReference instanceof UserReference) {
                    $this->userReference = new UserReference();
                }
                $this->userReference->userType = $value;
                return;
            case 'userStatus':
                if (!$this->userReference instanceof UserReference) {
                    $this->userReference = new UserReference();
                }
                $this->userReference->userStatus = $value;
                return;
            default:
                break;
        }

        if (property_exists($this, $name)) {
            return $this->{$name} = $value;
        }

        throw new ArgumentException(sprintf('Property `%s` does not exist on User', $name));
    }

    public function __get($name)
    {
        switch ($name) {
            case 'userId':
                if ($this->userReference !== null) {
                    return $this->userReference->userId;
                }
                return null;
            case 'userType':
                if ($this->userReference !== null) {
                    return $this->userReference->userType;
                }
                return null;
            case 'userStatus':
                if ($this->userReference !== null) {
                    return $this->userReference->userStatus;
                }
                return null;
            default:
                break;
        }

        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        throw new ArgumentException(sprintf('Property `%s` does not exist on User', $name));
    }

    public function __isset($name)
    {
        return in_array($name, [
                'userId',
                'userStatus',
                'userType',
            ]) || isset($this->{$name});
    }

    /**
     * Creates an `User` object from an existing user ID.
     *
     * @param string $userId
     * @param UserType $userType
     *
     * @return User
     * @throws ArgumentException
     */
    public static function fromId(string $userId,string $userType)
    {
        $userType = UserType::validate($userType);

        $user = new User();
        $user->userReference = new UserReference();
        $user->userReference->userId = $userId;
        $user->userReference->userType = $userType;

        return $user;

    }

    public function edit()
    {
        $builder = (new PayFacBuilder(TransactionType::EDIT))
            ->withUserReference($this->userReference);

        if ($this->userReference->userType !== null) {
            $builder = $builder->withModifier(constant( TransactionModifier::class."::{$this->userReference->userType}"));
        }

        return $builder;
    }
}
