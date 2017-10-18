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
	 * @param $shopId
	 *
	 * @return bool
	 */
	public function updateAccount($account, $shopId)
	{
		$data = array(
		    'id_shop' => $shopId,
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
		$model = $this->load($shopId)->addData($data);

		try {
			$model->save();
		} catch (Exception $e) {
			return false;
		}

		return true;
	}

    /**
     * @param int $shopId
     */
	public function disconnect($shopId)
	{
		$data = new stdClass();
		$this->updateAccount($data, $shopId);
	}

}