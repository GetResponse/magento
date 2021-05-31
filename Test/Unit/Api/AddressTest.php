<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Test\Unit\Api;

use GetResponse\GetResponseIntegration\Api\Address;
use GetResponse\GetResponseIntegration\Test\BaseTestCase;

class AddressTest extends BaseTestCase
{
    /**
     * @test
     */
    public function shouldNormalizeToCustomFieldsArray(): void
    {
        $address = new Address(
            'TestAddress',
            'PL',
            'John',
            'Smith',
            'Street 12',
            'XYZ',
            'City',
            '99-001',
            'Province',
            '9009',
            '544404400',
            'Company'
        );

        $expectedData = [
            'my_name' => 'TestAddress',
            'my_country_code' => 'PL',
            'my_first_name' => 'John',
            'my_last_name' => 'Smith',
            'my_address1' => 'Street 12',
            'my_address2' => 'XYZ',
            'my_city' => 'City',
            'my_zip_code' => '99-001',
            'my_province' => 'Province',
            'my_province_code' => '9009',
            'my_phone' => '544404400',
            'my_company' => 'Company'
        ];

        $data = $address->toCustomFieldsArray('my');

        self::assertEquals($expectedData, $data);
    }
}
