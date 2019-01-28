<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Order\Exception;

use GetResponse\GetResponseIntegration\Domain\GetResponse\GetResponseDomainException;
use Magento\Sales\Model\Order\Item;

/**
 * Class InvalidOrderException
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Order\Exception
 */
class InvalidOrderException extends GetResponseDomainException
{
    /**
     * @param Item $item
     * @return InvalidOrderException
     */
    public static function forItemWithEmptyProduct(Item $item)
    {
        return new self(sprintf('Product not found in Order Item: %s', $item->getName()));
    }
}