<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\Contact\Application\Command;

use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;

class RemoveContact
{
    private $scope;
    private $email;

    public function __construct(Scope $scope, string $email)
    {
        $this->scope = $scope;
        $this->email = $email;
    }

    public function getScope(): Scope
    {
        return $this->scope;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
