<?php
namespace GetResponse\GetResponseIntegration\Model;

use Magento\Framework\Model\AbstractModel;

class Account extends AbstractModel
{
    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('GetResponse\GetResponseIntegration\Model\ResourceModel\Account', 'id');
    }
}