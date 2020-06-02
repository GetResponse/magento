<?php
declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento\Subscriber\ReadModel;

use GetResponse\GetResponseIntegration\Domain\Magento\Subscriber\ReadModel\Query\SubscriberEmail;
use Magento\Framework\ObjectManagerInterface;
use Magento\Newsletter\Model\Subscriber;

class SubscriberReadModel
{
    private $objectManager;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function loadSubscriberByEmail(SubscriberEmail $query)
    {
        return $this->objectManager->create(Subscriber::class)->loadByEmail($query->getEmail());
    }
}
