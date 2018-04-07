<?php

/**
 * Class GetresponseIntegration_Getresponse_Domain_GetresponseCustomerHandler
 */
class GetresponseIntegration_Getresponse_Domain_GetresponseCustomerHandler
{
    /** @var GetresponseIntegration_Getresponse_Helper_Api */
    private $api;

    /** @var GetresponseIntegration_Getresponse_Model_Customs  */
    private $customsModel;

    /**
     * @param GetresponseIntegration_Getresponse_Helper_Api $api
     */
    public function __construct(GetresponseIntegration_Getresponse_Helper_Api $api)
    {
        $this->api = $api;
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

        $this->api->upsertContact(
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
            ->addFieldToFilter(
                array(
                array('attribute' => 'email', 'eq' => $email)
                )
            )->getFirstItem();
    }
}
