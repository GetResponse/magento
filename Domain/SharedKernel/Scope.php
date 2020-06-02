<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\SharedKernel;

class Scope
{
    private $scopeId;

    public function __construct($scopeId)
    {
        $this->scopeId = $scopeId;
    }

    public function getScopeId()
    {
        return $this->scopeId;
    }


}
