<?php

/**
 * Class GetresponseIntegration_Getresponse_Model_Account
 */
class GetresponseIntegration_Getresponse_Model_Account extends Mage_Core_Model_Abstract
{
	protected function _construct()
	{
		parent::_construct();
		$this->_init('getresponse/account');
	}

    /**
     * Override for automaticly choosing integration by shop id
     *
     * @param $id
     * @param null $field
     *
     * @return mixed
     */
    public function load($id, $field = null)
    {
        if (is_null($field)) {
            $field = 'id_shop';
        }

        return parent::load($id, $field);
    }

    /**
	 * @param $account
	 * @param $shop_id
	 *
	 * @return bool
	 */
	public function updateAccount($account, $shop_id)
	{
		$data = array(
		    'id_shop' => $shop_id,
			'accountId' => isset($account->accountId) ? $account->accountId : null,
			'firstName' => isset($account->firstName) ? $account->firstName : null,
			'lastName' => isset($account->lastName) ? $account->lastName : null,
			'email' => isset($account->email) ? $account->email : null,
			'phone' => isset($account->phone) ? $account->phone : null,
			'state' => isset($account->state) ? $account->state : null,
			'city' => isset($account->city) ? $account->city : null,
			'street' => isset($account->street) ? $account->street : null,
			'zipCode' => isset($account->zipCode) ? $account->zipCode : null,
			'country' => isset($account->countryCode->countryCode) ? $account->countryCode->countryCode : null,
			'numberOfEmployees' => isset($account->numberOfEmployees) ? $account->numberOfEmployees : null,
			'timeFormat' => isset($account->timeFormat) ? $account->timeFormat : null,
			'timeZone_name' => isset($account->timeZone->name) ? $account->timeZone->name : null,
			'timeZone_offest' => isset($account->timeZone->offset) ? $account->timeZone->offset : null,
		);
		$model = $this->load($shop_id)->addData($data);

		try {
			$model->save();
		} catch (Exception $e) {
			return false;
		}

		return true;
	}

	/**
	 *
	 */
	public function disconnectAccount($shop_id)
	{
		$data = new stdClass();
		$this->updateAccount($data, $shop_id);
	}

}