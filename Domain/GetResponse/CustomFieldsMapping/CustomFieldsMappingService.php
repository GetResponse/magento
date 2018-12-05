<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping;

use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\MagentoCustomerAttribute\MagentoCustomerAttribute;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\MagentoCustomerAttribute\MagentoCustomerAttributeCollection;
use GetResponse\GetResponseIntegration\Domain\Magento\Repository;
use Magento\Customer\Model\ResourceModel\Address\Attribute\CollectionFactory as AddressCollectionFactory;
use Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute;

/**
 * Class CustomFieldsMappingService
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMapping
 */
class CustomFieldsMappingService
{
    const BLACKLISTED_ATTRIBUTE_CODES = [
        'store_id',
        'disable_auto_group_change'
    ];

    /** @var Repository */
    private $repository;

    /** @var CollectionFactory */
    private $customerAttributeCollectionFactory;

    /** @var AddressCollectionFactory */
    private $addressCollectionFactory;

    /**
     * @param Repository $repository
     * @param CollectionFactory $customerAttributeCollectionFactory
     * @param AddressCollectionFactory $addressAttributeCollectionFactory
     */
    public function __construct(
        Repository $repository,
        CollectionFactory $customerAttributeCollectionFactory,
        AddressCollectionFactory $addressAttributeCollectionFactory
    ) {
        $this->repository = $repository;
        $this->customerAttributeCollectionFactory = $customerAttributeCollectionFactory;
        $this->addressCollectionFactory = $addressAttributeCollectionFactory;
    }

    public function setDefaultCustomFields()
    {
        $customFieldMappingCollection = CustomFieldsMappingCollection::createDefaults();
        $this->repository->setCustomsOnInit($customFieldMappingCollection->toArray());
    }

    /**
     * @return MagentoCustomerAttributeCollection
     */
    public function getMagentoCustomerAttributes()
    {
        $attributeCollection = new MagentoCustomerAttributeCollection();

        /** @var $attribute Attribute */
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

        /** @var Attribute $addressAttribute */
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