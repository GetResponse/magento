<?php
namespace GetResponse\GetResponseIntegration\Helper;

class Message
{
    const INCORRECT_API_RESPONSE_MESSAGE = 'Looks like we didn\'t expect this technical problem. If it persists, please contact the GetResponse dev team';

    const CONNECT_TO_GR = 'You need to connect to Get Response';

    const INCORRECT_SHOP_NAME = 'You need to enter a store name';
    const INCORRECT_SHOP = 'We couldn\'t delete this store. Please check if you\'ve made any changes to the store ID.';
    const DELETE_SHOP_ERROR = 'We couldn\'t delete this store. If the problem persists, please contact the GetResponse dev team. Be sure to include the error code that starts with ';

    const SELECT_CONTACT_LIST = 'You need to select a contact list';

    const INVALID_CUSTOM_FIELD_VALUE = 'The custom field %s contains invalid characters. You can use lowercase English alphabet characters, numbers, and underscore (\'_\')';

    const CANNOT_DELETE_RULE = 'We couldn\'t delete the rule. Please try again. If the problem persists, please contact the GetResponse dev team';

    const CANNOT_EDIT_RULE = 'We couldn\'t update the rule. Please try again. If the problem persists, please contact the GetResponse dev team';

    const RULE_UPDATED = 'Rule updated';

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

    const CANNOT_DELETE_LIST = 'We couldn\'t delete the list. Please try again. If the problem persists, please contact the GetResponse dev team';
    const CANNOT_CREATE_LIST = 'We couldn\'t create the list. Please try again. If the problem persists, please contact the GetResponse dev team';

    const LIST_VALIDATION_CAMPAIGN_NAME_ERROR = 'You need to enter a name that\'s at least 3 characters long';

    const LIST_VALIDATION_FROM_FIELD_ERROR = 'You need to select a sender email address';

    const LIST_VALIDATION_REPLY_TO_ERROR = 'You need to select a reply-to address';

    const LIST_VALIDATION_CONFIRMATION_SUBJECT_ERROR = 'Confirmation subject is a required field';

    const LIST_VALIDATION_CONFIRMATION_BODY = 'Confirmation body is a required field';

    const RULE_ADDED = 'Rule added';

    const RULE_DELETED = 'Rule deleted';

    const SELECT_RULE_CATEGORY = 'You need to select your product category';

    const SELECT_RULE_ACTION = 'You need to select what to do with the customer';

    const SELECT_RULE_TARGET_LIST = 'You need to select a target list';

    const ACCOUNT_DISCONNECTED = 'GetResponse account disconnected';

    const SETTINGS_SAVED = 'Settings saved';

    const ACCOUNT_CONNECTED = 'GetResponse account connected';

    const WEB_EVENT_TRAFFIC_ENABLED = 'Web event traffic tracking enabled';
    const WEB_EVENT_TRAFFIC_DISABLED = 'Web event traffic tracking disabled';
}
