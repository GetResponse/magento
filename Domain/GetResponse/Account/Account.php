<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Account;

class Account
{
    private $firstName;
    private $lastName;
    private $email;
    private $companyName;
    private $phone;
    private $state;
    private $city;
    private $street;
    private $zipCode;
    private $countryCode;

    public function __construct(
        string $firstName,
        string $lastName,
        string $email,
        string $companyName,
        string $phone,
        string $state,
        string $city,
        string $street,
        string $zipCode,
        string $countryCode
    ) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->companyName = $companyName;
        $this->phone = $phone;
        $this->state = $state;
        $this->city = $city;
        $this->street = $street;
        $this->zipCode = $zipCode;
        $this->countryCode = $countryCode;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function getZipCode(): string
    {
        return $this->zipCode;
    }

    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    public function toArray(): array
    {
        return [
            'firstName' => $this->getFirstName(),
            'lastName' => $this->getLastName(),
            'email' => $this->getEmail(),
            'phone' => $this->getPhone(),
            'companyName' => $this->getCompanyName(),
            'city' => $this->getCity(),
            'street' => $this->getStreet(),
            'zipCode' => $this->getZipCode()
        ];
    }
}
