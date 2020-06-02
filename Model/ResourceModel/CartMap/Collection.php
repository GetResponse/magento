<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Model\ResourceModel\CartMap;

use GetResponse\GetResponseIntegration\Model\ResourceModel\CartMap as ResourceCartMap;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use GetResponse\GetResponseIntegration\Model\CartMap;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            CartMap::class,
            ResourceCartMap::class
        );
    }
}
