<?php
namespace GetResponse\GetResponseIntegration\Block;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template\Context;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use Magento\Framework\View\Element\Template;

/**
 * Class Webform
 * @package GetResponse\GetResponseIntegration\Block
 */
class Webform extends Template
{
    /** @var Repository */
    private $repository;

    /**
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param Repository $repository
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        Repository $repository
    )
    {
        parent::__construct($context);
        $this->repository = $repository;
    }

    /**
     * @return mixed
     */
    public function getWebformSettings()
    {
        return $this->repository->getWebformSettings();
    }
}
