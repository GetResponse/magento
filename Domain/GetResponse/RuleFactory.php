<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

/**
 * Class RuleFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class RuleFactory
{
    /**
     * @param array $data
     * @return Rule
     */
    public static function createFromDbArray(array $data)
    {
        return new Rule(
            isset($data['id']) ? $data['id'] : 0,
            isset($data['category']) ? $data['category'] : 0,
            isset($data['action']) ? $data['action'] : '',
            isset($data['campaign']) ? $data['campaign'] : '',
            isset($data['cycle_day']) ? $data['cycle_day'] : 0,
            isset($data['autoresponderId']) ? $data['autoresponderId'] : ''
        );
    }

    /**
     * @param array $data
     * @return Rule
     */
    public static function createFromArray(array $data)
    {
        return new Rule(
            isset($data['id']) ? $data['id'] : 0,
            isset($data['category']) ? $data['category'] : 0,
            isset($data['action']) ? $data['action'] : '',
            isset($data['campaign']) ? $data['campaign'] : '',
            self::getAutoresponderDayFromData($data),
            self::getAutoresponderIdFromData($data)
        );
    }

    /**
     * @param array $data
     * @return string
     */
    private static function getAutoresponderDayFromData(array $data)
    {
        if (!empty($data['autoresponder']) && isset(explode('_', $data['autoresponder'])[0])) {
            return explode('_', $data['autoresponder'])[0];
        }

        return 0;
    }

    /**
     * @param array $data
     * @return string
     */
    private static function getAutoresponderIdFromData(array $data)
    {
        if (!empty($data['autoresponder']) && isset(explode('_', $data['autoresponder'])[1])) {
            return explode('_', $data['autoresponder'])[1];
        }

        return '';
    }
}
