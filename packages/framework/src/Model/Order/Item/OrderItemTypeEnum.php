<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Item;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class OrderItemTypeEnum extends AbstractEnum
{
    public const string TYPE_PAYMENT = 'payment';
    public const string TYPE_PRODUCT = 'product';
    public const string TYPE_DISCOUNT = 'discount';
    public const string TYPE_TRANSPORT = 'transport';
    public const string TYPE_ROUNDING = 'rounding';
}
