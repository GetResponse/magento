<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Block\Admin;

use GetResponse\GetResponseIntegration\Domain\GetResponse\Api\ApiException;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\MagentoCustomerAttribute\MagentoCustomerAttributeCollection;
use GrShareCode\Api\Exception\GetresponseApiException;

trait CustomFieldsTrait
{
    private $customFieldService;
    private $customFieldsMappingService;

    /**
     * @return array
     * @throws GetresponseApiException
     * @throws ApiException
     */
    public function getCustomFieldsFromGetResponse(): array
    {
        $result = [];

        $customFields = $this->customFieldService->getCustomFields($this->getScope());

        foreach ($customFields as $customField) {
            $result[] = [
                'id' => $customField->getId(),
                'name' => $customField->getName(),
            ];
        }

        return $result;
    }

    /**
     * @return string
     * @throws GetresponseApiException
     * @throws ApiException
     */
    public function getSerializedCustomFieldsFromGetResponse(): string
    {
        return $this->escapeHtml($this->serializer->serialize($this->getCustomFieldsFromGetResponse()));
    }

    public function getCustomFieldsMapping(): CustomFieldsMappingCollection
    {
        return CustomFieldsMappingCollection::createFromRepository(
            $this->repository->getCustomFieldsMappingForRegistration(
                $this->getScope()->getScopeId()
            )
        );
    }

    public function getMagentoCustomerAttributes(): MagentoCustomerAttributeCollection
    {
        return $this->customFieldsMappingService->getMagentoCustomerAttributes();
    }

    public function getSerializedMagentoCustomerAttributes(): string
    {
        return $this->serializer->serialize($this->getMagentoCustomerAttributes()->toArray());
    }
}
