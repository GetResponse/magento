<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\MagentoCustomerAttribute;

use Exception;
use GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\CustomFieldsMapping;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Customer\Model\Customer;

/**
 * Class MagentoCustomerAttributeService
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\CustomFieldsMapping\MagentoCustomerAttribute
 */
class MagentoCustomerAttributeService
{
    /**
     * @param CustomFieldsMapping $customFieldMapping
     * @param Customer $customer
     * @return null|string
     */
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

    /**
     * @param Customer $customer
     * @param string $attributeCode
     * @return mixed
     */
    private function getCustomerAttributeValueByCode(Customer $customer, $attributeCode)
    {
        try {
            /** @var Attribute $customerAttribute */
            $customerAttribute = $customer->getAttribute($attributeCode);

            if (!$customerAttribute) {
                return null;
            }

            return $customerAttribute->getFrontend()->getValue($customer);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @param Customer $customer
     * @param string $attributeCode
     * @return mixed
     */
    private function getAddressAttributeValueByCode(Customer $customer, $attributeCode)
    {
        try {

            $customerAddress = $customer->getDefaultShippingAddress();

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