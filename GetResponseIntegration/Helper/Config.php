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
    const CONFIG_DATA_ACCOUNT = 'getresponse/account';
    const CONFIG_DATA_RULES = 'getresponse/automation';
    const CONFIG_DATA_CONNECTION_SETTINGS = 'getresponse/connection-settings';
    const CONFIG_DATA_WEB_EVENT_TRACKING = 'getresponse/web-event-tracking';
    const CONFIG_DATA_REGISTRATION_SETTINGS = 'getresponse/registration/settings';
    const CONFIG_DATA_REGISTRATION_CUSTOMS = 'getresponse/registration/customs';
    const CONFIG_DATA_WEBFORMS = 'getresponse/webforms';
    const CONFIG_DATA_UNAUTHORIZED_API_CALL_DATE = 'getresponse/unauthorized-api-call-date';

    const PLUGIN_MAIN_PAGE = 'getresponseintegration/settings/index';

    const UNAUTHORIZED_RESPONSE_CODES = [1014, 1018, 1017];
    const DISCONNECT_DELAY = 60 * 60 * 24;

    const INCORRECT_API_RESOONSE_MESSAGE = 'Incorrect API response';
    const CACHE_KEY = 'getresponse_cache';
    const CACHE_TIME = 600; // 10 minutes of cache time
}
