<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Block\Admin;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiClientFactory;
use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomField\CustomFieldService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingService;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\MagentoCustomerAttribute\MagentoCustomerAttributeCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration\SubscribeViaRegistration;
use GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration\SubscribeViaRegistrationFactory;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Helper\MagentoStore;
use GrShareCode\Api\Exception\GetresponseApiException;
use GrShareCode\ContactList\ContactListCollection;
use GrShareCode\ContactList\ContactListService;
use Magento\Framework\View\Element\Template\Context;

class Registration extends AdminTemplate
{
    private $customFieldService;
    private $customFieldsMappingService;
    private $repository;

    public function __construct(
        Context $context,
        ApiClientFactory $apiClientFactory,
        Repository $repository,
        CustomFieldService $customFieldService,
        CustomFieldsMappingService $customFieldsMappingService,
        MagentoStore $magentoStore
    ) {
        parent::__construct($context, $magentoStore);

        $this->customFieldService = $customFieldService;
        $this->customFieldsMappingService = $customFieldsMappingService;
        $this->repository = $repository;
        $this->apiClient =  $apiClientFactory->createGetResponseApiClient($this->scope);
    }

    /**
     * @return ContactListCollection
     * @throws GetresponseApiException
     */
    public function getLists(): ContactListCollection
    {
        return (new ContactListService($this->apiClient))->getAllContactLists();
    }

    public function getCustomFieldsMapping(): CustomFieldsMappingCollection
    {
        return CustomFieldsMappingCollection::createFromRepository(
            $this->repository->getCustomFieldsMappingForRegistration(
                $this->scope->getScopeId()
            )
        );
    }

    public function getRegistrationSettings(): SubscribeViaRegistration
    {
        return SubscribeViaRegistrationFactory::createFromArray(
            $this->repository->getRegistrationSettings(
                $this->scope->getScopeId()
            )
        );
    }

    /**
     * @return array
     * @throws GetresponseApiException
     * @throws ApiException
     */
    public function getCustomFieldsFromGetResponse(): array
    {
        $result = [];

        $customFields = $this->customFieldService->getCustomFields($this->scope);

        foreach ($customFields as $customField) {
            $result[] = [
                'id' => $customField->getId(),
                'name' => $customField->getName(),
            ];
        }

        return $result;
    }

    public function getMagentoCustomerAttributes(): MagentoCustomerAttributeCollection
    {
        return $this->customFieldsMappingService->getMagentoCustomerAttributes();
    }
}
