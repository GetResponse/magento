<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\Application\Command;

use GetResponse\GetResponseIntegration\Domain\GetResponse\SubscribeViaRegistration\SubscribeViaRegistration;
use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;
use GrShareCode\Contact\ContactCustomField\ContactCustomFieldsCollection;
use Magento\Customer\Model\Customer;

class AddContact
{
    private $scope;
    private $email;
    private $firstName;
    private $lastName;
    private $contactListId;
    private $dayOfCycle;
    private $customs;
    private $updateIfAlreadyExists;

    public function __construct(
        Scope $scope,
        string $email,
        string $firstName,
        string $lastName,
        string $contactListId,
        $dayOfCycle,
        ContactCustomFieldsCollection $customs,
        bool $updateIfAlreadyExists
    ) {
        $this->scope = $scope;
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->contactListId = $contactListId;
        $this->dayOfCycle = $dayOfCycle;
        $this->customs = $customs;
        $this->updateIfAlreadyExists = $updateIfAlreadyExists;
    }

    public static function createFromCustomer(
        Customer $customer,
        SubscribeViaRegistration $registrationSettings,
        ContactCustomFieldsCollection $contactCustomFieldsCollection,
        Scope $scope
    ): AddContact {
        return new self(
            $scope,
            $customer->getEmail(),
            $customer->getFirstname(),
            $customer->getLastname(),
            $registrationSettings->getCampaignId(),
            $registrationSettings->getCycleDay(),
            $contactCustomFieldsCollection,
            $registrationSettings->isUpdateCustomFieldsEnalbed()
        );
    }

    public function getScope(): Scope
    {
        return $this->scope;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getContactListId(): string
    {
        return $this->contactListId;
    }

    public function getDayOfCycle()
    {
        return $this->dayOfCycle;
    }

    public function getCustoms(): ContactCustomFieldsCollection
    {
        return $this->customs;
    }

    public function isUpdateIfAlreadyExists(): bool
    {
        return $this->updateIfAlreadyExists;
    }
}
