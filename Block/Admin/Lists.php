<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Block\Admin;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Store\ReadModel\StoreReadModel;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GrShareCode\Api\Exception\GetresponseApiException;
use GrShareCode\ContactList\ContactListService;
use GrShareCode\ContactList\FromFieldsCollection;
use Magento\Framework\View\Element\Template\Context;

class Lists extends AdminTemplate
{
    private $storeReadModel;

    public function __construct(
        Context $context,
        ApiClientFactory $apiClientFactory,
        MagentoStore $magentoStore,
        StoreReadModel $storeReadModel
    ) {
        parent::__construct($context, $magentoStore);

        $this->storeReadModel = $storeReadModel;
        $this->apiClient =  $apiClientFactory->createGetResponseApiClient(
            new Scope($this->getScopeId())
        );
    }

    /**
     * @return FromFieldsCollection
     * @throws GetresponseApiException
     */
    public function getAccountFromFields(): FromFieldsCollection
    {
        return (new ContactListService($this->apiClient))->getFromFields();
    }

    /**
     * @return array
     * @throws GetresponseApiException
     */
    public function getSubscriptionConfirmationsSubject(): array
    {
        return $this->apiClient->getSubscriptionConfirmationSubject(
            $this->storeReadModel->getStoreLanguage(new Scope($this->getScopeId()))
        );
    }

    /**
     * @return array
     * @throws GetresponseApiException
     */
    public function getSubscriptionConfirmationsBody(): array
    {
        return $this->apiClient->getSubscriptionConfirmationBody(
            $this->storeReadModel->getStoreLanguage(new Scope($this->getScopeId()))
        );
    }

    public function getBackUrl($backUrl = null): string
    {
        if (null === $backUrl) {
            $backUrl = $this->getRequest()->getParam('back');
        }

        return $this->createBackUrl($backUrl);
    }

    private function createBackUrl($back): string
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
