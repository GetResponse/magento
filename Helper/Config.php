<?php
namespace GetResponse\GetResponseIntegration\Helper;

/**
 * Class Config
 * @package GetResponse\GetResponseIntegration\Helper
 */
class Config
{
    const CONFIG_DATA_SHOP_STATUS = 'getresponse/shop/status';
    const CONFIG_DATA_SHOP_ID = 'getresponse/shop/id';
    const CONFIG_DATA_ECOMMERCE_LIST_ID = 'getresponse/ecommerce/list/id';
    const CONFIG_DATA_ACCOUNT = 'getresponse/account';
    const CONFIG_DATA_CONNECTION_SETTINGS = 'getresponse/connection-settings';
    const CONFIG_DATA_WEB_EVENT_TRACKING = 'getresponse/web-event-tracking';
    const CONFIG_DATA_REGISTRATION_SETTINGS = 'getresponse/registration/settings';
    const CONFIG_DATA_REGISTRATION_CUSTOMS = 'getresponse/registration/customs';
    const CONFIG_DATA_NEWSLETTER_SETTINGS = 'getresponse/newsletter/settings';
    const CONFIG_DATA_WEBFORMS_SETTINGS = 'getresponse/webforms/settings';
    const CONFIG_DATA_ORIGIN_CUSTOM_FIELD_ID = 'getresponse/origin_custom_field_id';
    const INVALID_REQUEST_DATE_TIME = 'getresponse/invalid_request_date_time';

    const PLUGIN_MAIN_PAGE = 'getresponse/account/index';

    const UNAUTHORIZED_RESPONSE_CODES = [1014, 1018, 1017];
    const DISCONNECT_DELAY = 60 * 60 * 24;

    const CACHE_KEY = 'getresponse_cache';
    const CACHE_TIME = 600; // 10 minutes of cache time
}
