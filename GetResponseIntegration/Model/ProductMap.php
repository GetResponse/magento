<?php
namespace GetResponse\GetResponseIntegration\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class ProductMap
 * @package GetResponse\GetResponseIntegration\Model
 */
class ProductMap extends AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('GetResponse\GetResponseIntegration\Model\ResourceModel\ProductMap');
    }
}
