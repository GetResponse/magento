<?php
namespace GetResponse\GetResponseIntegration\Block;

use Exception;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Logger\Logger;
use GrShareCode\ContactList\ContactListService;
use GrShareCode\ContactList\FromFieldsCollection;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Element\Template\Context;

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
     * @param ManagerInterface $messageManager
     * @param RedirectFactory $redirectFactory
     * @param ApiClientFactory $apiClientFactory
     * @param Logger $logger
     * @param Repository $repository
     */
    public function __construct(
        Context $context,
        ManagerInterface $messageManager,
        RedirectFactory $redirectFactory,
        ApiClientFactory $apiClientFactory,
        Logger $logger,
        Repository $repository
    ) {
        parent::__construct(
            $context,
            $messageManager,
            $redirectFactory,
            $apiClientFactory,
            $logger
        );
        $this->repository = $repository;
    }

    /**
     * @return FromFieldsCollection|Redirect
     */
    public function getAccountFromFields()
    {
        try {
            $service = new ContactListService($this->getApiClientFactory()->createGetResponseApiClient());

            return $service->getFromFields();
        } catch (Exception $e) {
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
            $apiClient = $this->getApiClientFactory()->createGetResponseApiClient();

            return $apiClient->getSubscriptionConfirmationSubject($lang);
        } catch (Exception $e) {
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
            $apiClient = $this->getApiClientFactory()->createGetResponseApiClient();

            return $apiClient->getSubscriptionConfirmationBody($lang);
        } catch (Exception $e) {
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
