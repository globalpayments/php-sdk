<?php

namespace GlobalPayments\Api\Entities;

class Product
{
    /** @var string */
    public $productId;

    /** @var string */
    public $productName;

    /** @var string */
    public $description;

    /** @var integer */
    public $quantity;

    /** @var float */
    public $unitPrice;

    /** @var float */
    public $netUnitPrice;

    /** @var string */
    public $unitCurrency;

    /** @var float */
    public $taxAmount;

    /** @var float */
    public $taxPercentage;

    /** @var float */
    public $discountAmount;

    /** @var string */
    public $url;

    /** @var string */
    public $imageUrl;
}