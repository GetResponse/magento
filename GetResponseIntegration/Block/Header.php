<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTrackingSettingsFactory;
use Magento\Framework\View\Element\Template;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
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
     * @return string
     */
    public function getSnippetCode()
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
