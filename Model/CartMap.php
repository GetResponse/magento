<?php
namespace GetResponse\GetResponseIntegration\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class CartMap
 * @package GetResponse\GetResponseIntegration\Model
 */
class CartMap extends AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('GetResponse\GetResponseIntegration\Model\ResourceModel\CartMap');
    }
}
