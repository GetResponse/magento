<?php
namespace GetResponse\GetResponseIntegration\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;

/**
 * Class TrackingCodeSnippet
 * @package GetResponse\GetResponseIntegration\CustomerData
 */
class WebTrackingCode implements SectionSourceInterface
{

    /** @var CurrentCustomer */
    protected $currentCustomer;

    /**
     * @param CurrentCustomer $currentCustomer
     */
    public function __construct(CurrentCustomer $currentCustomer)
    {
        $this->currentCustomer = $currentCustomer;
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        $email = $this->currentCustomer->getCustomerId() ? $this->currentCustomer->getCustomer()->getEmail() : null;

        return [
            'customerEmail' => $email,
        ];
    }

}