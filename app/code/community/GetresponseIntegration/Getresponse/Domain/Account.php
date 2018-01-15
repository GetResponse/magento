<?php

class GetresponseIntegration_Getresponse_Domain_Account
{
    private $accountId;
    private $firstName;
    private $lastName;
    private $email;
    private $phone;
    private $state;
    private $city;
    private $street;
    private $zipCode;
    private $country;
    private $numberOfEmployees;
    private $timeFormat;
    private $timeZoneName;
    private $timeZoneOffset;

    public function __construct(
        $accountId,
        $firstName,
        $lastName,
        $email,
        $phone,
        $state,
        $city,
        $street,
        $zipCode,
        $country,
        $numberOfEmployees,
        $timeFormat,
        $timeZoneName,
        $timeZoneOffset
    ) {
        $this->accountId = $accountId;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->phone = $phone;
        $this->state = $state;
        $this->city = $city;
        $this->street = $street;
        $this->zipCode = $zipCode;
        $this->country = $country;
        $this->numberOfEmployees = $numberOfEmployees;
        $this->timeFormat = $timeFormat;
        $this->timeZoneName = $timeZoneName;
        $this->timeZoneOffset = $timeZoneOffset;
    }

    public function getAccountId()
    {
        return $this->accountId;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getLastName()
    {
        return $this->lastName;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function getState()
    {
        return $this->state;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function getStreet()
    {
        return $this->street;
    }

    public function getZipCode()
    {
        return $this->zipCode;
    }

    public function getCountry()
    {
        return $this->country;
    }

    public function getNumberOfEmployees()
    {
        return $this->numberOfEmployees;
    }

    public function toArray()
    {
        return [
            'accountId' => $this->accountId,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'phone' => $this->phone,
            'state' => $this->state,
            'city' => $this->city,
            'street' => $this->street,
            'zipCode' => $this->zipCode,
            'country' => $this->country,
            'numberOfEmployees' => $this->numberOfEmployees,
            'timeFormat' => $this->timeFormat,
            'timeZoneName' => $this->timeZoneName,
            'timeZoneOffset' => $this->timeZoneOffset
        ];
    }
}