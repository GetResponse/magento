<?php
namespace GetResponse\GetResponseIntegration\Block;

use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\RepositoryFactory;
use GrShareCode\ContactList\ContactListService;
use GrShareCode\ContactList\FromFieldsCollection;
use GrShareCode\GetresponseApiException;
use Magento\Framework\View\Element\Template\Context;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use Magento\Framework\View\Element\Template;

/**
 * Class Lists
 * @package GetResponse\GetResponseIntegration\Block
 */
class Lists extends Template
{
    /** @var Repository */
    private $repository;

    /** @var RepositoryFactory */
    private $repositoryFactory;

    /**
     * @param Context $context
     * @param Repository $repository
     * @param RepositoryFactory $repositoryFactory
     */
    public function __construct(
        Context $context,
        Repository $repository,
        RepositoryFactory $repositoryFactory
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->repositoryFactory = $repositoryFactory;
    }

    /**
     * @return FromFieldsCollection
     * @throws GetresponseApiException
     * @throws RepositoryException
     */
    public function getAccountFromFields()
    {
        $service = new ContactListService($this->repositoryFactory->createGetResponseApiClient());
        return $service->getFromFields();
    }

    /**
     * @return array
     * @throws GetresponseApiException
     * @throws RepositoryException
     */
    public function getSubscriptionConfirmationsSubject()
    {
        $countryCode = $this->repository->getMagentoCountryCode();
        $lang = substr($countryCode, 0, 2);
        $apiClient = $this->repositoryFactory->createGetResponseApiClient();
        return $apiClient->getSubscriptionConfirmationSubject($lang);
    }

    /**
     * @return array
     * @throws GetresponseApiException
     * @throws RepositoryException
     */
    public function getSubscriptionConfirmationsBody()
    {
        $countryCode = $this->repository->getMagentoCountryCode();
        $lang = substr($countryCode, 0, 2);
        $apiClient = $this->repositoryFactory->createGetResponseApiClient();
        return $apiClient->getSubscriptionConfirmationBody($lang);
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
