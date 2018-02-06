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

    private static $reservedCustoms = array('firstname', 'lastname', 'email');

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
                    && !in_array($cf['custom_field'], self::$reservedCustoms)
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
        $customFieldsCollectionRepository = new CustomFieldsCollectionRepository($shopId);
        $customFieldsCollectionRepository->delete();
    }
}