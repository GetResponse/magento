<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento\Quote\ReadModel;

use GetResponse\GetResponseIntegration\Domain\Magento\Quote\ReadModel\Query\CustomerQuotes;
use GetResponse\GetResponseIntegration\Domain\Magento\Quote\ReadModel\Query\QuoteById;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote;

class QuoteReadModel
{
    private $objectManager;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function getCustomerQuotes(CustomerQuotes $query)
    {
        return $this->objectManager
            ->create(Quote::class)
            ->getCollection()
            ->addFieldToFilter('customer_id', $query->getId())
            ->setOrder('created_at', 'desc');
    }

    public function getQuoteById(QuoteById $query): Quote
    {
        return $this->objectManager
            ->create(Quote::class)
            ->load($query->getId());
    }
}
