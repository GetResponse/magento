<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Model\ResourceModel\OrderMap;

use GetResponse\GetResponseIntegration\Model\ResourceModel\OrderMap as ResourceOrderMap;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use GetResponse\GetResponseIntegration\Model\OrderMap;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            OrderMap::class,
            ResourceOrderMap::class
        );
    }
}
