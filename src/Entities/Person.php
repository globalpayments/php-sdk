<?php

namespace GlobalPayments\Api\Entities;

use GlobalPayments\Api\Entities\Enums\PersonFunctions;

/**
 * The persons or applicants connected to an user (example: merchant).
 */
class Person
{
    /**
     * Describes the functions that a person can have in an organization.
     *
     * @var PersonFunctions
     */
    public $functions;

    /**
     * Person's first name
     *
     * @var string
     */
    public $firstName;

    /**
     * Middle's first name
     *
     * @var string
     */
    public $middleName;

    /**
     * Person's last name
     *
     * @var string
     */
    public $lastName;

    /**
     * Person's email address
     *
     * @var string
     */
    public $email;

    /**
     * Person's date of birth
     *
     * @var string
     */
    public $dateOfBirth;

    /**
     * The national id number or reference for the person for their nationality. For example for Americans this would
     * be SSN, for Canadians it would be the SIN, for British it would be the NIN.
     *
     * @var string
     */
    public $nationalIdReference;

    /**
     * The job title the person has.
     *
     * @var string
     */
    public $jobTitle;

    /**
     * The equity percentage the person owns of the business that is applying to Global Payments for payment
     * processing services.
     *
     * @var string
     */
    public $equityPercentage;

    /**
     * Customer's address
     *
     * @var Address
     */
    public $address;

    /**
     * Person's home phone number
     *
     * @var PhoneNumber
     */
    public $homePhone;

    /**
     * Person's work phone number
     *
     * @var PhoneNumber
     */
    public $workPhone;
}
