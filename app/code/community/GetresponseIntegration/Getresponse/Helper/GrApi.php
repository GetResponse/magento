<?php

use GetresponseIntegration_Getresponse_Domain_GetresponseException as GetresponseException;

/**
 * Class GetresponseIntegration_Getresponse_Helper_GrApi
 */
class GetresponseIntegration_Getresponse_Helper_GrApi
{
	/** @var string */
	public $api_key = '';

	/** @var string */
	public $api_url_360_com = 'https://api3.getresponse360.com/v3';

	/** @var string */
	public $api_url_360_pl = 'https://api3.getresponse360.pl/v3';

	/** @var string */
	public $api_url = 'https://api.getresponse.com/v3';

	/** @var null */
	public $domain = null;

	/** @var int */
	public $timeout = 8;

	/** @var */
	public $ping;

	/** @var bool */
	public $status = true;

	/** @var array */
	private $headers;

    /**
     * @var array
     * 1014 - Problem during authentication process
     * 1018 - Your IP was blocked
     * 1017 - Suspected behaviour, API was permanently blocked, please contact with our support
     */
	private $unauthorizedResponseCodes = array(1014, 1018, 1017);

    /**
     * Check, if api works properly.
     *
     * @param string $url
     * @param string $domain
     *
     * @return array
     */
	public function checkApi($url = '', $domain = '')
	{
		if ( !empty($url)) {
			$this->api_url = $url;
		}
		if ( !empty($domain)) {
			$this->domain = $domain;
		}

		try {
            return $this->call('accounts');
        } catch(Exception $e) {
		    return array();
        }
	}

	/**
	 * We can modify internal settings.
	 *
	 * @param string $key   key.
	 * @param string $value value.
	 */
	public function __set($key, $value)
	{
		$this->{$key} = $value;
	}

    /**
     * Return features list
     *
     * @return array
     * @throws GetresponseException
     */
	public function getFeatures()
	{
		return $this->call('accounts/features');
	}

    /**
     * Return features list
     *
     * @return array
     * @throws GetresponseException
     */
	public function getTrackingCode()
	{
		return $this->call('tracking');
	}

    /**
     * @param array $params
     *
     * @return array
     * @throws GetresponseException
     */
    public function getCampaigns($params)
    {
        return $this->call('campaigns?' . $this->setParams($params), 'GET', array(), true);
    }

    /**
     * @param array $params
     *
     * @return array
     * @throws GetresponseException
     */
	public function createCampaign($params)
	{
		return $this->call('campaigns', 'POST', $params);
	}

    /**
     * add single contact into your campaign
     *
     * @param $params
     *
     * @return array
     * @throws GetresponseException
     */
	public function addContact($params)
	{
		return $this->call('contacts', 'POST', $params);
	}

    /**
     * @param array $params
     *
     * @return array
     * @throws GetresponseException
     */
	public function getContacts($params = array())
	{
		return $this->call('contacts?' . $this->setParams($params));
	}

    /**
     * @param string $contact_id
     * @param array  $params
     *
     * @return array
     * @throws GetresponseException
     */
	public function updateContact($contact_id, $params = array())
	{
		return $this->call('contacts/' . $contact_id, 'POST', $params);
	}

    /**
     * @param array $params
     *
     * @return array
     * @throws GetresponseException
     */
	public function getAccountFromFields($params = array())
	{
		return $this->call('from-fields?' . $this->setParams($params), 'GET', array(), true);
	}

    /**
     * @param string $language
     *
     * @return array
     * @throws GetresponseException
     */
	public function getSubscriptionConfirmationsSubject($language = 'EN')
	{
		return $this->call('subscription-confirmations/subject/' . $language, 'GET', array(), true);
	}

    /**
     * @param string $language
     *
     * @return array
     * @throws GetresponseException
     */
	public function getSubscriptionConfirmationsBody($language = 'EN')
	{
		return $this->call('subscription-confirmations/body/' . $language);
	}

    /**
     * @param array $params
     *
     * @return array
     * @throws GetresponseException
     */
	public function addCustomField($params = array())
	{
		return $this->call('custom-fields', 'POST', $params);
	}

    /**
     * @param array $params
     *
     * @return array
     * @throws GetresponseException
     */
	public function getCustomFields($params = array())
	{
		return $this->call('custom-fields?' . $this->setParams($params), 'GET', array(), true);
	}

    /**
     * @param string $webformId
     *
     * @return array
     * @throws GetresponseException
     */
	public function getWebFormById($webformId)
	{
		return $this->call('webforms/' . $webformId);
	}

    /**
     * @param array $params
     *
     * @return array
     * @throws GetresponseException
     */
	public function getWebForms($params = array())
	{
		return $this->call('webforms?' . $this->setParams($params));
	}

    /**
     * @param string $formId
     *
     * @return array
     * @throws GetresponseException
     */
	public function getFormById($formId)
	{
		return $this->call('forms/' . $formId);
	}

    /**
     * @param array $params
     *
     * @return array
     * @throws GetresponseException
     */
	public function getForms($params = array())
	{
		return $this->call('forms?' . $this->setParams($params), 'GET', array(), true);
	}

    /**
     * @param array $params
     *
     * @return array
     * @throws GetresponseException
     */
	public function getAutoResponders($params)
	{
		return $this->call('autoresponders?' . $this->setParams($params), 'GET', array(), true);
	}

    /**
     * @param array $params
     *
     * @return array
     * @throws GetresponseException
     */
    public function getShops($params)
    {
        return $this->call('shops?' . $this->setParams($params), 'GET', array(), true);
	}

    /**
     * @param array $params
     *
     * @return array
     * @throws GetresponseException
     */
    public function addShop($params)
    {
        return $this->call('shops', 'POST', $params);
    }

    /**
     * @param string $shopId
     * @param array  $params
     *
     * @return array
     * @throws GetresponseException
     */
    public function addCart($shopId, $params)
    {
        return $this->call('shops/' . $shopId . '/carts', 'POST', $params);
    }

    /**
     * @param string $shopId
     * @param string $cartId
     * @param array $params
     *
     * @return array
     * @throws GetresponseException
     */
    public function updateCart($shopId, $cartId, $params)
    {
        return $this->call('shops/' . $shopId . '/carts/' . $cartId, 'POST', $params);
    }

    /**
     * @param string $shopId
     * @param string $cartId
     *
     * @return array
     * @throws GetresponseException
     */
    public function deleteCart($shopId, $cartId)
    {
        return $this->call('shops/' . $shopId . '/carts/' . $cartId, 'DELETE');
    }

    /**
     * @param string $shopId
     * @param string $productId
     *
     * @return array
     * @throws GetresponseException
     */
    public function getProductById($shopId, $productId)
    {
        return $this->call('shops/' . $shopId . '/products/' . $productId);
    }

    /**
     * @param string $shopId
     * @param array  $params
     *
     * @return array
     * @throws GetresponseException
     */
    public function addProduct($shopId, $params)
    {
        return $this->call('shops/' . $shopId . '/products', 'POST', $params);
    }

    /**
     * @param string $shopId
     * @param array  $params
     *
     * @return array
     * @throws GetresponseException
     */
    public function createOrder($shopId, $params)
    {
        return $this->call('shops/' . $shopId.'/orders?additionalFlags=skipAutomation', 'POST', $params);
    }

    /**
     * @param string $shopId
     * @param string $orderId
     * @param array  $params
     *
     * @return array
     * @throws GetresponseException
     */
    public function updateOrder($shopId, $orderId, $params)
    {
        return $this->call('shops/' . $shopId . '/orders/' . $orderId . '?additionalFlags=skipAutomation', 'POST', $params);
    }

    /**
     * @param string $shopId
     *
     * @return array
     * @throws GetresponseException
     */
    public function deleteShop($shopId)
    {
        return $this->call('shops/' . $shopId, 'DELETE');
    }

    /**
     * @param string $api_method
     * @param string $http_method
     * @param array  $params
     * @param bool   $withHeaders
     *
     * @return array
     * @throws GetresponseIntegration_Getresponse_Domain_GetresponseException
     */
	protected function call($api_method = null, $http_method = 'GET', $params = array(), $withHeaders = false)
	{
	    /** @var GetresponseIntegration_Getresponse_Helper_Logger $logger */
	    $logger = Mage::helper('getresponse/logger');

	    /** @var GetresponseIntegration_Getresponse_Helper_Data $grHelper */
	    $grHelper = Mage::helper('getresponse');

		if (empty($api_method)) {
			return array(
				'httpStatus' => '400',
				'code' => '1010',
				'codeDescription' => 'Error in external resources',
				'message' => 'Invalid api method'
			);
		}

		$params = json_encode($params);
		$url = $this->api_url . '/' . $api_method;

		$headers = array(
			'X-Auth-Token: api-key ' . $this->api_key,
			'Content-Type: application/json',
			'User-Agent: Getresponse Plugin '. $grHelper->getExtensionVersion(),
			'X-APP-ID: 71609442-d357-47ea-8c7e-ebc36a6b1a7d'
		);

		// for GetResponse 360
		if (isset($this->domain)) {
			$headers[] = 'X-Domain: ' . $this->domain;
		}

		//also as get method
		$options = array(
			CURLOPT_URL            => $url,
			CURLOPT_ENCODING       => 'gzip,deflate',
			CURLOPT_FRESH_CONNECT  => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_TIMEOUT        => $this->timeout,
			CURLOPT_HEADER         => $withHeaders,
			CURLOPT_HTTPHEADER     => $headers
		);

		if ($http_method == 'POST') {
			$options[CURLOPT_POST] = 1;
			$options[CURLOPT_POSTFIELDS] = $params;
		}
		else {
			if ($http_method == 'DELETE') {
				$options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
			}
		}

		try {
            $curl = curl_init();
            curl_setopt_array($curl, $options);
            $response = curl_exec($curl);
            curl_close($curl);

            if ($withHeaders) {
                list($headers, $response) = explode("\r\n\r\n", $response, 2);
                $this->headers = $this->prepareHeaders($headers);
            }

            $response = json_decode($response, true);
        } catch (Exception $e) {
            $logger->logException($e);
            return array();
        }


        if (!empty($response['codeDescription'])) {
            $logger->log(
                sprintf('API: %s %s method failed: %s (%s)', $http_method, $api_method, $response['codeDescription'],
                    $response['code'])
            );
        } else {
            $logger->log(
                sprintf('API: %s %s method successed', $http_method, $api_method)
            );
        }

        if (isset($response['httpStatus']) && (int) $response['httpStatus'] >= 400 && (int) $response['httpStatus'] < 500) {
            if (isset($response['code']) && in_array($response['code'], $this->unauthorizedResponseCodes)) {
                try {
                    $grHelper->handleUnauthorizedApiCall();
                } catch (Mage_Core_Model_Store_Exception $e) {
                } catch (Varien_Exception $e) {
                }
            }

            throw new GetresponseException($response['message'], $response['code']);
        } else {
            $grHelper->resetUnauthorizedApiCallDate();
        }

        return (array) $response;
	}

	/**
	 * @param array $params
	 *
	 * @return string
	 */
	protected function setParams($params = array())
	{
		$result = array();
		if (is_array($params)) {
			foreach ($params as $key => $value) {
				$result[$key] = $value;
			}
		}

		return http_build_query($result);
	}

    /**
     * @param string $headers
     * @return array
     */
    private function prepareHeaders( $headers ) {
        $headers = explode("\n", $headers);
        foreach ($headers as $header) {
            $params = explode(':', $header, 2);
            $key = isset($params[0]) ? $params[0] : null;
            $value = isset($params[1]) ? $params[1] : null;
            $headers[trim($key)] = trim($value);
        }
        return $headers;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }
}
