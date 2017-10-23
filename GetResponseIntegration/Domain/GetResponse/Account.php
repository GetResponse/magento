<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse;

/**
 * Class Account
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse
 */
class Account
{
    /** @var string */
    private $accountId;

    /** @var string */
    private $firstName;

    /** @var string */
    private $lastName;

    /** @var string */
    private $email;

    /** @var string */
    private $companyName;

    /** @var string */
    private $phone;

    /** @var string */
    private $state;

    /** @var string */
    private $city;

    /** @var string */
    private $street;

    /** @var string */
    private $zipCode;

    /** @var string */
    private $countryCode;

    /**
     * @param string $accountId
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param string $companyName
     * @param string $phone
     * @param string $state
     * @param string $city
     * @param string $street
     * @param string $zipCode
     * @param string $countryCode
     */
    public function __construct(
        $accountId,
        $firstName,
        $lastName,
        $email,
        $companyName,
        $phone,
        $state,
        $city,
        $street,
        $zipCode,
        $countryCode
    ) {
        $this->accountId = $accountId;
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

    /**
     * @return string
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
    public function getCompanyName()
    {
        return $this->companyName;
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
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'accountId' => $this->accountId,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'phone' => $this->phone,
            'companyName' => $this->companyName,
            'state' => $this->state,
            'city' => $this->street,
            'street' => $this->street,
            'zipCode' => $this->zipCode,
            'countryCode' => $this->countryCode,
        ];
    }
}
