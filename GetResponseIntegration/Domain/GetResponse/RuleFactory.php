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
    public static function buildFromPayload(array $data)
    {
        return new Rule(
            null,
            $data['category'],
            $data['action'],
            $data['campaign_id'],
            isset($data['cycle_day']) ? $data['cycle_day'] : ''
        );
    }

    /**
     * @param \stdClass $data
     *
     * @return Rule
     */
    public static function buildFromRepository(\stdClass $data)
    {
       return new Rule(
            $data->id,
            $data->category,
            $data->action,
            $data->campaign,
            $data->cycle_day
        );
    }
}
