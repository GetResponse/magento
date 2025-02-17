<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

class Visitor
{
    private $visitorUuid;

    public function __construct(string $visitorUuid)
    {
        $this->visitorUuid = $visitorUuid;
    }

    public function getVisitorUuid(): string
    {
        return $this->visitorUuid;
    }
}
