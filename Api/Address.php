<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

use JsonSerializable;

class Address implements JsonSerializable
{
    private $name;
    private $countryCode;
    private $firstName;
    private $lastName;
    private $address1;
    private $address2;
    private $city;
    private $zip;
    private $province;
    private $provinceCode;
    private $phone;
    private $company;

    public function __construct(
        string $name,
        string $countryCode,
        string $firstName,
        string $lastName,
        string $address1,
        ?string $address2,
        string $city,
        string $zip,
        ?string $province,
        ?string $provinceCode,
        ?string $phone,
        ?string $company
    ) {
        $this->name = $name;
        $this->countryCode = $countryCode;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->address1 = $address1;
        $this->address2 = $address2;
        $this->city = $city;
        $this->zip = $zip;
        $this->province = $province;
        $this->provinceCode = $provinceCode;
        $this->phone = $phone;
        $this->company = $company;
    }

    public function jsonSerialize(): array
    {
        return [
            'name' => $this->name,
            'country_code' => $this->countryCode,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'address1' => $this->address1,
            'address2' => $this->address2,
            'city' => $this->city,
            'zip' => $this->zip,
            'province' => $this->province,
            'province_code' => $this->provinceCode,
            'phone' => $this->phone,
            'company' => $this->company,
        ];
    }

    public function toCustomFieldsArray(string $prefix): array
    {
        return [
            $prefix . '_name' => $this->name,
            $prefix . '_country_code' => $this->countryCode,
            $prefix . '_first_name' => $this->firstName,
            $prefix . '_last_name' => $this->lastName,
            $prefix . '_address1' => $this->address1,
            $prefix . '_address2' => $this->address2,
            $prefix . '_city' => $this->city,
            $prefix . '_zip_code' => $this->zip,
            $prefix . '_province' => $this->province,
            $prefix . '_province_code' => $this->provinceCode,
            $prefix . '_phone' => $this->phone,
            $prefix . '_company' => $this->company
        ];
    }
}
