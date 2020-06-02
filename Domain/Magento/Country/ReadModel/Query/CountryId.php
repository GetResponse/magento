<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento\Country\ReadModel\Query;

class CountryId
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
