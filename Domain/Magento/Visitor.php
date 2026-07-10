<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

class Visitor
{
    /** @var string */
    private $visitorUuid;

    /**
     * @param string $visitorUuid
     */
    public function __construct(string $visitorUuid)
    {
        $this->visitorUuid = $visitorUuid;
    }

    /**
     * Get visitor uuid.
     */
    public function getVisitorUuid(): string
    {
        return $this->visitorUuid;
    }
}
