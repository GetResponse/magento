<?php
namespace GetResponse\GetResponseIntegration\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class OrderMap
 * @package GetResponse\GetResponseIntegration\Model
 */
class OrderMap extends AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('GetResponse\GetResponseIntegration\Model\ResourceModel\OrderMap');
    }
}
