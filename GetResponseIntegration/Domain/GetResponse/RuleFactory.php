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
            $data['id'],
            $data['category'],
            $data['action'],
            $data['campaign'],
            isset($data['cycle_day']) ? $data['cycle_day'] : ''
        );
    }
}
