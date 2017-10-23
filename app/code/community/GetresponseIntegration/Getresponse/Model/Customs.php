<?php

/**
 * Class GetresponseIntegration_Getresponse_Model_Customs
 */
class GetresponseIntegration_Getresponse_Model_Customs extends Mage_Core_Model_Abstract
{
    const ACTIVE = 1;
    const INACTIVE = 0;

    const RESERVED_CUSTOM_FIELDS = array('firstname', 'lastname', 'email');

    public $fields = array(
        array('name' => 'firstname', 'value' => self::ACTIVE),
        array('name' => 'lastname', 'value' => self::ACTIVE),
        array('name' => 'email', 'value' => self::ACTIVE),
        array('name' => 'street', 'value' => self::INACTIVE),
        array('name' => 'postcode', 'value' => self::INACTIVE),
        array('name' => 'city', 'value' => self::INACTIVE),
        array('name' => 'telephone', 'value' => self::INACTIVE),
        array('name' => 'country', 'value' => self::INACTIVE),
        array('name' => 'birthday', 'value' => self::INACTIVE),
        array('name' => 'company', 'value' => self::INACTIVE),
    );

    protected function _construct()
    {
        parent::_construct();
        $this->_init('getresponse/customs');
    }

    /**
     * @param $shopId
     * @return mixed
     */
    public function getCustoms($shopId)
    {
        return $this->getCollection()->addFieldToFilter('id_shop', $shopId)->getData();
    }

    /**
     * @param int $shopId
     */
    public function connectCustoms($shopId) {

        if (!empty($this->getCustoms($shopId))) {
            return;
        }

        foreach ($this->fields as $field) {
            $custom = Mage::getModel('getresponse/customs');
            $custom->setData(array(
                'id_shop' => $shopId,
                'custom_field' => $field['name'],
                'custom_value' => $field['name'],
                'default' => $field['value'],
                'active_custom' => $field['value']
            ));
            $custom->save();
        }
    }

    /**
     * @param $id
     * @param $data
     *
     * @return bool
     */
    public function updateCustom($id, $data)
    {
        $model = $this->load($id)->addData($data);

        try {
            $model->save();
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param array $params
     * @param $customer
     * @return array
     */
    public static function mapExportCustoms($params, $customer)
    {
        $fields = array();
        if ( !empty($params) && !empty($customer)) {

            $customer_data = $customer->getData();

            foreach ($params as $key => $val) {
                if (in_array($key, array_keys($customer_data)) && !empty($customer_data[$key])) {
                    $fields[$val] = trim(preg_replace('/\s+/', ' ', $customer_data[$key]));
                }
            }
        }

        return $fields;
    }

    /**
     * @param array $userCustoms
     * @param array $dbCustoms
     * @return array
     */
    public static function mapCustoms($userCustoms, $dbCustoms)
    {
        $fields = array();
        if ( !empty($userCustoms) && !empty($dbCustoms)) {

            foreach ($dbCustoms as $cf) {
                if (
                    in_array($cf['custom_field'], array_keys($userCustoms))
                    && !empty($userCustoms[$cf['custom_field']])
                    && !in_array($cf['custom_field'], self::RESERVED_CUSTOM_FIELDS)
                ) {
                    $fields[$cf['custom_value']] = trim(preg_replace('/\s+/', ' ', $userCustoms[$cf['custom_field']]));
                }
            }
        }
        return $fields;
    }

    /**
     * @param int $shopId
     */
    public function disconnect($shopId)
    {
        $customs = $this->getCustoms($shopId);
        if ( !empty($customs)) {
            foreach ($customs as $custom) {
                $data = array(
                    'custom_value' => $custom['custom_field'],
                    'active_custom' => 0
                );
                $this->updateCustom($custom['id_custom'], $data);
            }
        }
    }
}