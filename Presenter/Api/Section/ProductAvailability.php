<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Presenter\Api\Section;

use GetResponse\GetResponseIntegration\Domain\Magento\ProductAvailabilitySettings;

class ProductAvailability
{
    /** @var ProductAvailabilitySettings */
    private $settings;

    /**
     * @param ProductAvailabilitySettings $settings
     */
    public function __construct(ProductAvailabilitySettings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Get is enabled. Serialized by Magento Web API as "is_enabled".
     *
     * @return bool
     */
    public function getIsEnabled(): bool
    {
        return $this->settings->isEnabled();
    }
}
