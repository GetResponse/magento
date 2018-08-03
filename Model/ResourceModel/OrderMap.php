<?php
namespace GetResponse\GetResponseIntegration\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class OrderMap
 * @package GetResponse\GetResponseIntegration\Model\ResourceModel
 */
class OrderMap extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('getresponse_order_map', 'id');
    }
}
