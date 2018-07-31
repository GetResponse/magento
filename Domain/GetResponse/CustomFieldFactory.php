<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

use GetResponse\GetResponseIntegration\Helper\Message;

/**
 * Class CustomsFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class CustomFieldFactory
{
    /**
     * @param array $data
     * @return array
     * @throws CustomFieldFactoryException
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

        foreach ($customs as $field => $name) {
            if (false == preg_match('/^[_a-zA-Z0-9]{2,32}$/m', $name)) {
                throw CustomFieldFactoryException::createWithMessage(
                    sprintf(Message::INVALID_CUSTOM_FIELD_VALUE, $name)
                );
            }
        }

        return $customs;
    }
}
