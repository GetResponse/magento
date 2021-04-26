<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Model;

use GetResponse\GetResponseIntegration\Model\ResourceModel\OrderMap as ResourceOrderMap;
use Magento\Framework\Model\AbstractModel;

class OrderMap extends AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(ResourceOrderMap::class);
    }
}
