<?php
namespace GetResponse\GetResponseIntegration\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 * @package GetResponse\GetResponseIntegration\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '20.1.0', '<')) {
            $this->upgradeToVersion2010($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function upgradeToVersion2010(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('getresponse_settings'),
            'feature_tracking',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 16,
                'nullable' => false,
                'comment' => 'Tracking feature enabled in GetResponse',
                'default' => 'disabled'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('getresponse_settings'),
            'web_traffic',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 16,
                'nullable' => false,
                'comment' => 'Web tracking enabled in plugin',
                'default' => 'disabled'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('getresponse_settings'),
            'tracking_code_snippet',
            [
                'type' => Table::TYPE_TEXT,
                'nullable' => true,
                'comment' => 'Tracking code snippet',
            ]
        );


        $table = $setup->getConnection()->newTable(
            $setup->getTable('getresponse_product_map')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn(
            'gr_shop_id',
            Table::TYPE_TEXT,
            32,
            [],
            'GetResponse shop ID'
        )->addColumn(
            'entity_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => false, 'unsigned' => true, 'nullable' => false, 'primary' => false],
            'Entity ID'
        )->addColumn(
            'gr_product_id',
            Table::TYPE_TEXT,
            32,
            [],
            'GetResponse product ID'
        )->addForeignKey(
            $setup->getFkName(
                $setup->getTable('getresponse_product_map'),
                'entity_id',
                $setup->getTable('catalog_product_entity'),
                'entity_id'
            ),
            'entity_id',
            $setup->getTable('catalog_product_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        );

        $setup->getConnection()->createTable($table);

        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order'),
            'getresponse_order_id',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'nullable' => true,
                'comment' => 'GetResponse order id'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order'),
            'getresponse_order_md5',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 64,
                'nullable' => true,
                'comment' => 'GetResponse order md5'
            ]
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('quote'),
            'getresponse_cart_id',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 32,
                'nullable' => true,
                'comment' => 'GetResponse cart id'
            ]
        );
    }
}
