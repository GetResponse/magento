<?php
namespace GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand;

use GrShareCode\Export\Settings\EcommerceSettings;
use GrShareCode\Export\Settings\ExportSettings;

/**
 * Class ExportSettingsFactory
 * @package GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand
 */
class ExportSettingsFactory
{
    /**
     * @param ExportOnDemand $exportOnDemand
     * @return ExportSettings
     */
    public static function createFromExportOnDemand(ExportOnDemand $exportOnDemand)
    {
        return new ExportSettings(
            $exportOnDemand->getContactListId(),
            $exportOnDemand->getDayOfCycle(),
            new EcommerceSettings(
                $exportOnDemand->isSendEcommerceDataEnabled(),
                $exportOnDemand->getShopId()
            )
        );
    }
}