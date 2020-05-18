<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Block\Admin;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\NewsletterSettings;
use GetResponse\GetResponseIntegration\Domain\Magento\NewsletterSettingsFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GrShareCode\Api\Exception\GetresponseApiException;
use GrShareCode\ContactList\ContactListCollection;
use GrShareCode\ContactList\ContactListService;
use Magento\Framework\View\Element\Template\Context;

class Newsletter extends AdminTemplate
{
    private $repository;

    public function __construct(
        Context $context,
        ApiClientFactory $apiClientFactory,
        Repository $repository,
        MagentoStore $magentoStore
    ) {
        parent::__construct($context, $magentoStore);
        $this->repository = $repository;
        $this->apiClient =  $apiClientFactory->createGetResponseApiClient($this->getScope());
    }

    /**
     * @return ContactListCollection
     * @throws GetresponseApiException
     */
    public function getLists(): ContactListCollection
    {
        return (new ContactListService($this->apiClient))->getAllContactLists();
    }

    public function getNewsletterSettings(): NewsletterSettings
    {
        return NewsletterSettingsFactory::createFromArray(
            $this->repository->getNewsletterSettings($this->getScope()->getScopeId())
        );
    }
}
