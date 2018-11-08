<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\GetResponse\GetresponseApiClientFactory;
use GrShareCode\ContactList\ContactListService;
use GrShareCode\ContactList\FromFieldsCollection;
use Magento\Framework\View\Element\Template\Context;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class Lists
 * @package GetResponse\GetResponseIntegration\Block
 */
class Lists extends GetResponse
{
    /** @var Repository */
    private $repository;

    /**
     * @param Context $context
     * @param Repository $repository
     * @param GetresponseApiClientFactory $apiClientFactory
     * @param RedirectFactory $redirectFactory
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        Context $context,
        Repository $repository,
        GetresponseApiClientFactory $apiClientFactory,
        RedirectFactory $redirectFactory,
        ManagerInterface $messageManager
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->apiClientFactory = $apiClientFactory;
        $this->redirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * @return FromFieldsCollection|Redirect
     */
    public function getAccountFromFields()
    {
        try {
            $service = new ContactListService($this->apiClientFactory->createGetResponseApiClient());
            return $service->getFromFields();
        } catch (\Exception $e) {
           return $this->handleException($e);
        }
    }

    /**
     * @return array|Redirect
     */
    public function getSubscriptionConfirmationsSubject()
    {
        try {
            $countryCode = $this->repository->getMagentoCountryCode();
            $lang = substr($countryCode, 0, 2);
            $apiClient = $this->apiClientFactory->createGetResponseApiClient();
            return $apiClient->getSubscriptionConfirmationSubject($lang);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @return array|Redirect
     */
    public function getSubscriptionConfirmationsBody()
    {
        try {
            $countryCode = $this->repository->getMagentoCountryCode();
            $lang = substr($countryCode, 0, 2);
            $apiClient = $this->apiClientFactory->createGetResponseApiClient();
            return $apiClient->getSubscriptionConfirmationBody($lang);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    /**
     * @param string $backUrl
     * @return string
     */
    public function getBackUrl($backUrl = null)
    {
        if (null === $backUrl) {
            $backUrl = $this->getRequest()->getParam('back');
        }
        return $this->createBackUrl($backUrl);
    }

    /**
     * @param string $back
     * @return string
     */
    private function createBackUrl($back)
    {
        switch ($back) {
            case 'export':
                return 'getresponse/export/index';
                break;

            case 'registration':
                return 'getresponse/registration/index';
                break;

            case 'newsletter':
                return 'getresponse/newsletter/index';
                break;
        }
        return '';
    }
}
