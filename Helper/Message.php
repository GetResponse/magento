<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Helper;

class Message
{
    const INCORRECT_API_RESPONSE_MESSAGE = 'Looks like we didn\'t expect this technical problem. If it persists, please contact the GetResponse dev team';

    const CONNECT_TO_GR = 'Your GetResponse account isn’t connected yet. You need to enter the API key to complete setup';

    const INCORRECT_SHOP_NAME = 'You need to enter a store name';
    const INCORRECT_SHOP = 'We couldn\'t delete this store. Please check if you\'ve made any changes to the store ID.';
    const DELETE_SHOP_ERROR = 'We couldn\'t delete this store. If the problem persists, please contact the GetResponse dev team. Be sure to include the error code that starts with ';

    const SELECT_CONTACT_LIST = 'You need to select a contact list';

    const INVALID_CUSTOM_FIELD_VALUE = 'The custom field %s contains invalid characters. You can use lowercase English alphabet characters, numbers, and underscore (\'_\')';

    const EMPTY_API_KEY = 'You need to enter the API key. This field can\'t be empty';

    const SELECT_FORM = 'You need to select a form';

    const SELECT_FORM_POSITION = 'You need to select positioning of the form';

    const SELECT_FORM_POSITION_AND_PLACEMENT = 'You need to select a form and its placement';

    const FORM_PUBLISHED = 'Form published';

    const FORM_UNPUBLISHED = 'Form unpublished';

    const STORE_REMOVED = 'Store removed';

    const STORE_CHOOSE = 'You need to choose a store';

    const ECOMMERCE_SAVED = 'Ecommerce settings saved';

    const DATA_EXPORTED = 'Customer data exported';

    const LIST_CREATED = 'List created';

    const SHOP_DELETED = 'Shop deleted';

    const CANNOT_DELETE_LIST = 'We couldn\'t delete the list. Please try again. If the problem persists, please contact the GetResponse dev team';
    const CANNOT_CREATE_LIST = 'We couldn\'t create the list. Please try again. If the problem persists, please contact the GetResponse dev team';

    const LIST_VALIDATION_CAMPAIGN_NAME_ERROR = 'You need to enter a name that\'s at least 3 characters long';

    const LIST_VALIDATION_FROM_FIELD_ERROR = 'You need to select a sender email address';

    const LIST_VALIDATION_REPLY_TO_ERROR = 'You need to select a reply-to address';

    const LIST_VALIDATION_CONFIRMATION_SUBJECT_ERROR = 'Confirmation subject is a required field';

    const LIST_VALIDATION_CONFIRMATION_BODY = 'Confirmation body is a required field';

    const ACCOUNT_DISCONNECTED = 'GetResponse account disconnected';

    const SETTINGS_SAVED = 'Settings saved';

    const ACCOUNT_CONNECTED = 'GetResponse account connected';

    const WEB_EVENT_TRAFFIC_ENABLED = 'Web event traffic tracking enabled';
    const WEB_EVENT_TRAFFIC_DISABLED = 'Web event traffic tracking disabled';

    const SELECT_AUTORESPONDER_DAY = 'You need to select a autoresponder day';

    const CUSTOM_FIELDS_MAPPING_VALIDATION_MAGENTO_CUSTOM_DETAILS_EMPTY = 'Mapping: Customer details can not be empty';
    const CUSTOM_FIELDS_MAPPING_VALIDATION_GETRESPONSE_CUSTOM_FIELD_EMPTY = 'Mapping: GetResponse custom field can not be empty';
    const CUSTOM_FIELDS_MAPPING_VALIDATION_GETRESPONSE_CUSTOM_FIELD_DUPLICATED = 'Mapping: GetResponse custom field is used more than once';

    const API_ERROR_MESSAGE = 'The API key seems incorrect. Please check if you typed or pasted it correctly. If you recently generated a new key, please make sure you’re using the right one';

}
