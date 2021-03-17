<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Api;

class Address
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

    public function getName(): string
    {
        return $this->name;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getAddress1(): string
    {
        return $this->address1;
    }

    public function getAddress2(): ?string
    {
        return $this->address2;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getZip(): string
    {
        return $this->zip;
    }

    public function getProvince(): ?string
    {
        return $this->province;
    }

    public function getProvinceCode(): ?string
    {
        return $this->provinceCode;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function toApiRequest(): array
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
}
