<?php

use GetresponseIntegration_Getresponse_Domain_GetresponseOrderBuilder as GrOrderBuilder;
use GetresponseIntegration_Getresponse_Domain_GetresponseCartBuilder as GrCartBuilder;
use GetresponseIntegration_Getresponse_Domain_GetresponseProductHandler as GrProductHandler;

/**
 * Class GetresponseIntegration_Getresponse_Domain_GetresponseCustomerHandler
 */
class GetresponseIntegration_Getresponse_Domain_GetresponseCustomerHandler
{
    /** @var GetresponseIntegration_Getresponse_Helper_Api */
    private $api;

    /** @var GetresponseIntegration_Getresponse_Model_Customs  */
    private $customsModel;

    public function __construct()
    {
        $this->api = Mage::helper('getresponse/api');
        $this->customsModel = Mage::getModel('getresponse/customs');
    }

    /**
     * @param string $campaignId
     * @param int $cycleDay
     * @param array $grCustomFields
     * @param array $custom_fields
     * @param string $email
     */
    public function sendCustomerToGetResponse(
        $campaignId,
        $cycleDay,
        $grCustomFields,
        $custom_fields,
        $email
    )
    {
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = $this->findCustomerByEmail($email);

        if (!empty($customer)) {
            $name = $customer->getName();
        } else {
            $name = null;
        }

        $this->api->addContact(
            $campaignId,
            $name,
            $email,
            $cycleDay,
            $this->customsModel->mapExportCustoms(array_flip($custom_fields), $customer),
            $grCustomFields
        );
    }

    /**
     * @param string $email
     *
     * @return Mage_Customer_Model_Customer|null
     */
    private function findCustomerByEmail($email)
    {
        return Mage::getResourceModel('customer/customer_collection')
            ->addAttributeToSelect('email')
            ->addAttributeToSelect('firstname')
            ->addAttributeToSelect('lastname')
            ->joinAttribute('street', 'customer_address/street', 'default_billing', null, 'left')
            ->joinAttribute('postcode', 'customer_address/city', 'default_billing', null, 'left')
            ->joinAttribute('city', 'customer_address/postcode', 'default_billing', null, 'left')
            ->joinAttribute('telephone', 'customer_address/telephone', 'default_billing', null, 'left')
            ->joinAttribute('country', 'customer_address/country_id', 'default_billing', null, 'left')
            ->joinAttribute('company', 'customer_address/company', 'default_billing', null, 'left')
            ->joinAttribute('birthday', 'customer/dob', 'entity_id', null, 'left')
            ->addFieldToFilter([
                ['attribute' => 'email', 'eq' => $email]
            ])->getFirstItem();
    }

    /**
     * @param int $subscriberId
     * @param string $campaignId
     * @param string $store_id
     * @param string $email
     */
    private function exportSubscriberEcommerceDetails(
        $subscriberId,
        $campaignId,
        $store_id,
        $email
    ) {
        $orderBuilder = new GrOrderBuilder($this->api, $store_id);
        $cartBuilder = new GrCartBuilder($this->api, $store_id);
        $productBuilder = new GrProductHandler($this->api, $store_id);

        /** @var Mage_Sales_Model_Resource_Order_Collection $orders */
        $orders = $this->getCustomerOrderCollection($subscriberId);

        if (0 === $orders->count()) {
            return;
        }

        $subscriber = $this->api->getContact(
            $email,
            $campaignId
        );

        if (!isset($subscriber->contactId)) {
            Mage::log('Subscriber not found during export - ' . $subscriber->email);
            return;
        }

        /** @var Mage_Sales_Model_Order $order */
        foreach ($orders as $order) {

            $gr_products = [];

            /** @var Mage_Sales_Model_Order_Item $product */
            foreach ($order->getAllItems() as $product) {
                $gr_products[$product->getProduct()->getId()] = $productBuilder->createGetresponseProduct($product);
            }

            $gr_cart = $cartBuilder->buildGetresponseCart(
                $subscriber->contactId,
                $order,
                $gr_products
            );

            if (!isset($gr_cart['cartId'])) {
                Mage::log('Cart not created', 1, 'getresponse.log');
                continue;
            }

            $orderBuilder->createGetresponseOrder(
                $subscriber->contactId,
                $order,
                $gr_cart['cartId'],
                $gr_products
            );
        }
    }

    /**
     * @param int  $customerId
     *
     * @return Mage_Sales_Model_Resource_Order_Collection
     */
    private function getCustomerOrderCollection($customerId)
    {
        $orderCollection = Mage::getResourceModel('sales/order_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', $customerId)
            ->setOrder('created_at', 'desc');

        return $orderCollection;
    }

}
