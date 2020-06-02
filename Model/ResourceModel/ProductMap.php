<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ProductMap extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('getresponse_product_map', 'id');
    }
}
