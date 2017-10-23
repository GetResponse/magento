<?php

require_once Mage::getModuleDir('controllers',
        'GetresponseIntegration_Getresponse') . DIRECTORY_SEPARATOR . 'BaseController.php';

class GetresponseIntegration_Getresponse_ExportController extends GetresponseIntegration_Getresponse_BaseController
{

    /**
     * GET getresponse/index/export
     */
    public function indexAction()
    {
        $this->_initAction();
        $this->_title($this->__('Export customers'))->_title($this->__('GetResponse'));

        $this->prepareCustomsForMapping();

        $this->_addContent($this->getLayout()
            ->createBlock('Mage_Core_Block_Template', 'getresponse_content')
            ->setTemplate('getresponse/export.phtml')
            ->assign('campaign_days', $this->api->getCampaignDays())
            ->assign('campaigns', $this->api->getGrCampaigns())
            ->assign('customs', $this->prepareCustomsForMapping())
        );

        $this->renderLayout();
    }

    /**
     * POST getresponse/export/run
     */
    public function runAction()
    {
        $this->_initAction();

        $campaign_id = $this->getRequest()->getParam('campaign_id');
        if (empty($campaign_id)) {
            $this->_getSession()->addError('List can\'t be empty');
            $this->_redirect('*/*/index');
            return;
        }

        $this->exportCustomers($campaign_id, $this->getRequest()->getParams());
        $this->_redirect('*/*/index');
    }

    /**
     * @param $campaign_id
     * @param $params
     *
     * @return bool
     */
    protected function exportCustomers($campaign_id, $params)
    {
        $subscribers = Mage::helper('getresponse')->getNewsletterSubscribersCollection();

        $cycleDay = '';
        if (isset($params['gr_autoresponder']) && 1 == $params['gr_autoresponder']) {
            $cycleDay = (int)$params['cycleDay'];
        }

        $custom_fields = $this->prepareCustomFields(
            isset($params['gr_custom_field']) ? $params['gr_custom_field'] : [],
            isset($params['custom_field']) ? $params['custom_field'] : []
        );

        if (!empty($params['gr_custom_field'])) {
            foreach ($params['gr_custom_field'] as $field_key => $field_value) {
                if (false == preg_match('/^[_a-zA-Z0-9]{2,32}$/m', ($field_value))) {
                    $this->_getSession()->addError('Incorrect field name: ' . $field_key . '.');
                    return false;
                }
            }
        }

        $reports = [
            'created' => 0,
            'updated' => 0,
            'error' => 0,
        ];

        if (!empty($subscribers)) {
            foreach ($subscribers as $subscriber) {
                $customer = Mage::getResourceModel('customer/customer_collection')
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
                        ['attribute' => 'email', 'eq' => $subscriber->getEmail()]
                    ])->getFirstItem();

                if (!empty($customer)) {
                    $name = $customer->getName();
                } else {
                    $name = null;
                }
                $result = Mage::helper('getresponse/api')->addContact(
                    $campaign_id,
                    $name,
                    $subscriber->getEmail(),
                    $cycleDay,
                    Mage::getModel('getresponse/customs')->mapExportCustoms($custom_fields, $customer)
                );

                if (GetresponseIntegration_Getresponse_Helper_Api::CONTACT_CREATED === $result) {
                    $reports['created']++;
                } elseif (GetresponseIntegration_Getresponse_Helper_Api::CONTACT_UPDATED == $result) {
                    $reports['updated']++;
                } else {
                    $reports['error']++;
                }
            }
        }

        $flashMessage = 'Customer data exported';

        $this->_getSession()->addSuccess($flashMessage);

        return true;
    }

    private function prepareCustomFields($grCustomFields, $customFields)
    {
        $fields = [];

        foreach ($grCustomFields as $id => $name) {
            $fields[$name] = isset($customFields[$id]) ? $customFields[$id] : null;
        }

        return $fields;
    }

}