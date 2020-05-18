<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class OrderMap extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('getresponse_order_map', 'id');
    }
}
