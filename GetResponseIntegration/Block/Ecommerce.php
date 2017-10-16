<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Ecommerce
 * @package GetResponse\GetResponseIntegration\Block
 */
class Ecommerce extends GetResponse
{
    /** @var Repository */
    private $repository;

    /**
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param Repository $repository
     */
    public function __construct(Context $context, ObjectManagerInterface $objectManager, Repository $repository)
    {
        parent::__construct($context, $objectManager);
        $this->repository = $repository;
    }

    /**
     * @return string
     */
    public function getShopStatusFromConfig()
    {
        return $this->repository->getShopStatus();
    }

    /**
     * @return string
     */
    public function getCurrentShopId()
    {
        return $this->repository->getShopId();
    }

    /**
     * @return array
     */
    public function getShops()
    {
        return (array)$this->getClient()->getShops();
    }
}
