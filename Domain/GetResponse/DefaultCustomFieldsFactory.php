<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

/**
 * Class DefaultCustomsFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class DefaultCustomFieldsFactory
{
    /**
     * @return array
     */
    public static function createDefaultCustomsMap()
    {
        return [
            [
                'id' => 1,
                'customField' => 'firstname',
                'customValue' => 'firstname',
                'customName' => 'firstname',
                'isDefault' => 1,
                'isActive' => 1

            ],
            [
                'id' => 2,
                'customField' => 'lastname',
                'customValue' => 'lastname',
                'customName' => 'lastname',
                'isDefault' => 1,
                'isActive' => 1

            ],
            [
                'id' => 3,
                'customField' => 'email',
                'customValue' => 'email',
                'customName' => 'email',
                'isDefault' => 1,
                'isActive' => 1

            ],
            [
                'id' => 4,
                'customField' => 'street',
                'customValue' => 'street',
                'customName' => 'street',
                'isDefault' => 0,
                'isActive' => 0

            ],
            [
                'id' => 5,
                'customField' => 'postcode',
                'customValue' => 'postcode',
                'customName' => 'postcode',
                'isDefault' => 0,
                'isActive' => 0

            ],
            [
                'id' => 6,
                'customField' => 'city',
                'customValue' => 'city',
                'customName' => 'city',
                'isDefault' => 0,
                'isActive' => 0

            ],
            [
                'id' => 7,
                'customField' => 'telephone',
                'customValue' => 'telephone',
                'customName' => 'telephone',
                'isDefault' => 0,
                'isActive' => 0

            ],
            [
                'id' => 8,
                'customField' => 'country',
                'customValue' => 'country',
                'customName' => 'country',
                'isDefault' => 0,
                'isActive' => 0

            ],
            [
                'id' => 9,
                'customField' => 'birthday',
                'customValue' => 'birthday',
                'customName' => 'birthday',
                'isDefault' => 0,
                'isActive' => 0

            ]
        ];
    }
}
