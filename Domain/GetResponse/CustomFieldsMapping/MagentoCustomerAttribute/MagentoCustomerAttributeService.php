<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\MagentoCustomerAttribute;

use Exception;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMapping;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
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
        return isset($params[$attributeCode]) ? $params[$attributeCode] : null;
    }

    private function getAddressAttributeValueByCode(Customer $customer, $attributeCode)
    {
        try {

            $customerAddress = $customer->getDefaultShipping();

            if (!$customerAddress) {
                return null;
            }

            /** @var Attribute $customerAttribute */
            $customerAddressAttribute = $customerAddress->getAttributes()[$attributeCode];

            if (!$customerAddressAttribute) {
                return null;
            }

            return $customerAddress->getData($attributeCode);
        } catch (Exception $e) {
            return null;
        }
    }
}
