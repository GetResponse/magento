<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

/**
 * Class RulesCollectionFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class RulesCollectionFactory
{
    /**
     * @param array $data
     *
     * @return RulesCollection|Rule[]
     */
    public static function buildFromRepository(array $data)
    {
        $rules = new RulesCollection();

        foreach ($data as $row) {

            $rules->add(new Rule(
                $row->id,
                $row->category,
                $row->action,
                $row->campaign,
                $row->cycle_day
            ));
        }

        return $rules;
    }
}
