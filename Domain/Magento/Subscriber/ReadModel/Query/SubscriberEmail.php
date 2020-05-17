<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento\Subscriber\ReadModel\Query;

class SubscriberEmail
{
    private $email;

    public function __construct(string $email)
    {
        $this->email = $email;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
