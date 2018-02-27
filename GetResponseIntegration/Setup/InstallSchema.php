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
        $table = $installer->getConnection()
            ->newTable($installer->getTable('getresponse_settings'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'id_shop',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Id shop'
            )
            ->addColumn(
                'api_key',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'GR Api Key'
            )
            ->addColumn(
                'api_url',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'GR Api Url'
            )
            ->addColumn(
                'api_domain',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'GR Api Domain'
            )
            ->addColumn(
                'active_subscription',
                Table::TYPE_BOOLEAN,
                null,
                ['default' => true, 'nullable' => false],
                'Active subscription'
            )
            ->addColumn(
                'update',
                Table::TYPE_BOOLEAN,
                null,
                ['default' => true, 'nullable' => false],
                'Update custom fields'
            )
            ->addColumn(
                'cycle_day',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'GR campaign cycle day'
            )
            ->addColumn(
                'campaign_id',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'GR campaign id'
            );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()
            ->newTable($installer->getTable('getresponse_account'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'id_shop',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Id shop'
            )
            ->addColumn(
                'account_id',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Id'
            )
            ->addColumn(
                'first_name',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'First name'
            )
            ->addColumn(
                'last_name',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Last name'
            )
            ->addColumn(
                'email',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Email'
            )
            ->addColumn(
                'company_name',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Company name'
            )
            ->addColumn(
                'phone',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'Phone'
            )
            ->addColumn(
                'state',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'First name'
            )
            ->addColumn(
                'city',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'First name'
            )
            ->addColumn(
                'street',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'First name'
            )
            ->addColumn(
                'zip_code',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'First name'
            )
            ->addColumn(
                'country_code',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'First name'
            );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()
            ->newTable($installer->getTable('getresponse_customs'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'id_shop',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Id shop'
            )
            ->addColumn(
                'custom_field',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Custom field'
            )
            ->addColumn(
                'custom_value',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Custom field value'
            )
            ->addColumn(
                'custom_name',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Custom field name'
            )
            ->addColumn(
                'default',
                Table::TYPE_BOOLEAN,
                null,
                ['default' => true, 'nullable' => false],
                'default field'
            )
            ->addColumn(
                'active_custom',
                Table::TYPE_BOOLEAN,
                null,
                ['default' => true, 'nullable' => false],
                'Active custom field'
            );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()
            ->newTable($installer->getTable('getresponse_webform'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'id_shop',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Id shop'
            )
            ->addColumn(
                'webform_id',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Webform id'
            )
            ->addColumn(
                'active_subscription',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false],
                'Active subscription flag'
            )
            ->addColumn(
                'sidebar',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Sidebar name'
            )
            ->addColumn(
                'style',
                Table::TYPE_BOOLEAN,
                null,
                ['default' => true, 'nullable' => false],
                'Webform style'
            )
            ->addColumn(
                'url',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Url to webform'
            );
        $installer->getConnection()->createTable($table);

        $table = $installer->getConnection()
            ->newTable($installer->getTable('getresponse_automation'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'id_shop',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Id shop'
            )
            ->addColumn(
                'category_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Category id'
            )
            ->addColumn(
                'campaign_id',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Campaign id'
            )
            ->addColumn(
                'action',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Action type'
            )
            ->addColumn(
                'cycle_day',
                Table::TYPE_TEXT,
                null,
                ['nullable' => true],
                'GR campaign cycle day'
            )
            ->addColumn(
                'active',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false],
                'active flag'
            );
        $installer->getConnection()->createTable($table);

        $installer->getConnection()->delete($installer->getTable('getresponse_customs'));
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $stores = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStores();
        foreach ($stores as $store) {
            $installer->getConnection()->insertMultiple($installer->getTable('getresponse_customs'), [
                'id_shop' => $store->getId(),
                'custom_field' => 'firstname',
                'custom_value' => 'firstname',
                'custom_name' => 'firstname',
                'default' => 1,
                'active_custom' => 1
            ]);
            $installer->getConnection()->insertMultiple($installer->getTable('getresponse_customs'), [
                'id_shop' => $store->getId(),
                'custom_field' => 'lastname',
                'custom_value' => 'lastname',
                'custom_name' => 'lastname',
                'default' => 1,
                'active_custom' => 1
            ]);
            $installer->getConnection()->insertMultiple($installer->getTable('getresponse_customs'), [
                'id_shop' => $store->getId(),
                'custom_field' => 'email',
                'custom_value' => 'email',
                'custom_name' => 'email',
                'default' => 1,
                'active_custom' => 1
            ]);
            $installer->getConnection()->insertMultiple($installer->getTable('getresponse_customs'), [
                'id_shop' => $store->getId(),
                'custom_field' => 'street',
                'custom_value' => 'street',
                'custom_name' => 'magento_street',
                'default' => 0,
                'active_custom' => 0
            ]);
            $installer->getConnection()->insertMultiple($installer->getTable('getresponse_customs'), [
                'id_shop' => $store->getId(),
                'custom_field' => 'postcode',
                'custom_value' => 'postcode',
                'custom_name' => 'magento_postcode',
                'default' => 0,
                'active_custom' => 0
            ]);
            $installer->getConnection()->insertMultiple($installer->getTable('getresponse_customs'), [
                'id_shop' => $store->getId(),
                'custom_field' => 'city',
                'custom_value' => 'city',
                'custom_name' => 'magento_city',
                'default' => 0,
                'active_custom' => 0
            ]);
            $installer->getConnection()->insertMultiple($installer->getTable('getresponse_customs'), [
                'id_shop' => $store->getId(),
                'custom_field' => 'telephone',
                'custom_value' => 'telephone',
                'custom_name' => 'magento_telephone',
                'default' => 0,
                'active_custom' => 0
            ]);
            $installer->getConnection()->insertMultiple($installer->getTable('getresponse_customs'), [
                'id_shop' => $store->getId(),
                'custom_field' => 'country',
                'custom_value' => 'country',
                'custom_name' => 'magento_country',
                'default' => 0,
                'active_custom' => 0
            ]);
            $installer->getConnection()->insertMultiple($installer->getTable('getresponse_customs'), [
                'id_shop' => $store->getId(),
                'custom_field' => 'birthday',
                'custom_value' => 'birthday',
                'custom_name' => 'magento_birthday',
                'default' => 0,
                'active_custom' => 0
            ]);
        }

        $installer->endSetup();
    }
}
