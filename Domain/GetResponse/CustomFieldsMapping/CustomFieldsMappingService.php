<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping;

use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\MagentoCustomerAttribute\MagentoCustomerAttribute;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\MagentoCustomerAttribute\MagentoCustomerAttributeCollection;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use Magento\Customer\Model\ResourceModel\Address\Attribute\CollectionFactory as AddressCollectionFactory;
use Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory;

class CustomFieldsMappingService
{
    const BLACKLISTED_ATTRIBUTE_CODES = [
        'store_id',
        'disable_auto_group_change'
    ];

    private $repository;
    private $customerAttributeCollectionFactory;
    private $addressCollectionFactory;

    public function __construct(
        Repository $repository,
        CollectionFactory $customerAttributeCollectionFactory,
        AddressCollectionFactory $addressAttributeCollectionFactory
    ) {
        $this->repository = $repository;
        $this->customerAttributeCollectionFactory = $customerAttributeCollectionFactory;
        $this->addressCollectionFactory = $addressAttributeCollectionFactory;
    }

    public function setDefaultCustomFields(Scope $scope)
    {
        $customFieldMappingCollection = CustomFieldsMappingCollection::createDefaults();
        $this->repository->setCustomsOnInit(
            $customFieldMappingCollection->toArray(),
            $scope->getScopeId()
        );
    }

    public function getMagentoCustomerAttributes(): MagentoCustomerAttributeCollection
    {
        $attributeCollection = new MagentoCustomerAttributeCollection();

        foreach ($this->customerAttributeCollectionFactory->create() as $attribute) {
            if (null === $attribute->getFrontendLabel()) {
                continue;
            }

            if (in_array($attribute->getAttributeCode(), self::BLACKLISTED_ATTRIBUTE_CODES, true)) {
                continue;
            }

            $attributeCollection->add(
                MagentoCustomerAttribute::createFromCustomerAttribute($attribute)
            );
        }

        foreach ($this->addressCollectionFactory->create() as $addressAttribute) {
            if (null === $addressAttribute->getFrontendLabel()) {
                continue;
            }

            $attribute = MagentoCustomerAttribute::createFromAddressAttribute($addressAttribute);
            $attributeCollection->add($attribute);
        }

        return $attributeCollection;
    }
}
