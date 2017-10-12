<?php
namespace GetResponse\GetResponseIntegration\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Webform
 * @package GetResponse\GetResponseIntegration\Model
 */
class Webform extends AbstractModel
{
    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('GetResponse\GetResponseIntegration\Model\ResourceModel\Webform', 'id');
    }
}