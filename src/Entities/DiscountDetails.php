<?php

namespace GlobalPayments\Api\Entities;

class DiscountDetails
{

    /**
     * The dollar amount of discount applied to a product
     *
     * @var float
     */
    public $discountAmount;

    /**
     * The name of the discount applied to a product.
     * This does not impact transaction functionality. It is used for reporting purposes.
     *
     * @var string
     */
    public $discountName;

    /**
     * The discount percentage applied to a product. Corresponds with productDiscountName.
     * This does not impact transaction functionality. It is used for reporting purposes.
     *
     * @var float|int
     */
    public $discountPercentage;

    /**
     * This field defines the transaction types that the discount can be applied to.
     * Corresponds with productDiscountName.
     * This does not impact transaction functionality. It is used for reporting purposes.
     *
     * @var float
     */
    public $discountType;

    /**
     * Indicates the priority order in which discounts are applied at both the order and product levels.
     *
     * @var int
     */
    public $priority;

    /**
     * Indicates if the discount can be stacked with other discounts.
     *
     * @var bool
     */
    public $stackable;

    public function __construct(
        $discountAmount = null,
        $discountName = null,
        $discountPercentage = null,
        $discountType = null,
        $priority = 1,
        $stackable = true
    ) {
        $this->discountAmount       = $discountAmount;
        $this->discountName         = $discountName;
        $this->discountPercentage   = $discountPercentage;
        $this->discountType         = $discountType;
        $this->priority             = $priority;
        $this->stackable            = $stackable;
    }
}
