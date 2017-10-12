<?php
namespace GetResponse\GetResponseIntegration\Model\ResourceModel\Webform;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Webform
 * @package GetResponse\GetResponseIntegration\Model\ResourceModel\Webform
 */
class Webform extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('GetResponse\GetResponseIntegration\Model\Webform', 'GetResponse\GetResponseIntegration\Model\ResourceModel\Webform');
    }
}