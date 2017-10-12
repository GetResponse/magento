<?php
namespace GetResponse\GetResponseIntegration\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Customs
 * @package GetResponse\GetResponseIntegration\Model
 */
class Customs extends AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('GetResponse\GetResponseIntegration\Model\ResourceModel\Customs');
    }
}
