<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\MagentoCustomerAttribute;

use Exception;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMapping;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Data\Customer;

class MagentoCustomerAttributeService
{
    public function getAttributeValue(CustomFieldsMapping $customFieldMapping, Customer $customer)
    {
        $attributeCode = $customFieldMapping->getMagentoAttributeCode();

        if ($customFieldMapping->isTypeCustomer()) {
            return $this->getCustomerAttributeValueByCode($customer, $attributeCode);
        }

        if ($customFieldMapping->isTypeAddress()) {
            return $this->getAddressAttributeValueByCode($customer, $attributeCode);
        }

        return null;
    }

    private function getCustomerAttributeValueByCode(Customer $customer, $attributeCode)
    {
        $params = $customer->__toArray();
        return $params[$attributeCode] ?? null;
    }

    private function getAddressAttributeValueByCode(Customer $customer, $attributeCode): ?string
    {
        try {
            $customerAddress = [];
            if ($customer->getAddresses() !== null)
            {
                foreach ($customer->getAddresses() as $address) {
                    $customerAddress = $address->__toArray();
                }
            }

            $attribute = $customerAddress[$attributeCode] ?? null;

            return is_array($attribute) ? implode(' - ', $attribute) : $attribute;
        } catch (Exception $e) {
            return null;
        }
    }
}
