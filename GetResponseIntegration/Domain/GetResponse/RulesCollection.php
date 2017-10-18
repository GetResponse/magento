<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

/**
 * Class RulesCollection
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class RulesCollection
{
    /** @var array */
    private $rules;

    /**
     * @param Rule $rule
     */
    public function add(Rule $rule)
    {
        $this->rules[] = $rule;
    }

    /**
     * @return array|Rule[]
     */
    public function getRules()
    {
        return $this->rules;
    }
}
