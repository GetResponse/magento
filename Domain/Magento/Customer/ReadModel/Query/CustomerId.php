<?php
declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento\Customer\ReadModel\Query;

class CustomerId
{
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return (int) $this->id;
    }
}
