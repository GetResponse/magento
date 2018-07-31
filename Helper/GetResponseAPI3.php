<?php
namespace GetResponse\GetResponseIntegration\Helper;

/**
 * Class GetResponseAPI3
 * @package GetResponse\GetResponseIntegration\Helper
 */
class GetResponseAPI3
{
    /** @var string */
    private $api_key;

    /** @var string */
    private $api_url = 'https://api.getresponse.com/v3';

    /** @var int */
    private $timeout = 8;

    /** @var string */
    private $enterprise_domain;

    /** @var string */
    public $http_status;

    /** @var string */
    private $version;

    /**
     * Set api key and optionally API endpoint
     * @param string $api_key
     * @param string $api_url
     * @param string $enterprise_domain
     * @param string $version
     */
    public function __construct($api_key, $api_url = null, $enterprise_domain = null, $version = '')
    {
        $this->api_key = $api_key;
        $this->version = $version;

        if (!empty($api_url)) {
            $this->api_url = $api_url;
        }

        if (!empty($enterprise_domain)) {
            $this->enterprise_domain = $enterprise_domain;
        }
    }

    /**
     * get account details
     *
     * @return mixed
     */
    public function accounts()
    {
        return $this->call('accounts');
    }

    /**
     * @return mixed
     */
    public function ping()
    {
        return $this->accounts();
    }

    /**
     * Return all campaigns
     * @param array $params
     * @return mixed
     */
    public function getCampaigns($params)
    {
        $params['page'] = 1;
        $params['perPage'] = $pageSize = 100;

        $result = [];

        do {
            $response = (array) $this->call('campaigns?' . $this->setParams($params));
            $result = array_merge($result, $response);
            $params['page']++;
            $responseCount = count($response);
        } while ($responseCount == $pageSize);

        return $result;
    }

    /**
     * get single campaign
     * @param string $campaign_id retrieved using API
     * @return mixed
     */
    public function getCampaign($campaign_id)
    {
        return $this->call('campaigns/' . $campaign_id);
    }

    /**
     * adding campaign
     * @param $params
     * @return mixed
     */
    public function createCampaign($params)
    {
        return $this->call('campaigns', 'POST', $params);
    }

    /**
     * get all subscription confirmation subjects
     * @param string $lang
     * @return mixed
     */
    public function getSubscriptionConfirmationsSubject($lang = 'EN')
    {
        return $this->call('subscription-confirmations/subject/' . $lang);
    }

    /**
     * get all subscription confirmation bodies
     * @param string $lang
     * @return mixed
     */
    public function getSubscriptionConfirmationsBody($lang = 'EN')
    {
        return $this->call('subscription-confirmations/body/' . $lang);
    }

    /**
     * add single contact into your campaign
     *
     * @param array $params
     * @return mixed
     */
    public function addContact($params)
    {
        return $this->call('contacts', 'POST', $params);
    }

    /**
     * retrieving contact by id
     *
     * @param string $contact_id - contact id obtained by API
     * @return mixed
     */
    public function getContact($contact_id)
    {
        return $this->call('contacts/' . $contact_id);
    }

    /**
     * retrieving contact by params
     * @param array $params
     *
     * @return mixed
     */
    public function getContacts($params = [])
    {
        return $this->call('contacts?' . $this->setParams($params));
    }

    /**
     * updating any fields of your subscriber (without email of course)
     * @param string $contact_id
     * @param array $params
     *
     * @return mixed
     */
    public function updateContact($contact_id, $params = [])
    {
        return $this->call('contacts/' . $contact_id, 'POST', $params);
    }

    /**
     * drop single user by ID
     *
     * @param string $contact_id - obtained by API
     * @return mixed
     */
    public function deleteContact($contact_id)
    {
        return $this->call('contacts/' . $contact_id, 'DELETE');
    }

    /**
     * retrieve account custom fields
     * @param array $params
     *
     * @return mixed
     */
    public function getCustomFields($params = [])
    {
        return $this->call('custom-fields?' . $this->setParams($params));
    }

    /**
     * retrieve single custom field
     *
     * @param array $params
     * @return mixed
     */
    public function addCustomField($params = [])
    {
        return $this->call('custom-fields', 'POST', $params);
    }

    /**
     * retrieve account from fields
     * @param array $params
     *
     * @return mixed
     */
    public function getAccountFromFields($params = [])
    {
        return $this->call('from-fields?' . $this->setParams($params));
    }

    /**
     * retrieve autoresponders
     * @param array $params
     *
     * @return mixed
     */
    public function getAutoresponders($params = [])
    {
        return $this->call('autoresponders?' . $this->setParams($params));
    }

    /**
     * add custom field
     *
     * @param array $params
     * @return mixed
     */
    public function setCustomField($params)
    {
        return $this->call('custom-fields', 'POST', $params);
    }

    /**
     * retrieve single custom field
     *
     * @param string $custom_id obtained by API
     * @return mixed
     */
    public function getCustomField($custom_id)
    {
        return $this->call('custom-fields/' . $custom_id, 'GET');
    }

    /**
     * get single web form
     *
     * @param int $webform_id
     * @return mixed
     */
    public function getWebForm($webform_id)
    {
        return $this->call('webforms/' . $webform_id);
    }

    /**
     * retrieve all webforms
     * @param array $params
     *
     * @return mixed
     */
    public function getWebForms($params = [])
    {
        return $this->call('webforms?' . $this->setParams($params));
    }

    /**
     * get single form
     *
     * @param int $form_id
     * @return mixed
     */
    public function getForm($form_id)
    {
        return $this->call('forms/' . $form_id);
    }

    /**
     * retrieve all forms
     * @param array $params
     *
     * @return mixed
     */
    public function getForms($params = [])
    {
        return $this->call('forms?' . $this->setParams($params));
    }

    /**
     * @return mixed
     */
    public function getFeatures()
    {
        return $this->call('accounts/features');
    }

    /**
     * @return mixed
     */
    public function getTrackingCode()
    {
        return $this->call('tracking');
    }

    /**
     * Curl run request
     *
     * @param null $api_method
     * @param string $http_method
     * @param array $params
     * @return mixed
     */
    private function call($api_method = null, $http_method = 'GET', $params = [])
    {
        if (empty($api_method)) {
            return (object)[
                'httpStatus' => '400',
                'code' => '1010',
                'codeDescription' => 'Error in external resources',
                'message' => 'Invalid api method'
            ];
        }

        $params = json_encode($params);
        $url = $this->api_url . '/' . $api_method;

        $headers = [
            'X-Auth-Token: api-key ' . $this->api_key,
            'Content-Type: application/json',
            'User-Agent: Getresponse Plugin ' . $this->version,
            'X-APP-ID: d7a458d2-1a75-4296-b417-ed601697e289'
        ];

        // for GetResponse 360
        if (isset($this->enterprise_domain)) {
            $headers[] = 'X-Domain: ' . $this->enterprise_domain;
        }

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_ENCODING => 'gzip,deflate',
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPHEADER => $headers
        ];

        if ($http_method == 'POST') {
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = $params;
        } elseif ($http_method == 'DELETE') {
            $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
        }

        try {
            $curl = curl_init();
            curl_setopt_array($curl, $options);

            $response = json_decode(curl_exec($curl));
            $this->http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            curl_close($curl);
        } catch (\Exception $e) {
            return false;
        }

        return (object)$response;
    }

    /**
     * @param array $params
     *
     * @return string
     */
    private function setParams($params = [])
    {
        $result = [];
        if (is_array($params)) {
            foreach ($params as $key => $value) {
                $result[$key] = $value;
            }
        }

        return http_build_query($result);
    }

    /**
     * @param string $shopName
     * @param string $locale
     * @param string $currency
     *
     * @return mixed
     */
    public function createShop($shopName, $locale, $currency)
    {
        $params = [
            'name' => $shopName,
            'locale' => $locale,
            'currency' => $currency
        ];

        return $this->call('shops', 'POST', $params);
    }

    /**
     * @return mixed
     */
    public function getShops()
    {
        $params['page'] = 1;
        $params['perPage'] = $pageSize = 100;

        $result = [];

        do {
            $response = (array) $this->call('shops?' . $this->setParams($params));
            $result = array_merge($result, $response);
            $params['page']++;
            $responseCount = count($response);
        } while ($responseCount == $pageSize);

        return $result;
    }

    /**
     * @param string $shopId
     * @return mixed
     */
    public function deleteShop($shopId)
    {
        return $this->call('shops/' . $shopId, 'DELETE');
    }

    /**
     * @param string $shopId
     * @param array $params
     *
     * @return mixed
     */
    public function addNewCart($shopId, $params)
    {
        return $this->call('shops/' . $shopId . '/carts', 'POST', $params);
    }

    /**
     * @param string $shopId
     * @param string $cartId
     * @param array $params
     *
     * @return mixed
     */
    public function updateCart($shopId, $cartId, $params)
    {
        return $this->call('shops/' . $shopId . '/carts/' . $cartId, 'POST', $params);
    }

    /**
     * @param string $shopId
     * @param string $cartId
     *
     * @return mixed
     */
    public function deleteCart($shopId, $cartId)
    {
        return $this->call('shops/' . $shopId . '/carts/' . $cartId, 'DELETE');
    }

    /**
     * @param string $shopId
     * @param array $params
     *
     * @return mixed
     */
    public function addProduct($shopId, $params)
    {
        return $this->call('shops/' . $shopId . '/products', 'POST', $params);
    }

    /**
     * @param string $shopId
     * @param array $params
     *
     * @return mixed
     */
    public function addCart($shopId, $params)
    {
        return $this->call('shops/' . $shopId . '/carts', 'POST', $params);
    }

    /**
     * @param string $shopId
     * @param array $params
     *
     * @return mixed
     */
    public function createOrder($shopId, $params)
    {
        return $this->call('shops/' . $shopId . '/orders', 'POST', $params);
    }

    /**
     * @param string $shopId
     * @param string $orderId
     * @param array $params
     *
     * @return mixed
     */
    public function getOrder($shopId, $orderId, $params = [])
    {
        return $this->call('shops/' . $shopId . '/orders/' . $orderId, 'GET', $params);
    }

    /**
     * @param string $shopId
     * @param string $orderId
     * @param array $params
     *
     * @return mixed
     */
    public function updateOrder($shopId, $orderId, $params)
    {
        return $this->call('shops/' . $shopId . '/orders/' . $orderId, 'POST', $params);
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function getCustomFieldByName($name)
    {
        $result = (array)$this->call('custom-fields?query[name]=' . $name);

        return reset($result);
    }
}
