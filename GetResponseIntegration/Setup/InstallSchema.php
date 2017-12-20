<?php
namespace GetResponse\GetResponseIntegration\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class InstallSchema
 * @package GetResponse\GetResponseIntegration\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();
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
        $installer->endSetup();
    }
}
