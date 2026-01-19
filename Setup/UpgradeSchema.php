<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context): void
    {
        $setup->startSetup();
        $this->removeUnusedTables($setup);

        $setup->endSetup();
    }

    private function removeUnusedTables(SchemaSetupInterface $setup): void
    {
        $connection = $setup->getConnection();

        $tablesToRemove = [
            'getresponse_account',
            'getresponse_automation',
            'getresponse_customs',
            'getresponse_settings',
            'getresponse_webform',
            'getresponse_cart_map',
            'getresponse_order_map',
            'getresponse_product_map'
        ];

        foreach ($tablesToRemove as $tableToRemove) {
            $tableName = $setup->getTable($tableToRemove);

            if ($connection->isTableExists($tableName)) {
                $connection->dropTable($tableName);
            }
        }
    }
}
