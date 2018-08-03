<?php
namespace GetResponse\GetResponseIntegration\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Zend_Db_Exception;

/**
 * Class UpgradeSchema
 * @package GetResponse\GetResponseIntegration\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '20.1.0', '<')) {
            $this->upgradeToVersion2010($setup);
        }

        if (version_compare($context->getVersion(), '20.1.2', '<=')) {
            $this->ver2012removeUnusedTables($setup);
        }

        if (version_compare($context->getVersion(), '20.1.4', '<=')) {
            $this->addEcommerceTables($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     * @throws Zend_Db_Exception
     */
    private function upgradeToVersion2010(SchemaSetupInterface $setup)
    {
        $table = $setup->getConnection()->newTable(
            $setup->getTable('getresponse_order_map')
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
            'order_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => false, 'unsigned' => true, 'nullable' => false, 'primary' => false],
            'Order ID'
        )->addColumn(
            'gr_order_id',
            Table::TYPE_TEXT,
            32,
            [],
            'GetResponse order ID'
        )->addColumn(
            'payload_md5',
            Table::TYPE_TEXT,
            64,
            ['nullable' => false],
            'GetResponse order payload md5'
        )->addForeignKey(
            $setup->getFkName(
                $setup->getTable('getresponse_order_map'),
                'order_id',
                $setup->getTable('sales_order'),
                'entity_id'
            ),
            'order_id',
            $setup->getTable('sales_order'),
            'entity_id',
            Table::ACTION_NO_ACTION
        );

        $setup->getConnection()->createTable($table);

        $table = $setup->getConnection()->newTable(
            $setup->getTable('getresponse_cart_map')
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
            'cart_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => false, 'unsigned' => true, 'nullable' => false, 'primary' => false],
            'Cart ID'
        )->addColumn(
            'gr_cart_id',
            Table::TYPE_TEXT,
            32,
            [],
            'GetResponse cart ID'
        )->addForeignKey(
            $setup->getFkName(
                $setup->getTable('getresponse_cart_map'),
                'cart_id',
                $setup->getTable('quote'),
                'entity_id'
            ),
            'cart_id',
            $setup->getTable('quote'),
            'entity_id',
            Table::ACTION_NO_ACTION
        );

        $setup->getConnection()->createTable($table);

        $table = $setup->getConnection()->newTable(
            $setup->getTable('getresponse_product_map')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn(
            'magento_product_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => false, 'unsigned' => true, 'nullable' => true, 'primary' => false],
            'Magento product Id'
        )->addColumn(
            'magento_variant_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => false, 'unsigned' => true, 'nullable' => false, 'primary' => false],
            'Magento Variant Id'
        )->addColumn(
            'gr_shop_id',
            Table::TYPE_TEXT,
            32,
            [],
            'GetResponse shop ID'
        )->addColumn(
            'gr_product_id',
            Table::TYPE_TEXT,
            32,
            [],
            'GetResponse product ID'
        )->addColumn(
            'gr_variant_id',
            Table::TYPE_TEXT,
            32,
            [],
            'GetResponse product ID'
        )->addForeignKey(
            $setup->getFkName(
                $setup->getTable('getresponse_product_map'),
                'magento_product_id',
                $setup->getTable('catalog_product_entity'),
                'entity_id'
            ),
            'magento_product_id',
            $setup->getTable('catalog_product_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $setup->getFkName(
                $setup->getTable('getresponse_product_map'),
                'magento_variant_id',
                $setup->getTable('catalog_product_entity'),
                'entity_id'
            ),
            'magento_variant_id',
            $setup->getTable('catalog_product_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        );

        $setup->getConnection()->createTable($table);
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function ver2012removeUnusedTables(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->query("DROP TABLE IF EXISTS " . $setup->getTable('getresponse_account'));
        $setup->getConnection()->query("DROP TABLE IF EXISTS " . $setup->getTable('getresponse_automation'));
        $setup->getConnection()->query("DROP TABLE IF EXISTS " . $setup->getTable('getresponse_customs'));
        $setup->getConnection()->query("DROP TABLE IF EXISTS " . $setup->getTable('getresponse_settings'));
        $setup->getConnection()->query("DROP TABLE IF EXISTS " . $setup->getTable('getresponse_webform'));
    }

    /**
     * @param SchemaSetupInterface $setup
     * @throws Zend_Db_Exception
     */
    private function addEcommerceTables($setup)
    {
        $table = $setup->getConnection()->newTable(
            $setup->getTable('getresponse_order_map')
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
            'order_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => false, 'unsigned' => true, 'nullable' => false, 'primary' => false],
            'Order ID'
        )->addColumn(
            'gr_order_id',
            Table::TYPE_TEXT,
            32,
            [],
            'GetResponse order ID'
        )->addColumn(
            'payload_md5',
            Table::TYPE_TEXT,
            64,
            ['nullable' => false],
            'GetResponse order payload md5'
        )->addForeignKey(
            $setup->getFkName(
                $setup->getTable('getresponse_order_map'),
                'order_id',
                $setup->getTable('sales_order'),
                'entity_id'
            ),
            'order_id',
            $setup->getTable('sales_order'),
            'entity_id',
            Table::ACTION_NO_ACTION
        );

        $setup->getConnection()->createTable($table);

        $table = $setup->getConnection()->newTable(
            $setup->getTable('getresponse_cart_map')
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
            'cart_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => false, 'unsigned' => true, 'nullable' => false, 'primary' => false],
            'Cart ID'
        )->addColumn(
            'gr_cart_id',
            Table::TYPE_TEXT,
            32,
            [],
            'GetResponse cart ID'
        )->addForeignKey(
            $setup->getFkName(
                $setup->getTable('getresponse_cart_map'),
                'cart_id',
                $setup->getTable('quote'),
                'entity_id'
            ),
            'cart_id',
            $setup->getTable('quote'),
            'entity_id',
            Table::ACTION_NO_ACTION
        );

        $setup->getConnection()->createTable($table);

        $setup->getConnection()->query("DROP TABLE IF EXISTS " . $setup->getTable('getresponse_product_map'));


        $table = $setup->getConnection()->newTable(
            $setup->getTable('getresponse_product_map')
        )->addColumn(
            'id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn(
            'magento_product_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => false, 'unsigned' => true, 'nullable' => true, 'primary' => false],
            'Magento product Id'
        )->addColumn(
            'magento_variant_id',
            Table::TYPE_INTEGER,
            null,
            ['identity' => false, 'unsigned' => true, 'nullable' => false, 'primary' => false],
            'Magento Variant Id'
        )->addColumn(
            'gr_shop_id',
            Table::TYPE_TEXT,
            32,
            [],
            'GetResponse shop ID'
        )->addColumn(
            'gr_product_id',
            Table::TYPE_TEXT,
            32,
            [],
            'GetResponse product ID'
        )->addColumn(
            'gr_variant_id',
            Table::TYPE_TEXT,
            32,
            [],
            'GetResponse product ID'
        )->addForeignKey(
            $setup->getFkName(
                $setup->getTable('getresponse_product_map'),
                'magento_product_id',
                $setup->getTable('catalog_product_entity'),
                'entity_id'
            ),
            'magento_product_id',
            $setup->getTable('catalog_product_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        )->addForeignKey(
            $setup->getFkName(
                $setup->getTable('getresponse_product_map'),
                'magento_variant_id',
                $setup->getTable('catalog_product_entity'),
                'entity_id'
            ),
            'magento_variant_id',
            $setup->getTable('catalog_product_entity'),
            'entity_id',
            Table::ACTION_CASCADE
        );

        $setup->getConnection()->createTable($table);

        $setup->getConnection()->dropColumn(
            $setup->getTable('quote'),
            'getresponse_cart_id'
        );
    }
}
