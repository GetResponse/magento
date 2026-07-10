<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Presenter\Api;

use GetResponse\GetResponseIntegration\Presenter\Api\Section\General;

class ConfigurationPresenter
{
    /** @var General */
    private $general;
    /** @var array */
    private $storesCollection;

    /**
     * @param General $general
     * @param array $storesCollection
     */
    public function __construct(
        General $general,
        array $storesCollection
    ) {
        $this->general = $general;
        $this->storesCollection = $storesCollection;
    }

    /**
     * Get general.
     *
     * @return \GetResponse\GetResponseIntegration\Presenter\Api\Section\General
     */
    public function getGeneral(): General
    {
        return $this->general;
    }

    /**
     * Get stores.
     *
     * @return \GetResponse\GetResponseIntegration\Presenter\Api\Section\Store[]
     */
    public function getStores(): array
    {
        return $this->storesCollection;
    }
}
