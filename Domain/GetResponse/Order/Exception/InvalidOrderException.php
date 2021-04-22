<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Order\Exception;

use GetResponse\GetResponseIntegration\Domain\GetResponse\GetResponseDomainException;
use Magento\Sales\Model\Order\Item;

class InvalidOrderException extends GetResponseDomainException
{
    /**
     * @param Item $item
     * @return InvalidOrderException
     */
    public static function forItemWithEmptyProduct(Item $item): InvalidOrderException
    {
        return new self(sprintf('Product not found in Order Item: %s', $item->getName()));
    }
}
