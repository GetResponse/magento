<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTrackingSettings;
use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTrackingSettingsFactory;
use Magento\Framework\View\Element\Template\Context;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use Magento\Framework\View\Element\Template;

/**
 * Class WebEventTracking
 * @package GetResponse\GetResponseIntegration\Block
 */
class WebEventTracking extends Template
{
    /** @var Repository */
    private $repository;

    /**
     * @param Context $context
     * @param Repository $repository
     */
    public function __construct(
        Context $context,
        Repository $repository
    ) {
        parent::__construct($context);
        $this->repository = $repository;
    }

    /**
     * @return WebEventTrackingSettings
     */
    public function getWebEventTracking()
    {
        return WebEventTrackingSettingsFactory::createFromArray(
            $this->repository->getWebEventTracking()
        );
    }
}
