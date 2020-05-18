<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Contact;

use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMappingCollection;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\MagentoCustomerAttribute\MagentoCustomerAttributeService;
use GrShareCode\Contact\ContactCustomField\ContactCustomField;
use GrShareCode\Contact\ContactCustomField\ContactCustomFieldsCollection;
use Magento\Customer\Model\Customer;

class ContactCustomFieldsCollectionFactory
{
    private $magentoCustomerAttributeService;

    public function __construct(
        MagentoCustomerAttributeService $magentoCustomerAttributeService
    ) {
        $this->magentoCustomerAttributeService = $magentoCustomerAttributeService;
    }

    public function createForCustomer(
        Customer $customer,
        CustomFieldsMappingCollection $customFieldsMappingCollection,
        $isUpdateCustomFieldEnabled
    ): ContactCustomFieldsCollection {
        $contactCustomFieldCollection = new ContactCustomFieldsCollection();

        if (!$isUpdateCustomFieldEnabled) {
            return $contactCustomFieldCollection;
        }

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

    public function createForSubscriber(): ContactCustomFieldsCollection
    {
        return new ContactCustomFieldsCollection();
    }
}
