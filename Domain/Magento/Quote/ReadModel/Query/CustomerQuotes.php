<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento\Quote\ReadModel\Query;

class CustomerQuotes
{
    private $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
