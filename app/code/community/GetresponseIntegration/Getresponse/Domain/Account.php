<?php

/**
 * Class GetresponseIntegration_Getresponse_Domain_Account
 */
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

    /**
     * @param int $accountId
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param string $phone
     * @param string $state
     * @param string $city
     * @param string $street
     * @param string $zipCode
     * @param string $country
     * @param int $numberOfEmployees
     * @param string $timeFormat
     * @param string $timeZoneName
     * @param string $timeZoneOffset
     */
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

    /**
     * @return int
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @return string
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @return int
     */
    public function getNumberOfEmployees()
    {
        return $this->numberOfEmployees;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
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
        );
    }
}
