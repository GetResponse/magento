<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Domain\GetResponse\ExportOnDemand;

use GrShareCode\Export\Settings\EcommerceSettings;
use GrShareCode\Export\Settings\ExportSettings;

class ExportSettingsFactory
{
    public static function createFromExportOnDemand(ExportOnDemand $exportOnDemand): ExportSettings
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
