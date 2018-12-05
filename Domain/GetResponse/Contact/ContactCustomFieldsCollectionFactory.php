<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Contact;

use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMapping;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\MagentoCustomerAttribute\MagentoCustomerAttributeService;
use GrShareCode\Contact\ContactCustomField\ContactCustomField;
use GrShareCode\Contact\ContactCustomField\ContactCustomFieldsCollection;
use Magento\Customer\Model\Customer;

/**
 * Class ContactCustomFieldsCollectionFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\Contact
 */
class ContactCustomFieldsCollectionFactory
{
    /** @var MagentoCustomerAttributeService */
    private $magentoCustomerAttributeService;

    /**
     * @param MagentoCustomerAttributeService $magentoCustomerAttributeService
     */
    public function __construct(MagentoCustomerAttributeService $magentoCustomerAttributeService)
    {
        $this->magentoCustomerAttributeService = $magentoCustomerAttributeService;
    }

    /**
     * @param Customer $customer
     * @param CustomFieldsMappingCollection $customFieldsMappingCollection
     * @param bool $isUpdateCustomFieldEnabled
     * @return ContactCustomFieldsCollection
     */
    public function createForCustomer(
        Customer $customer,
        CustomFieldsMappingCollection $customFieldsMappingCollection,
        $isUpdateCustomFieldEnabled
    ) {
        $contactCustomFieldCollection = new ContactCustomFieldsCollection();

        if (!$isUpdateCustomFieldEnabled) {
            return $contactCustomFieldCollection;
        }

        /** @var CustomFieldsMapping $customFieldMapping */
        foreach ($customFieldsMappingCollection as $customFieldMapping) {

            if ($customFieldMapping->isDefault()) {
                continue;
            }

            $customFieldValue = $this->magentoCustomerAttributeService->getAttributeValue(
                $customFieldMapping,
                $customer
            );

            if (!$customFieldValue) {
                continue;
            }

            $customFieldId = $customFieldMapping->getGetResponseCustomId();

            $contactCustomFieldCollection->add(
                new ContactCustomField($customFieldId, [$customFieldValue])
            );
        }

        return $contactCustomFieldCollection;
    }

    /**
     * @return ContactCustomFieldsCollection
     */
    public function createForSubscriber()
    {
        return new ContactCustomFieldsCollection();
    }
}