<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\Magento;

class EcommerceSettingsFactory
{
    /**
     * @param array $data
     * @return EcommerceSettings
     * @throws ValidationException
     */
    public static function createFromPost($data): EcommerceSettings
    {
        if (isset($data['ecommerce_status']) && (int) $data['ecommerce_status'] === 1) {
            return new EcommerceSettings(EcommerceSettings::STATUS_ENABLED, $data['shop_id'], $data['list_id']);
        }

        return new EcommerceSettings(EcommerceSettings::STATUS_DISABLED, null, null);
    }
}
