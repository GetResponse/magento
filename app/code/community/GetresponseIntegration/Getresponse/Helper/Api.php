<?php

use GetresponseIntegration_Getresponse_Domain_GetresponseException as GetresponseException;

/**
 * Class GetresponseIntegration_Getresponse_Helper_Api
 */
class GetresponseIntegration_Getresponse_Helper_Api
{
    const CONTACT_ERROR = 1;
    const CONTACT_UPDATED = 2;
    const CONTACT_CREATED = 3;
    const ORIGIN_NAME = 'magento';

    const GET_AUTORESPONDER_CACHE_KEY = 'get_autoresponders';
    const GET_CAMPAIGN_CACHE_KEY = 'get_campaign';
    const CONTACT_CACHE_KEY = 'get_campaign';
    const GET_FROM_FIELDS_CACHE_KEY = 'get_from_fields';
    const GET_CONFIRMATIONS_SUBJECT = 'get_confirmations_subject';
    const GET_CONFIRMATIONS_BODY = 'get_confirmations_body';
    const GET_PUBLISHED_FORMS = 'get_published_forms';
    const GET_PUBLISHED_WEB_FORMS = 'get_published_web_forms';
    const GET_SHOPS = 'get_shops';
    const PRODUCT_CACHE_KEY = 'product';
    const ALL_CUSTOM_FIELDS = 'all_custom_fields';
    const PER_PAGE = 100;

    public static $status
        = array(
            self::CONTACT_CREATED => 'Created',
            self::CONTACT_UPDATED => 'Updated',
            self::CONTACT_ERROR   => 'Not added'
        );

    /** @var array */
    private $cachedCustoms = array();

    /** @var GetresponseIntegration_Getresponse_Model_Cache */
    protected $cache;

    public function __construct()
    {
        $this->cache = Mage::getSingleton('getresponse/cache');
    }

    /**
     * @return GetresponseIntegration_Getresponse_Helper_GrApi
     */
    public static function getApiInstance()
    {
        static $instance;

        if (null === $instance) {
            Mage::getResourceHelper('getresponse/grapi');
            $instance = new GetresponseIntegration_Getresponse_Helper_GrApi();
        }

        return $instance;
    }

    /**
     * @param string $email
     * @param string $campaignId
     *
     * @return mixed
     * @throws GetresponseException
     */
    public function getContact($email, $campaignId)
    {
        $key = md5(self::CONTACT_CACHE_KEY . $email . $campaignId);
        $contact = $this->cache->load($key);

        if (false === $contact) {
            $contacts = $this->getApiInstance()->getContacts(
                array('query'        => array(
                    'email'      => $email,
                    'campaignId' => $campaignId
                ), 'additionalFlags' => 'forceCustoms'
                )
            );

            $contact = reset($contacts);
            $this->cache->save($contact, $key);
        }

        return $contact;
    }

    /**
     * @return array
     * @throws GetresponseException
     */
    public function getCustomFields()
    {
        $finalCustoms = $this->cache->load(self::ALL_CUSTOM_FIELDS);

        if (false === $finalCustoms) {

            $finalCustoms = array();
            $customs = $this->getApiInstance()->getCustomFields(
                array('perPage' => self::PER_PAGE, 'page' => 1)
            );

            $headers = $this->getApiInstance()->getHeaders();

            $totalPages = isset($headers['TotalPages']) ? $headers['TotalPages']
                : 0;

            for ($page = 2; $page <= $totalPages; $page++) {
                $response = $this->getApiInstance()->getCustomFields(
                    array('perPage' => self::PER_PAGE, 'page' => $page)
                );
                $customs = array_merge($customs, $response);
            }

            foreach ($customs as $customField) {
                if (isset($customField['name'])
                    && isset($customField['customFieldId'])
                ) {
                    $finalCustoms[$customField['name']]
                        = $customField['customFieldId'];
                }
            }

            $this->cache->save($finalCustoms, self::ALL_CUSTOM_FIELDS);
        }

        return $finalCustoms;
    }

    /**
     * @param $userCustoms
     *
     * @return array
     */
    public function setCustoms($userCustoms)
    {
        $customFields = array();

        if (empty($userCustoms)) {
            return $customFields;
        }

        foreach ($userCustoms as $name => $value) {

            if (!isset($this->cachedCustoms[$name])) {

                try {
                    $customs = $this->getApiInstance()->getCustomFields(
                        array('query[name]' => $name)
                    );

                    $custom = reset($customs);

                    // custom field not found - create new
                    if (empty($custom) || empty($custom['customFieldId'])) {
                        $custom = $this->getApiInstance()->addCustomField(
                            array(
                                'name'   => $name,
                                'type'   => is_array($value) ? "checkbox"
                                    : "text",
                                'hidden' => "false",
                                'values' => is_array($value) ? $value
                                    : array($value),
                            )
                        );
                        // Custom adding failed
                        if (!isset($custom['customFieldId'])) {
                            continue;
                        }
                    }

                    $this->cachedCustoms[$name] = $custom;
                } catch (GetresponseException $e) {
                    continue;
                }

            } else {
                $custom = $this->cachedCustoms[$name];
            }

            $customFields[] = array(
                'customFieldId' => $custom['customFieldId'],
                'value'         => is_array($value) ? $value : array($value)
            );
        }

        return $customFields;
    }

    /**
     * @param array $results
     * @param array $userCustoms
     *
     * @return array
     */
    public function mergeUserCustoms($results, $userCustoms)
    {
        $customFields = array();

        if (is_array($results)) {
            foreach ($results as $customs) {
                $value = $customs['value'];
                if (in_array($customs['name'], array_keys($userCustoms))) {
                    $userCustomValue = $userCustoms[$customs['name']];
                    $value = is_array($userCustomValue) ? $userCustomValue
                        : array($userCustomValue);
                    unset($userCustoms[$customs['name']]);
                }

                $customFields[] = array(
                    'customFieldId' => $customs['customFieldId'],
                    'value'         => $value,
                );
            }
        }

        return array_merge($customFields, $this->setCustoms($userCustoms));
    }

    /**
     * @return array
     * @throws GetresponseException
     */
    public function getCampaigns()
    {
        $finalCampaigns = $this->cache->load(self::GET_CAMPAIGN_CACHE_KEY);

        if (false === $finalCampaigns) {
            $finalCampaigns = array();

            $campaigns = $this->getApiInstance()->getCampaigns(
                array(
                    'sort'    => array('name' => 'asc'),
                    'page'    => 1,
                    'perPage' => self::PER_PAGE
                )
            );

            $headers = $this->getApiInstance()->getHeaders();
            $totalPages = isset($headers['TotalPages']) ? $headers['TotalPages']
                : 1;

            for ($page = 2; $page <= $totalPages; $page++) {
                $response = $this->getApiInstance()->getCampaigns(
                    array(
                        'sort'    => array('name' => 'asc'),
                        'page'    => $page,
                        'perPage' => self::PER_PAGE
                    )
                );

                $campaigns = array_merge($campaigns, $response);
            }

            foreach ($campaigns as $campaign) {
                $finalCampaigns[$campaign['campaignId']] = $campaign['name'];
            }

            $this->cache->save($finalCampaigns, self::GET_CAMPAIGN_CACHE_KEY);
        }

        return $finalCampaigns;
    }

    /**
     * @return array
     * @throws GetresponseException
     */
    public function getPublishedForms()
    {
        $finalForms = $this->cache->load(self::GET_PUBLISHED_FORMS);

        if (false === $finalForms) {
            $finalForms = array();

            $forms = $this->getApiInstance()->getForms(
                array(
                    'page'    => 1,
                    'perPage' => self::PER_PAGE
                )
            );

            $headers = $this->getApiInstance()->getHeaders();
            $totalPages = isset($headers['TotalPages']) ? $headers['TotalPages']
                : 1;

            for ($page = 2; $page <= $totalPages; $page++) {
                $response = $this->getApiInstance()->getForms(
                    array(
                        'page'    => $page,
                        'perPage' => self::PER_PAGE
                    )
                );

                $forms = array_merge($forms, $response);
            }

            foreach ($forms as $form) {
                if (isset($form['status']) && 'published' === $form['status']) {
                    $finalForms[] = $form;
                }
            }

            $this->cache->save($finalForms, self::GET_PUBLISHED_FORMS);
        }

        return $finalForms;
    }

    /**
     * @param string $id
     *
     * @return array
     * @throws GetresponseException
     */
    public function getWebform($id)
    {
        return $this->getApiInstance()->getWebFormById($id);
    }

    /**
     * @param string $formId
     *
     * @return array
     * @throws GetresponseException
     */
    public function getForm($formId)
    {
        return $this->getApiInstance()->getFormById($formId);
    }

    /**
     * @return array
     * @throws GetresponseException
     */
    public function getPublishedWebForms()
    {
        $finalForms = $this->cache->load(self::GET_PUBLISHED_WEB_FORMS);

        if (false === $finalForms) {
            $finalForms = array();

            $forms = $this->getApiInstance()->getWebForms(
                array(
                    'page'    => 1,
                    'perPage' => self::PER_PAGE
                )
            );

            $headers = $this->getApiInstance()->getHeaders();
            $totalPages = isset($headers['TotalPages']) ? $headers['TotalPages']
                : 1;

            for ($page = 2; $page <= $totalPages; $page++) {
                $response = $this->getApiInstance()->getWebForms(
                    array(
                        'page'    => $page,
                        'perPage' => self::PER_PAGE
                    )
                );

                $forms = array_merge($forms, $response);
            }

            foreach ($forms as $form) {
                if (isset($form['status']) && in_array($form['status'], array('enabled', 'published'))) {
                    $finalForms[] = $form;
                }
            }

            $this->cache->save($finalForms, self::GET_PUBLISHED_WEB_FORMS);
        }

        return $finalForms;
    }

    /**
     * @return array
     * @throws GetresponseException
     */
    public function getCampaignDays()
    {
        $campaignDays = $this->cache->load(self::GET_AUTORESPONDER_CACHE_KEY);

        if (false === $campaignDays) {

            $campaignDays = array();
            $page = 1;

            $autoresponders = $this->getApiInstance()->getAutoResponders(
                array('page' => $page, 'perPage' => self::PER_PAGE)
            );

            $headers = $this->getApiInstance()->getHeaders();
            $totalPages = isset($headers['TotalPages']) ? $headers['TotalPages']
                : 1;

            for ($page = 2; $page <= $totalPages; $page++) {
                $response = $this->getApiInstance()->getAutoResponders(
                    array('page' => $page, 'perPage' => self::PER_PAGE)
                );
                $autoresponders = array_merge($autoresponders, $response);
            }

            foreach ($autoresponders as $autoresponder) {

                if ($autoresponder['triggerSettings']['dayOfCycle'] == null) {
                    continue;
                }

                $campaignDays[$autoresponder['triggerSettings']['subscribedCampaign']['campaignId']][$autoresponder['autoresponderId']]
                    = array(
                    'day'    => $autoresponder['triggerSettings']['dayOfCycle'],
                    'name'   => $autoresponder['name'],
                    'status' => $autoresponder['status']
                );
            }
            $this->cache->save(
                $campaignDays, self::GET_AUTORESPONDER_CACHE_KEY
            );
        }

        return $campaignDays;
    }

    /**
     * @param string $campaignName
     * @param string $fromField
     * @param string $replyToField
     * @param string $confirmationSubject
     * @param string $confirmationBody
     *
     * @throws GetresponseException
     */
    public function addCampaignToGetResponse(
        $campaignName,
        $fromField,
        $replyToField,
        $confirmationSubject,
        $confirmationBody
    ) {
        $locale = Mage::app()->getLocale()->getLocaleCode();
        $code = strtoupper(substr($locale, 0, 2));

        $params = array(
            'name'         => $campaignName,
            'confirmation' => array(
                'fromField'                         => array('fromFieldId' => $fromField),
                'replyTo'                           => array('fromFieldId' => $replyToField),
                'subscriptionConfirmationBodyId'    => $confirmationBody,
                'subscriptionConfirmationSubjectId' => $confirmationSubject
            ),
            'languageCode' => $code
        );

        $this->getApiInstance()->createCampaign($params);
        $this->cache->remove(self::GET_CAMPAIGN_CACHE_KEY);
    }

    /**
     * @param string $apiKey
     * @param string $url
     * @param string $domain
     */
    public function setApiDetails($apiKey, $url, $domain)
    {
        if (!empty($apiKey)) {
            $this->getApiInstance()->api_key = $apiKey;
        }

        if (!empty($url)) {
            $this->getApiInstance()->api_url = $url;
        }

        if (!empty($domain)) {
            $this->getApiInstance()->domain = $domain;
        }
    }

    /**
     * @return array
     * @throws GetresponseException
     */
    public function getShops()
    {
        $shops = $this->cache->load(self::GET_SHOPS);

        if (false === $shops) {

            $shops = $this->getApiInstance()->getShops(
                array('page' => 1, 'perPage' => self::PER_PAGE)
            );

            $headers = $this->getApiInstance()->getHeaders();
            $totalPages = isset($headers['TotalPages']) ? $headers['TotalPages']
                : 1;

            for ($page = 2; $page <= $totalPages; $page++) {
                $response = $this->getApiInstance()->getShops(
                    array('page' => $page, 'perPage' => self::PER_PAGE)
                );
                $shops = array_merge($shops, $response);
            }
            $this->cache->save($shops, self::GET_SHOPS);
        }

        return $shops;
    }

    /**
     * @param string $name
     *
     * @return array
     * @throws GetresponseException
     * @throws Mage_Core_Model_Store_Exception
     */
    public function addShop($name)
    {
        $locale = substr(Mage::app()->getLocale()->getDefaultLocale(), 0, 2);
        $currency = Mage::app()->getStore()->getCurrentCurrencyCode();
        $this->cache->remove(self::GET_SHOPS);

        $params = array(
            'name'     => $name,
            'locale'   => $locale,
            'currency' => $currency
        );

        return $this->getApiInstance()->addShop($params);
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
        return $this->getApiInstance()->addCart($shopId, $params);
    }

    /**
     * @param string $shopId
     * @param string $cartId
     * @param array  $params
     *
     * @return array
     * @throws GetresponseException
     */
    public function updateCart($shopId, $cartId, $params)
    {
        return $this->getApiInstance()->updateCart($shopId, $cartId, $params);
    }

    /**
     * @param string $shopId
     * @param string $cartId
     *
     * @throws GetresponseException
     */
    public function deleteCart($shopId, $cartId)
    {
        $this->getApiInstance()->deleteCart($shopId, $cartId);
    }

    /**
     * @param string $shopId
     * @param string $productId
     *
     * @return array
     * @throws Exception
     */
    public function getProductById($shopId, $productId)
    {
        $cacheKey = md5(self::PRODUCT_CACHE_KEY . $shopId . $productId);
        $product = $this->cache->load($cacheKey);

        if (false === $product) {
            $product = $this->getApiInstance()->getProductById(
                $shopId, $productId
            );
            $this->cache->save($product, $cacheKey);
        }

        return $product;
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
        return $this->getApiInstance()->addProduct($shopId, $params);
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
        return $this->getApiInstance()->createOrder($shopId, $params);
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
        return $this->getApiInstance()->updateOrder($shopId, $orderId, $params);
    }

    /**
     * @param string $shopId
     *
     * @return bool
     */
    public function deleteShop($shopId)
    {
        try {
            $this->getApiInstance()->deleteShop($shopId);
            $this->cache->remove(self::GET_SHOPS);
            return true;
        } catch (GetresponseException $e) {
            return false;
        }
    }

    /**
     * @return array
     * @throws GetresponseException
     */
    public function getFromFields()
    {
        $fromFields = $this->cache->load(self::GET_FROM_FIELDS_CACHE_KEY);

        if (false === $fromFields) {

            $fromFields = $this->getApiInstance()->getAccountFromFields(
                array('page' => 1, 'perPage' => self::PER_PAGE)
            );

            $headers = $this->getApiInstance()->getHeaders();
            $totalPages = isset($headers['TotalPages']) ? $headers['TotalPages']
                : 1;

            for ($page = 2; $page <= $totalPages; $page++) {
                $response = $this->getApiInstance()->getAccountFromFields(
                    array('page' => 1, 'perPage' => self::PER_PAGE)
                );
                $fromFields = array_merge($fromFields, $response);
            }

            $this->cache->save($fromFields, self::GET_FROM_FIELDS_CACHE_KEY);
        }

        return $fromFields;
    }

    /**
     * @param string $language
     *
     * @return array
     * @throws GetresponseException
     */
    public function getSubscriptionConfirmationsSubject($language)
    {
        $subject = $this->cache->load(self::GET_CONFIRMATIONS_SUBJECT);
        if (false === $subject) {

            $subject = $this->getApiInstance()
                ->getSubscriptionConfirmationsSubject(
                    $language
                );
            $this->cache->save($subject, self::GET_CONFIRMATIONS_SUBJECT);
        }

        return $subject;
    }

    /**
     * @param string $language
     *
     * @return array
     * @throws GetresponseException
     */
    public function getSubscriptionConfirmationsBody($language)
    {
        $body = $this->cache->load(self::GET_CONFIRMATIONS_BODY);

        if (false === $body) {
            $body = $this->getApiInstance()->getSubscriptionConfirmationsBody(
                $language
            );
            $this->cache->save($body, self::GET_CONFIRMATIONS_BODY);
        }

        return $body;
    }

    /**
     * @param string $name
     *
     * @return array
     * @throws GetresponseException
     */
    public function addCustomField($name)
    {
        $custom = $this->getApiInstance()->addCustomField(
            array(
                'name'   => $name,
                'type'   => "text",
                'hidden' => "false",
                'values' => array(),
            )
        );

        return $custom;
    }

    /**
     * Merges magento and getresponse custom fields.
     *
     * @param array $userCustoms    - magento custom fields
     * @param array $grCustomFields - getresponse custom fields
     *
     * @return array - merged custom fields
     */
    private function setExportCustoms($userCustoms, $grCustomFields)
    {
        $customsHashMap = array();

        if (empty($userCustoms)) {
            return $customsHashMap;
        }

        foreach ($userCustoms as $name => $value) {

            foreach ($grCustomFields as $grCustomName => $grCustomId) {
                if ($grCustomName === $name) {

                    $customsHashMap[] = array(
                        'customFieldId' => $grCustomId,
                        'value'         => is_array($value) ? $value
                            : array($value)
                    );
                    break;
                }
            }
        }

        return $customsHashMap;
    }

    /**
     * Creates or updates GR user.
     *
     * @param string $campaign
     * @param string $name
     * @param string $email
     * @param string $cycleDay
     * @param array  $userCustoms
     * @param array  $grCustomFields
     *
     * @return int
     */
    public function upsertContact($campaign, $name, $email, $cycleDay = '',
        $userCustoms = array(), $grCustomFields = array()
    ) {
        $params = array(
            'email'     => $email,
            'campaign'  => array('campaignId' => $campaign)
        );

        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $params['ipAddress'] = $_SERVER['REMOTE_ADDR'];
        }

        $trimName = trim($name);
        if (!empty($trimName)) {
            $params['name'] = trim($name);
        }

        if (is_numeric($cycleDay) && $cycleDay >= 0) {
            $params['dayOfCycle'] = $cycleDay;
        }

        try {
            $contact = $this->getContact($email, $campaign);
        } catch (GetresponseException $e) {
            $contact = array();
        }

        // If contact already exists in gr account.
        if (!empty($contact['contactId'])) {
            if (!empty($contact['customFieldValues']) || !empty($userCustoms)) {
                $params['customFieldValues'] = $this->mergeUserCustoms(
                    $contact['customFieldValues'], $userCustoms
                );
            }

            try {
                $this->getApiInstance()->updateContact(
                    $contact['contactId'], $params
                );
            } catch (GetresponseException $e) {
                return self::CONTACT_ERROR;
            }

            return self::CONTACT_UPDATED;

        } else {
            $userCustoms['origin'] = self::ORIGIN_NAME;
            if (empty($grCustomFields)) {
                $params['customFieldValues'] = $this->setCustoms($userCustoms);
            } else {
                $params['customFieldValues'] = $this->setExportCustoms(
                    $userCustoms, $grCustomFields
                );
            }

            try {
                $this->getApiInstance()->addContact($params);

                return self::CONTACT_CREATED;
            } catch (GetresponseException $e) {
                return self::CONTACT_ERROR;
            }
        }
    }
}
