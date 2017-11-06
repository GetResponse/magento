<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

/**
 * Class CustomsFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class CustomFieldFactory
{
    /**
     * @param array $data
     *
     * @return array
     */
    public static function createFromArray(array $data)
    {
        if (!isset($data['custom']) || empty($data['custom'])) {
            return [];
        }

        if (count($data['custom']) !== count($data['gr_custom'])) {
            return [];
        }

        $customs = [];

        foreach ($data['custom'] as $id => $name) {
            $value = isset($data['gr_custom'][$id]) ? $data['gr_custom'][$id] : '';
            $customs[$name] = $value;
        }

        return $customs;
    }
}
