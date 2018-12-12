<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTrackingSettingsFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Header
 * @package GetResponse\GetResponseIntegration\Block
 */
class Header extends Template
{
    /** @var Repository */
    private $repository;

    /**
     * @param Context $context
     * @param Repository $repository
     */
    public function __construct(Context $context, Repository $repository)
    {
        parent::__construct($context);
        $this->repository = $repository;
    }

    /**
     * @return array
     */
    public function getTrackingData()
    {
        $trackingCodeSnippet = $this->getTrackingCodeSnippet();

        return [
            'trackingCodeSnippet' => $trackingCodeSnippet
        ];
    }

    /**
     * @return string
     */
    private function getTrackingCodeSnippet()
    {
        $webEventTracking = WebEventTrackingSettingsFactory::createFromArray(
            $this->repository->getWebEventTracking()
        );

        if ($webEventTracking->isEnabled()) {
            return $webEventTracking->getCodeSnippet();
        }

        return '';
    }

}
