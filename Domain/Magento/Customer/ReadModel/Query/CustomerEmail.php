<?php
declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento\Customer\ReadModel\Query;

use GetResponse\GetResponseIntegration\Domain\SharedKernel\Scope;

class CustomerEmail
{
    private $email;
    private $scope;

    public function __construct(string $email, Scope $scope)
    {
        $this->email = $email;
        $this->scope = $scope;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getScope(): Scope
    {
        return $this->scope;
    }
}
