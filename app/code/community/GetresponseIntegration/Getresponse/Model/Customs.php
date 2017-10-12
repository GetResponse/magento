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
     * @param $shop_id
     *
     * @return mixed
     */
    public function getCustoms($shop_id)
    {
        return $this->getCollection()->addFieldToFilter('id_shop', $shop_id)->getData();
    }

    /**
     * @param int $shop_id
     */
    public function connectCustoms($shop_id) {

        if (!empty($this->getCustoms($shop_id))) {
            return;
        }

        foreach ($this->fields as $field) {
            $custom = Mage::getModel('getresponse/customs');
            $custom->setData(array(
                'id_shop' => $shop_id,
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
     * @param $params
     * @param $customer
     *
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
     * @param $user_customs
     * @param $db_customs
     *
     * @return array
     */
    public static function mapCustoms($user_customs, $db_customs)
    {
        $fields = array();
        if ( !empty($user_customs) && !empty($db_customs)) {

            foreach ($db_customs as $cf) {
                if (
                    in_array($cf['custom_field'], array_keys($user_customs))
                    && !empty($user_customs[$cf['custom_field']])
                    && !in_array($cf['custom_field'], self::RESERVED_CUSTOM_FIELDS)
                ) {
                    $fields[$cf['custom_value']] = trim(preg_replace('/\s+/', ' ', $user_customs[$cf['custom_field']]));
                }
            }
        }
        return $fields;
    }

    /**
     *
     */
    public function disconnectCustoms($shop_id)
    {
        $customs = $this->getCustoms($shop_id);
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