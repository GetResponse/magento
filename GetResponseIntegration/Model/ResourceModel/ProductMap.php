<?php
namespace GetResponse\GetResponseIntegration\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class ProductMap
 * @package GetResponse\GetResponseIntegration\Model\ResourceModel
 */
class ProductMap extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('getresponse_product_map', 'id');
    }
}
