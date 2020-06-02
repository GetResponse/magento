<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Block\Admin;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomField\CustomFieldService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration\SubscribeViaRegistration;
use GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration\SubscribeViaRegistrationFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GetResponse\GetResponseIntegration\Helper\Route;
use GrShareCode\Api\Exception\GetresponseApiException;
use GrShareCode\ContactList\ContactListCollection;
use GrShareCode\ContactList\ContactListService;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template\Context;

class Registration extends AdminTemplate
{
    use CustomFieldsTrait;
    use AutoresponderTrait;

    private $repository;
    protected $serializer;

    public function __construct(
        Context $context,
        ApiClientFactory $apiClientFactory,
        Repository $repository,
        CustomFieldService $customFieldService,
        CustomFieldsMappingService $customFieldsMappingService,
        MagentoStore $magentoStore,
        SerializerInterface $serializer
    ) {
        parent::__construct($context, $magentoStore);

        $this->customFieldService = $customFieldService;
        $this->customFieldsMappingService = $customFieldsMappingService;
        $this->serializer = $serializer;
        $this->repository = $repository;
        $this->routePrefix = Route::REGISTRATION_INDEX_ROUTE;
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

    public function getRegistrationSettings(): SubscribeViaRegistration
    {
        return SubscribeViaRegistrationFactory::createFromArray(
            $this->repository->getRegistrationSettings(
                $this->getScope()->getScopeId()
            )
        );
    }
}
