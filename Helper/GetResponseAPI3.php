<?php
namespace GetResponse\GetResponseIntegration\Helper;

/**
 * Class GetResponseAPI3
 * @package GetResponse\GetResponseIntegration\Helper
 */
class GetResponseAPI3
{
    /** @var string */
    private $apiKey;

    /** @var string */
    private $apiUrl = 'https://api.getresponse.com/v3';

    /** @var int */
    private $timeout = 8;

    /** @var string */
    private $enterpriseDomain;

    /** @var string */
    public $httpStatus;

    /** @var string */
    private $version;

    /** @var array */
    private $headers;

    /**
     * @param string $apiKey
     * @param string $apiUrl
     * @param string $enterpriseDomain
     * @param string $version
     */
    public function __construct($apiKey, $apiUrl = '', $enterpriseDomain = '', $version = '')
    {
        $this->apiKey = $apiKey;
        $this->version = $version;

        if (!empty($apiUrl)) {
            $this->apiUrl = $apiUrl;
        }

        if (!empty($enterpriseDomain)) {
            $this->enterpriseDomain = $enterpriseDomain;
        }
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return array
     */
    public function ping()
    {
        return $this->call('accounts');
    }

    /**
     * @param array $params
     * @return array
     */
    public function getCampaigns($params)
    {
        $params['perPage'] = 100;
        $params['page'] = 1;
        $result = $this->call('campaigns?' . $this->setParams($params), 'GET', [], true);

        $headers = $this->getHeaders();

        for($i = 2; $i <= $headers['TotalPages']; $i++) {
            $params['page'] = $i;
            $res = $this->call('campaigns?' . $this->setParams($params), 'GET', []);
            $result = array_merge($result, $res);
        }

        return $result;

    }

    /**
     * @param string $campaignId
     * @return array
     */
    public function getCampaign($campaignId)
    {
        return $this->call('campaigns/' . $campaignId);
    }

    /**
     * @param array $params
     * @return array
     */
    public function createCampaign($params)
    {
        return $this->call('campaigns', 'POST', $params);
    }

    /**
     * @param string $lang
     * @return array
     */
    public function getSubscriptionConfirmationsSubject($lang = 'EN')
    {
        return $this->call('subscription-confirmations/subject/' . $lang);
    }

    /**
     * @param string $lang
     * @return array
     */
    public function getSubscriptionConfirmationsBody($lang = 'EN')
    {
        return $this->call('subscription-confirmations/body/' . $lang);
    }

    /**
     * @param array $params
     * @return array
     */
    public function addContact($params)
    {
        return $this->call('contacts', 'POST', $params);
    }

    /**
     * @param string $contactId
     * @return array
     */
    public function getContact($contactId)
    {
        return $this->call('contacts/' . $contactId);
    }

    /**
     * @param array $params
     * @return array
     */
    public function getContacts($params = [])
    {
        return $this->call('contacts?' . $this->setParams($params));
    }

    /**
     * @param string $contactId
     * @param array $params
     *
     * @return array
     */
    public function updateContact($contactId, $params = [])
    {
        return $this->call('contacts/' . $contactId, 'POST', $params);
    }

    /**
     * @param string $contactId
     * @return array
     */
    public function deleteContact($contactId)
    {
        return $this->call('contacts/' . $contactId, 'DELETE');
    }

    /**
     * @param array $params
     * @return array
     */
    public function addCustomField($params = [])
    {
        return $this->call('custom-fields', 'POST', $params);
    }

    /**
     * @param array $params
     * @return array
     */
    public function getAccountFromFields($params = [])
    {
        return $this->call('from-fields?' . $this->setParams($params));
    }

    /**
     * @param array $params
     * @return array
     */
    public function getAutoresponders($params = [])
    {
        $params['perPage'] = 100;
        $params['page'] = 1;
        $result = $this->call('autoresponders?' . $this->setParams($params), 'GET', [], true);
        $headers = $this->getHeaders();

        for($i = 2; $i <= $headers['TotalPages']; $i++) {
            $params['page'] = $i;
            $res = $this->call('autoresponders?' . $this->setParams($params), 'GET', []);
            $result = array_merge($result, $res);
        }

        return $result;

    }

    /**
     * @param array $params
     * @return array
     */
    public function getWebForms($params = [])
    {
        $params['perPage'] = 100;
        $params['page'] = 1;
        $result = $this->call('webforms?' . $this->setParams($params), 'GET', [], true);
        $headers = $this->getHeaders();

        for($i = 2; $i <= $headers['TotalPages']; $i++) {
            $params['page'] = $i;
            $res = $this->call('webforms?' . $this->setParams($params), 'GET', []);
            $result = array_merge($result, $res);
        }

        return $result;
    }

    /**
     * @param array $params
     * @return array
     */
    public function getForms($params = [])
    {
        $params['perPage'] = 100;
        $params['page'] = 1;
        $result = $this->call('forms?' . $this->setParams($params), 'GET', [], true);
        $headers = $this->getHeaders();

        for($i = 2; $i <= $headers['TotalPages']; $i++) {
            $params['page'] = $i;
            $res = $this->call('forms?' . $this->setParams($params), 'GET', []);
            $result = array_merge($result, $res);
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getFeatures()
    {
        return $this->call('accounts/features');
    }

    /**
     * @return array
     */
    public function getTrackingCode()
    {
        return $this->call('tracking');
    }

    /**
     * @param string $shopName
     * @param string $locale
     * @param string $currency
     *
     * @return array
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
     * @return array
     */
    public function getShops()
    {
        $params['perPage'] = 100;
        $params['page'] = 1;
        $result = $this->call('shops?' . $this->setParams($params), 'GET', [], true);
        $headers = $this->getHeaders();

        for($i = 2; $i <= $headers['TotalPages']; $i++) {
            $params['page'] = $i;
            $res = $this->call('shops?' . $this->setParams($params), 'GET', []);
            $result = array_merge($result, $res);
        }

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
     * @param $name
     * @return array
     */
    public function getCustomFieldByName($name)
    {
        $result = $this->call('custom-fields?query[name]=' . $name);
        return reset($result);
    }

    /**
     * @param string $apiMethod
     * @param string $httpMethod
     * @param array $params
     * @param bool $withHeaders
     * @return array
     */
    private function call($apiMethod, $httpMethod = 'GET', $params = [], $withHeaders = false)
    {
        if (empty($apiMethod)) {
            return [
                'httpStatus' => '400',
                'code' => '1010',
                'codeDescription' => 'Error in external resources',
                'message' => 'Invalid api method'
            ];
        }

        $params = json_encode($params);
        $url = $this->apiUrl . '/' . $apiMethod;

        $headers = [
            'X-Auth-Token: api-key ' . $this->apiKey,
            'Content-Type: application/json',
            'User-Agent: Getresponse Plugin ' . $this->version,
            'X-APP-ID: d7a458d2-1a75-4296-b417-ed601697e289'
        ];

        // for GetResponse 360
        if (isset($this->enterpriseDomain)) {
            $headers[] = 'X-Domain: ' . $this->enterpriseDomain;
        }

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_ENCODING => 'gzip,deflate',
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HEADER => $withHeaders,
            CURLOPT_HTTPHEADER => $headers
        ];

        if ($httpMethod == 'POST') {
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = $params;
        } elseif ($httpMethod == 'DELETE') {
            $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
        }

        $curl = curl_init();
        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);
        $this->httpStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if (false === $response) {
            return [];
        }

        if ($withHeaders) {
            list($headers, $response) = explode("\r\n\r\n", $response, 2);
            $this->headers = $this->prepareHeaders($headers);
        }

        return json_decode($response, true);
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
     * @param string $rawHeaders
     * @return array
     */
    private function prepareHeaders( $rawHeaders ) {
        $rawHeaders = explode("\r\n", $rawHeaders);
        $headers = array();

        foreach ($rawHeaders as $header) {
            $params = explode(':', $header, 2);
            $key = isset($params[0]) ? $params[0] : null;
            $value = isset($params[1]) ? $params[1] : null;
            $headers[trim($key)] = trim($value);
        }

        return $headers;
    }
}
