<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Helper;

class Config
{
    const PLUGIN_MAIN_PAGE = 'getresponse/account/index';
    const CONFIG_DATA_SHOP_STATUS = 'getresponse/shop/status';
    const CONFIG_DATA_SHOP_ID = 'getresponse/shop/id';
    const CONFIG_DATA_ECOMMERCE_LIST_ID = 'getresponse/ecommerce/list/id';
    const CONFIG_DATA_ACCOUNT = 'getresponse/account';
    const CONFIG_DATA_CONNECTION_SETTINGS = 'getresponse/connection-settings';
    const CONFIG_DATA_WEB_EVENT_TRACKING = 'getresponse/web-event-tracking';
    const CONFIG_DATA_PLUGIN_MODE = 'getresponse/plugin-mode';
    const CONFIG_LIVE_SYNCHRONIZATION = 'getresponse/live-synchronization';
    const CONFIG_DATA_FACEBOOK_PIXEL_SNIPPET = 'getresponse/facebook-pixel-snippet';
    const CONFIG_DATA_FACEBOOK_ADS_PIXEL_SNIPPET = 'getresponse/facebook-ads-pixel-snippet';
    const CONFIG_DATA_FACEBOOK_BUSINESS_EXTENSION_SNIPPET = 'getresponse/facebook-business-extension-snippet';
    const CONFIG_DATA_REGISTRATION_SETTINGS = 'getresponse/registration/settings';
    const CONFIG_DATA_REGISTRATION_CUSTOMS = 'getresponse/registration/customs';
    const CONFIG_DATA_NEWSLETTER_SETTINGS = 'getresponse/newsletter/settings';
    const CONFIG_DATA_WEBFORMS_SETTINGS = 'getresponse/webforms/settings';
    const CONFIG_DATA_ORIGIN_CUSTOM_FIELD_ID = 'getresponse/origin_custom_field_id';
    const CONFIG_LOCALE_CODE = 'general/locale/code';

    const INVALID_REQUEST_DATE_TIME = 'getresponse/invalid_request_date_time';

    const UNAUTHORIZED_RESPONSE_CODES = [1014, 1018, 1017];
    const DISCONNECT_DELAY = 60 * 60 * 24;

    const CACHE_KEY = 'getresponse_cache';
    const CACHE_TIME = 600; // 10 minutes of cache time
    const SCOPE_TAG = 'scope';
    const SCOPE = 'websites';
    const SCOPE_SESSION = 'grScopeId';

    const API_APP_SECRET = '010b02c432482c288dca40f5dae0b132';
}
