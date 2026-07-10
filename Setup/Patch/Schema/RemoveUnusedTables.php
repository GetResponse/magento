<?php

declare(strict_types=1);

namespace GetResponse\GetResponseIntegration\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class RemoveUnusedTables implements SchemaPatchInterface
{
    /** @var SchemaSetupInterface */
    private $schemaSetup;

    /** @param SchemaSetupInterface $schemaSetup */
    public function __construct(SchemaSetupInterface $schemaSetup)
    {
        $this->schemaSetup = $schemaSetup;
    }

    /**
     * Remove unused integration tables.
     */
    public function apply(): self
    {
        $this->schemaSetup->startSetup();

        $connection = $this->schemaSetup->getConnection();
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
            $tableName = $this->schemaSetup->getTable($tableToRemove);

            if ($connection->isTableExists($tableName)) {
                $connection->dropTable($tableName);
            }
        }

        $this->schemaSetup->endSetup();

        return $this;
    }

    /**
     * Return patch dependencies.
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * Return patch aliases.
     */
    public function getAliases(): array
    {
        return [];
    }
}
