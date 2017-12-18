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
     *
     * @return Rule
     */
    public static function createFromArray(array $data)
    {
        return new Rule(
            isset($data['id']) ? $data['id'] : 0,
            isset($data['category']) ? $data['category'] : 0,
            isset($data['action']) ? $data['action'] : '',
            isset($data['campaign']) ? $data['campaign'] : '',
            isset($data['cycle_day']) ? $data['cycle_day'] : 0
        );
    }
}
