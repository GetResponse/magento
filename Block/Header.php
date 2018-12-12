<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\Magento\WebEventTrackingSettingsFactory;
use Magento\Customer\Model\Session;
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

    /** @var Session */
    private $customerSession;

    /**
     * @param Context $context
     * @param Repository $repository
     * @param Session $customerSession
     */
    public function __construct(Context $context, Repository $repository, Session $customerSession)
    {
        parent::__construct($context);
        $this->repository = $repository;
        $this->customerSession = $customerSession;
    }

    /**
     * @return array
     */
    public function getTrackingData()
    {
        $trackingCodeSnippet = $this->getTrackingCodeSnippet();

        return [
            'isTrackingCodeEnabled' => !empty($trackingCodeSnippet),
            'trackingCodeSnippet' => $trackingCodeSnippet,
            'customerEmail' => $this->getLoggedInCustomerEmail(),
        ];
    }

    /**
     * @return string
     */
    private function getLoggedInCustomerEmail()
    {
        if (false === $this->customerSession->isLoggedIn()) {
            return '';
        }

        return $this->customerSession->getCustomer()->getEmail();
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
