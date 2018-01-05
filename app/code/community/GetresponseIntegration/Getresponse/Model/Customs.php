<?php
use GetresponseIntegration_Getresponse_Domain_CustomFieldsCollectionFactory as CustomFieldsCollectionFactory;
use GetresponseIntegration_Getresponse_Domain_CustomFieldsCollectionRepository as CustomFieldsCollectionRepository;
use GetresponseIntegration_Getresponse_Domain_CustomFieldFactory as CustomFieldFactory;

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
        $customFieldsCollectionRepository = new CustomFieldsCollectionRepository($shopId);
        $customFieldsCollection = $customFieldsCollectionRepository->getCollection();

        return $customFieldsCollection;
    }

    /**
     * @param int $shopId
     */
    public function connectCustoms($shopId)
    {
        if (!empty($this->getCustoms($shopId))) {
            return;
        }

        $customFieldsCollectionRepository = new CustomFieldsCollectionRepository($shopId);
        $customFieldsCollection = CustomFieldsCollectionFactory::createFromArray(array());

        foreach ($this->fields as $field) {
            $custom = CustomFieldFactory::createFromArray(array(
                'id' => substr(md5(rand()), 0, 5),
                'customField' => $field['name'],
                'customValue' => $field['name'],
                'isDefault' => $field['value'],
                'isActive' => $field['value']
                )
            );
            $customFieldsCollection->add($custom);
        }
        $customFieldsCollectionRepository->create($customFieldsCollection);
    }

    /**
     * @param $id
     * @param $data
     *
     * @return bool
     */
    public function updateCustom($id, $data, $shopId)
    {
        $customs = $this->getCustoms($shopId);
//        $index = array_search($id, array_column($customs, 'id_custom'));
//        $customs[$index] = array_replace($customs[$index], $data);

        foreach ($customs as $key => $custom) {
            if ($custom['id_custom'] === $id) {
                $custom['custom_active'] = $data['custom_active'];
            }
        }


        try {
            $customFieldsCollectionRepository = new CustomFieldsCollectionRepository($shopId);
            $customFieldsCollection = CustomFieldsCollectionFactory::createFromArray(array());
            foreach ($customs as $custom) {
                $customTemp = CustomFieldFactory::createFromArray(array(
                        'id' => $custom['id_custom'],
                        'customField' => $custom['custom_field'],
                        'customValue' => $custom['custom_value'],
                        'isDefault' => $custom['default'],
                        'isActive' => $custom['custom_active']
                    )
                );
                $customFieldsCollection->add($customTemp);
            };
            $customFieldsCollectionRepository->create($customFieldsCollection);
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
        if (!empty($params) && !empty($customer)) {

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
                    'custom_active' => 0
                );
                $this->updateCustom($custom['id_custom'], $data, $shopId);
            }
        }
    }
}