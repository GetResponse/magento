<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\ReadModel\Query;

use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;

class ContactByEmail
{
    private $email;
    private $contactListId;
    private $scope;

    public function __construct($email, $contactListId, Scope $scope)
    {
        $this->email = $email;
        $this->contactListId = $contactListId;
        $this->scope = $scope;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getContactListId()
    {
        return $this->contactListId;
    }

    public function getScope(): Scope
    {
        return $this->scope;
    }
}
