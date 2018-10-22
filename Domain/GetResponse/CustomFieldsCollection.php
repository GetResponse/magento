<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

/**
 * Class CustomsCollection
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class CustomFieldsCollection
{
    /** @var array|CustomField[] */
    private $customs = [];

    /**
     * @param CustomField $custom
     */
    public function add(CustomField $custom)
    {
        $this->customs[] = $custom;
    }

    /**
     * @return array|CustomField[]
     */
    public function getCustoms()
    {
        return $this->customs;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $result = [];

        if (empty($this->customs)) {
            return $result;
        }

        foreach ($this->customs as $custom) {
            $result[] = $custom->toArray();
        }

        return $result;
    }
}
